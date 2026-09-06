<?php

namespace Modules\Indikator\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Indikator\Entities\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::all();

        return view('indikator::kategori.index', compact('kategoris'));
    }

    public function create()
    {
        return view('indikator::kategori.create');
    }

   public function store(Request $request)
{
    $data = $request->validate([
        'kode_kategori' => 'required|string|max:10|unique:kategori,kode_kategori',
        'nama_kategori' => 'required|string|max:100',
        'skor_maksimal' => 'required|integer|min:0',
    ]);

    try {
        Kategori::create($data);

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    } catch (\Throwable $e) {
        report($e);
        return redirect()->back()->withInput()
            ->with('error', 'Gagal menambahkan kategori. Silakan coba lagi.');
    }
}

    public function show($id)
    {
        $kategori = Kategori::findOrFail($id);

        return view('indikator::kategori.show', compact('kategori'));
    }

    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);

        return view('indikator::kategori.edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $data = $request->validate([
            'kode_kategori' => 'required|string|max:10|unique:kategori,kode_kategori,' . $id,
            'nama_kategori' => 'required|string|max:100',
            'skor_maksimal' => 'required|integer|min:0',
        ]);

        try {
            $kategori->update($data);

            return redirect()
                ->route('kategori.index')
                ->with('success', 'Kategori berhasil diperbarui.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui kategori. Silakan coba lagi.');
        }
    }

    public function destroy($id)
    {
        try {
            $kategori = Kategori::findOrFail($id);

            $kategori->delete();

            return redirect()
                ->route('kategori.index')
                ->with('success', 'Kategori berhasil dihapus.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('kategori.index')
                ->with('error', 'Gagal menghapus kategori. Mungkin masih digunakan oleh unit atau indikator lain.');
        }
    }
}