<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Core\Menu;
use Spatie\Permission\Models\Role;

class MenusController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $menus = Menu::orderBy('urut','ASC')->get();
        return view('menu.index', compact('menus'));
    }

    public function create() 
    {
        $menus = Menu::where('parent_id',0)->orderBy('urut','ASC')->get();
        $roles = Role::latest()->get();
        return view('menu.create', compact('menus','roles'));
    }

    public function simpan(Request $request) 
    {
        $request->validate([
            'menu' => 'required',
        ]);
        try {
            $array_menu = json_decode($request->menu, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Format data menu tidak valid.');
            }
            $this->saveMenu($array_menu);
            $menus = Menu::orderBy('urut','ASC')->get();
            return view('menu.index', compact('menus'))
                ->with('success', 'Urutan menu berhasil disimpan');
        } catch (\Throwable $e) {
            report($e);
            $menus = Menu::orderBy('urut','ASC')->get();
            return view('menu.index', compact('menus'))
                ->with('error', 'Gagal menyimpan urutan menu. Silakan coba lagi.');
        }
    }

    private function saveMenu($menu, $parent_id=0){
        $urut=1;
        foreach($menu as $m){            
            $dm = Menu::where('id',$m['id'])->first();
            if($dm){
                $dm->urut=$urut;
                $dm->parent_id=$parent_id;
                $dm->save();
            }
            if(isset($m['children']) && count($m['children'])>0){
                $this->saveMenu($m['children'], $m['id']);
            }
            $urut++;
        }
    }

    public function store(Request $request) 
    {
        $request->validate([
            'name' => 'required',
            'can' => 'required',
            'icon' => 'required',
        ]);
       
        try {
            $active="";
            if($request->url!="")$active=serialize([$request->url,$request->url."*"]);
            $menu = Menu::create([
                'label' => $request->name,
                'url' => $request->url,
                'can' => serialize($request->can),
                'icon' => $request->icon,
                'modul' => 'Core',
                'urut' => 1,
                'parent_id' => $request->parent_id,
                'active' => $active,
            ]);

            return redirect()->route('menus.index')
                    ->with('success', 'Berhasil menambah menu');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambah menu. Silakan coba lagi.');
        }
    }

    public function edit($menu)
    {
        $menu = Menu::where('id',$menu)->first();
        if($menu){
            $menus = Menu::where('parent_id',0)->orderBy('urut','ASC')->get();
            return view('menu.edit', [
                'menu' => $menu,
                'menus' => $menus,
                'roles' => Role::latest()->get()
            ]);
        }else 
            // Perbaikan: sebelumnya kasus "menu tidak ditemukan" ini keliru menampilkan
            // pesan sukses ("Berhasil menambah menu"). Sekarang ditampilkan sebagai error.
            return redirect()->route('menus.index')
                ->with('error', 'Menu tidak ditemukan.');
    }
    

    public function update(Request $request, $menu)
    {
        $request->validate([
            'name' => 'required',
            'can' => 'required',
            'icon' => 'required',
        ]);
        try {
            $active="";
            if($request->url!="")$active=serialize([$request->url,$request->url."*"]);
            $menu = Menu::where('id',$menu)->first();
            if($menu){
                $menu->label = $request->name;
                $menu->url= $request->url;
                $menu->can= serialize($request->can);
                $menu->icon= $request->icon;
                $menu->urut= 1;
                $menu->parent_id= $request->parent_id;
                $menu->active= $active;
                $menu->save();

                return redirect()->route('menus.index')
                        ->with('success', 'Berhasil merubah menu');
            }
            return redirect()->route('menus.index')
                    ->with('error', 'Menu tidak ditemukan.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal merubah menu. Silakan coba lagi.');
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = Menu::destroy($id);
            if (!$deleted) {
                return redirect()->route('menus.index')
                    ->with('error', 'Menu tidak ditemukan.');
            }
            return redirect()->route('menus.index')
                ->with('success', 'Berhasil menghapus menu');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('menus.index')
                ->with('error', 'Gagal menghapus menu. Mungkin masih memiliki sub-menu.');
        }
    }
}
