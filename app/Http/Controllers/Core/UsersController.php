<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Models\Core\User;

use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Hash;
use Auth;

class UsersController extends Controller
{
    /**
     * Display all users
     * 
     * @return \Illuminate\Http\Response
     */
    public function index() 
    {
        $users = User::latest()->paginate(50);
        
        return view('users.index', compact('users'));
    }

    /**
     * Show form for creating user
     * 
     * @return \Illuminate\Http\Response
     */
    public function create() 
    {
        $roles = Role::latest()->get();
        $units = \Modules\Indikator\Entities\Unit::orderBy('id', 'desc')->get();
        return view('users.create', compact('roles', 'units'));
    }

    /**
     * Store a newly created user
     * 
     * @param User $user
     * @param StoreUserRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function store(User $user, StoreUserRequest $request)
{
    $request->validate([
    'name'     => 'required|string|max:255',
    'username' => 'required|string|max:255|unique:users,username',
    'email'    => 'required|email|max:255|unique:users,email',
    'password' => 'required|confirmed|min:8',
    'role'     => 'required|exists:roles,name',

    'unit_id' => [
        'nullable',
        'exists:unit,id',
        Rule::requiredIf($request->role === 'koordinator'),
    ],
]);
    try {
        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role_aktif' => $request->role,
            'unit_id'     => $request->unit_id,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')
            ->withSuccess('User berhasil dibuat.');
    } catch (\Throwable $e) {
        report($e);
        return redirect()->back()->withInput()
            ->withError('Gagal membuat user. Silakan coba lagi atau hubungi admin.');
    }
}

    /**
     * Show user data
     * 
     * @param User $user
     * 
     * @return \Illuminate\Http\Response
     */
    public function show(User $user) 
    {
        return view('users.show', [
            'user' => $user
        ]);
    }

    /**
     * Edit user data
     * 
     * @param User $user
     * 
     * @return \Illuminate\Http\Response
     */
public function edit(User $user)
{
    return view('users.edit', [
        'user'     => $user,
        'userRole' => $user->roles->pluck('name')->toArray(),
        'roles'    => Role::orderBy('name', 'asc')->get(),
        'unit_id'     => \Modules\Indikator\Entities\Unit::orderBy('nama_unit', 'asc')->get(),
    ]);
}

    /**
     * Edit user profile data
     * 
     * 
     * @return \Illuminate\Http\Response
     */
    public function editProfile() 
    {
        $user=Auth::user();
        return view('users.editprofile', [
            'user' => $user,
        ]);
    }

    /**
     * Update profile user data
     * 
     * @param User $user
     * @param UpdateProfileRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(User $user, UpdateProfileRequest $request) 
    {
        $validator = $request->validated();

        if (!$validator) {
            return redirect()->route('users.editprofile')
                ->withErrors($validator);
        }

        try {
            if ($request->avatar) {
                $newname = $user->id . "." . $request->file('avatar')->getClientOriginalExtension();

                // Perbaikan: sebelumnya kondisi ini terbalik, sehingga pesan "berhasil"
                // tetap muncul walaupun upload avatar GAGAL. Sekarang upload yang gagal
                // benar-benar dianggap gagal dan menampilkan notifikasi error.
                if (!Storage::disk('public_avatar')->putFileAs('/', $request->file('avatar'), $newname)) {
                    return redirect()->route('users.editprofile')
                        ->withError('Gagal mengunggah foto profil. Silakan coba lagi.');
                }

                $user->avatar = $newname;
            }

            if ($request->password) {
                $user->password = Hash::make($request->password);
            }

            $user->update();

            return redirect()->route('users.editprofile')
                ->withSuccess(__('User updated successfully.'));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('users.editprofile')
                ->withError('Gagal memperbarui profil. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Update user data
     * 
     * @param User $user
     * @param UpdateUserRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(User $user, UpdateUserRequest $request)
{
    try {
        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'unit_id'  => $request->unit_id,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles($request->role);

        return redirect()->route('users.index')
            ->withSuccess('User berhasil diperbarui.');
    } catch (\Throwable $e) {
        report($e);
        return redirect()->back()->withInput()
            ->withError('Gagal memperbarui user. Silakan coba lagi atau hubungi admin.');
    }
}
	
	public function tukaruser(User $user){
		try {
			$users = User::where(['id' => $user->id])->first();
			if ($users) {
				\Illuminate\Support\Facades\Session::flush();
				\Auth::logout();
				\Auth::login($user, true);
				return redirect()->route('home.index')->with('success', 'Sukses beralih user');
			}
			return redirect()->route('home.index')->with('warning', 'Gagal beralih user');
		} catch (\Throwable $e) {
			report($e);
			return redirect()->route('home.index')->with('error', 'Gagal beralih user. Silakan coba lagi.');
		}
	}

    /**
     * Delete user data
     * 
     * @param User $user
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user) 
    {
        try {
            $user->delete();

            return redirect()->route('users.index')
                ->withSuccess(__('User deleted successfully.'));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('users.index')
                ->withError('Gagal menghapus user. Mungkin data ini masih terhubung dengan data lain.');
        }
    }
}
