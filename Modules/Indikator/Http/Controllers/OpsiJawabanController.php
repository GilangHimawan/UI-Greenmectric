<?php
namespace Modules\Indikator\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Indikator\Entities\Indikator;
use Modules\Indikator\Entities\OpsiJawaban;
use Illuminate\Http\Request;

/**
 * OpsiJawabanController
 * CRUD pilihan jawaban untuk indikator bertipe "pilihan"
 */
class OpsiJawabanController extends Controller
{
    public function index(Indikator $indikator)
    {
        $opsiJawabans = $indikator->opsiJawabans;
        return view('indikator::opsi.index', compact('indikator', 'opsiJawabans'));
    }

    public function create(Indikator $indikator)
    {
        // Pastikan hanya indikator tipe pilihan yang bisa punya opsi
        abort_if($indikator->tipe_jawaban !== 'pilihan', 403,
            'Hanya indikator bertipe pilihan yang bisa memiliki opsi jawaban.');

        return view('indikator::opsi.form', [
            'indikator' => $indikator,
            'opsi'      => new OpsiJawaban(),
        ]);
    }

    public function store(Request $request, Indikator $indikator)
    {
        $data = $request->validate([
            'label'      => 'required|string|max:150',
            'nilai_skor' => 'required|integer|min:0|max:32767',
        ]);

        try {
            $data['indikator_id'] = $indikator->id;
            OpsiJawaban::create($data);

            return redirect()->route('admin.indikator.opsi.index', $indikator)
                             ->with('success', 'Opsi jawaban berhasil ditambahkan.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menambahkan opsi jawaban. Silakan coba lagi.');
        }
    }

    public function edit(OpsiJawaban $opsi)
    {
        return view('indikator::opsi.form', [
            'indikator' => $opsi->indikator,
            'opsi'      => $opsi,
        ]);
    }

    public function update(Request $request, OpsiJawaban $opsi)
    {
        $data = $request->validate([
            'label'      => 'required|string|max:150',
            'nilai_skor' => 'required|integer|min:0|max:32767',
        ]);

        try {
            $opsi->update($data);

            return redirect()->route('admin.indikator.opsi.index', $opsi->indikator)
                             ->with('success', 'Opsi jawaban berhasil diperbarui.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui opsi jawaban. Silakan coba lagi.');
        }
    }

    public function destroy(OpsiJawaban $opsi)
    {
        try {
            $indikator = $opsi->indikator;
            $opsi->delete();

            return redirect()->route('admin.indikator.opsi.index', $indikator)
                             ->with('success', 'Opsi jawaban berhasil dihapus.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()
                ->with('error', 'Gagal menghapus opsi jawaban. Silakan coba lagi.');
        }
    }
}
