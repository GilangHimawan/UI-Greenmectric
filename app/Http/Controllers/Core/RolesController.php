<?php

namespace App\Http\Controllers\Core;

use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;


class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {

    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $roles = Role::orderBy('id','DESC')->paginate(5);
        return view('roles.index',compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
public function create()
{
    $permissions = Permission::orderBy('name')
        ->get()
        ->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

    return view('roles.create', compact('permissions'));
}
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);
    
        try {
            $role = Role::create(['name' => $request->get('name')]);
            $role->syncPermissions($request->get('permission'));

            return redirect()->route('roles.index')
                            ->with('success','Role created successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat role. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        $role = $role;
        $rolePermissions = $role->permissions;
    
        return view('roles.show', compact('role', 'rolePermissions'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                return explode('.', $permission->name)[0];
            });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact(
            'role',
            'permissions',
            'rolePermissions'
        ));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Role $role, Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);
        
        try {
            $role->update($request->only('name'));

            $role->syncPermissions($request->get('permission'));

            return redirect()->route('roles.index')
                            ->with('success','Role updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui role. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        try {
            $role->delete();

            return redirect()->route('roles.index')
                            ->with('success','Role deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('roles.index')
                ->with('error', 'Gagal menghapus role. Mungkin masih digunakan oleh user lain.');
        }
    }
}