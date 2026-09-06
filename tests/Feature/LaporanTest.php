<?php

namespace Tests\Feature;

use App\Models\Core\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Assessment\Entities\Assessment;
use Modules\Assessment\Entities\Hasil;
use Modules\Assessment\Entities\Jawaban;
use Modules\Assessment\Entities\Periode;
use Modules\Indikator\Entities\Kategori;
use Modules\Indikator\Entities\Indikator;
use Modules\Indikator\Entities\OpsiJawaban;
use Modules\Indikator\Entities\Unit;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $kategori;
    protected $unit;
    protected $periode;
    protected $indikator;
    protected $opsi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        Role::firstOrCreate([
            'name' => 'koordinator',
            'guard_name' => 'web',
        ]);

        $this->kategori = Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting and Infrastructure',
            'skor_maksimal' => 1800,
        ]);

        $this->unit = Unit::create([
            'nama_unit' => 'UPT TIK',
            'kode_unit' => 'UPT01',
            'kategori_id' => $this->kategori->id,
        ]);

        $this->user = User::create([
            'name' => 'Koordinator',
            'username' => 'koordinator',
            'email' => 'koordinator@test.com',
            'password' => Hash::make('password'),
            'role_aktif' => 'koordinator',
            'unit_id' => $this->unit->id,
        ]);

        $this->user->assignRole('koordinator');

        $this->periode = Periode::create([
            'tahun' => 2026,
            'status' => 'aktif',
        ]);

        $this->indikator = Indikator::create([
            'kategori_id' => $this->kategori->id,
            'kode_indikator' => 'SI1',
            'pertanyaan' => 'Jumlah ruang terbuka hijau',
            'poin_maksimal' => 200,
            'tipe_jawaban' => 'pilihan_ganda',
            'wajib_file' => 0,
            'status' => 'Aktif',
            'urutan' => 1,
        ]);

        $this->opsi = OpsiJawaban::create([
            'indikator_id' => $this->indikator->id,
            'urutan' => 5,
            'label' => '> 95%',
            'nilai_skor' => 200,
        ]);
    }

    /**
     * Simulasikan koordinator sudah submit assessment untuk unit ini,
     * sehingga ada data jawaban + rekap hasil yang bisa dilaporkan.
     */
    protected function submitAssessment(): void
    {
        $assessment = Assessment::create([
            'periode_id' => $this->periode->id,
            'indikator_id' => $this->indikator->id,
        ]);

        Jawaban::create([
            'assessment_id' => $assessment->id,
            'unit_id' => $this->unit->id,
            'dijawab_oleh' => $this->user->id,
            'jawaban' => (string) $this->opsi->id,
            'skor_diperoleh' => $this->opsi->nilai_skor,
        ]);

        Hasil::create([
            'periode_id' => $this->periode->id,
            'unit_id' => $this->unit->id,
            'status' => 'submitted',
            'total_skor' => $this->opsi->nilai_skor,
            'skor_maksimal' => $this->indikator->poin_maksimal,
            'persentase' => 100,
        ]);
    }

    /** @test */
    public function halaman_laporan_dapat_diakses()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('laporan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('assessment::Laporan.index');
    }

    /** @test */
    public function laporan_menampilkan_rekap_skor_yang_benar()
    {
        $this->submitAssessment();

        $this->actingAs($this->user);

        $response = $this->get(route('laporan.index'));

        $response->assertStatus(200);

        $response->assertViewHas('laporan', function ($laporan) {
            $rekapSi = $laporan[$this->kategori->id] ?? null;

            return $rekapSi
                && $rekapSi['total'] === 200
                && $rekapSi['maksimal'] === 200
                && $rekapSi['persentase'] === 100.0;
        });

        $response->assertViewHas('overall', 100.0);
    }

    /** @test */
    /** @test */
    public function laporan_bisa_difilter_per_unit()
    {
        $this->submitAssessment();

        // Unit lain harus tetap punya kategori (kolom kategori_id NOT NULL),
        // jadi buatkan kategori terpisah supaya datanya tidak nyampur dengan
        // assessment milik $this->unit.
        $kategoriLain = Kategori::create([
            'kode_kategori' => 'EC',
            'nama_kategori' => 'Energy and Climate Change',
            'skor_maksimal' => 2000,
        ]);

        $unitLain = Unit::create([
            'nama_unit' => 'Unit Lain',
            'kode_unit' => 'UL01',
            'kategori_id' => $kategoriLain->id,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('laporan.index', ['unit' => $unitLain->id]));

        $response->assertStatus(200);

        // Difilter ke unit yang tidak submit apapun -> laporan harus kosong.
        $response->assertViewHas('laporan', function ($laporan) {
            return empty($laporan);
        });
    }

    /** @test */
    public function laporan_kosong_jika_belum_ada_jawaban()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('laporan.index'));

        $response->assertStatus(200);

        $response->assertViewHas('laporan', function ($laporan) {
            return empty($laporan);
        });

        $response->assertViewHas('overall', 0);
    }

    /** @test */
    public function preview_pdf_berhasil_di_stream()
    {
        $this->submitAssessment();

        $this->actingAs($this->user);

        $response = $this->get(route('laporan.preview'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function download_pdf_berhasil()
    {
        $this->submitAssessment();

        $this->actingAs($this->user);

        $response = $this->get(route('laporan.download'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /** @test */
    public function download_laporan_excel_berhasil_untuk_kategori_sendiri()
    {
        $this->submitAssessment();

        $this->actingAs($this->user);

        $response = $this->get(route('assessment.export', ['kategori' => $this->kategori->id]));

        $response->assertStatus(200);
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    /** @test */
    public function download_laporan_excel_gagal_jika_kategori_tidak_punya_unit_pemilik()
    {
        $kategoriYatim = Kategori::create([
            'kode_kategori' => 'EC',
            'nama_kategori' => 'Energy and Climate Change',
            'skor_maksimal' => 2000,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('assessment.export', ['kategori' => $kategoriYatim->id]));

        $response->assertNotFound();
    }
}