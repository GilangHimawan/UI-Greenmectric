<?php

namespace Modules\Indikator\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Indikator\Entities\Unit;
use Modules\Indikator\Entities\Kategori;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()

    {
        $units = Unit::with('kategori')->orderBy('kode_unit')->get();
        $kategoris = Kategori::orderBy('kode_kategori')->get();
        return view('indikator::unit.index', compact('units', 'kategoris'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $kategoris = Kategori::orderBy('kode_kategori')->get();
        return view('indikator::unit.create', compact('kategoris'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
                'nama_unit'   => 'required|string|max:255',
                'kode_unit'   => 'required|string|max:255|unique:unit,kode_unit',
                'kategori_id' => 'required|exists:kategori,id',
        ]);

        try {
            Unit::create([
                'nama_unit'   => $request->nama_unit,
                'kode_unit'   => $request->kode_unit,
                'kategori_id' => $request->kategori_id,
            ]);

            return redirect()
                ->route('unit.index')
                ->with('success', 'Data unit berhasil ditambahkan.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan data unit. Silakan coba lagi.');
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('indikator::unit.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $kategoris = Kategori::orderBy('kode_kategori')->get();
        $unit = Unit::findOrFail($id);
        return view('indikator::unit.edit', compact('kategoris', 'unit'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_unit' => 'required|string|max:255',
            'kode_unit' => 'required|string|max:255|unique:unit,kode_unit,' . $id,
            'kategori_id' => 'required|exists:kategori,id',
        ]);

        try {
            $unit = \Modules\Indikator\Entities\Unit::findOrFail($id);
            $unit->nama_unit = $request->input('nama_unit');
            $unit->kode_unit = $request->input('kode_unit');
            $unit->kategori_id = $request->input('kategori_id');
            $unit->save();

            return redirect()
                ->route('unit.index')
                ->with('success', 'Data unit berhasil diperbarui.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui data unit. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        try {
            $unit = \Modules\Indikator\Entities\Unit::findOrFail($id);
            $unit->delete();

            return redirect()
                ->route('unit.index')
                ->with('success', 'Data unit berhasil dihapus.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('unit.index')
                ->with('error', 'Gagal menghapus data unit. Mungkin masih digunakan pada data lain.');
        }
    }
}
