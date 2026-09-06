<?php

namespace Tests\Feature;

use App\Models\Core\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Assessment\Entities\Hasil;
use Modules\Assessment\Entities\Periode;
use Modules\Indikator\Entities\Kategori;
use Modules\Indikator\Entities\Indikator;
use Modules\Indikator\Entities\Unit;
use Spatie\Permission\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Assessment\Entities\Jawaban;
use Tests\TestCase;

class AssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $kategori;
    protected $unit;
    protected $periode;
    protected $indikator;

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
            'poin_maksimal' => 100,
            'tipe_jawaban' => 'angka',
            'wajib_file' => 0,
            'status' => 'Aktif',
            'urutan' => 1,
        ]);
    }

    /** @test */
    public function test_simpan_draft_assessment_berhasil()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('assessment.store'), [
            'aksi' => 'draft',
            'jawaban' => [
                $this->indikator->id => 80,
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('assessment', [
            'periode_id' => $this->periode->id,
            'indikator_id' => $this->indikator->id,
        ]);

        $this->assertDatabaseHas('hasil_assessment', [
            'periode_id' => $this->periode->id,
            'unit_id' => $this->unit->id,
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function test_submit_assessment_berhasil()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('assessment.store'), [
            'aksi' => 'submit',
            'jawaban' => [
                $this->indikator->id => 100,
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('hasil_assessment', [
            'periode_id' => $this->periode->id,
            'unit_id' => $this->unit->id,
            'status' => 'submitted',
        ]);
    }

    /** @test */
    public function test_submit_assessment_gagal_tanpa_jawaban()
    {
        $this->actingAs($this->user);

        $response = $this->from(route('assessment.create'))
            ->post(route('assessment.store'), [
                'aksi' => 'submit',
                'jawaban' => [],
            ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function test_assessment_gagal_jika_sudah_disubmit()
    {
        Hasil::create([
            'periode_id' => $this->periode->id,
            'unit_id' => $this->unit->id,
            'status' => 'submitted',
            'total_skor' => 100,
            'skor_maksimal' => 100,
            'persentase' => 100,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('assessment.store'), [
            'aksi' => 'draft',
            'jawaban' => [
                $this->indikator->id => 50,
            ],
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function test_assessment_gagal_jika_tidak_ada_periode_aktif()
    {
        Periode::query()->delete();

        $this->actingAs($this->user);

        $response = $this->post(route('assessment.store'), [
            'aksi' => 'draft',
            'jawaban' => [
                $this->indikator->id => 50,
            ],
        ]);

        $response->assertNotFound();
    }
    /** @test */
public function test_submit_assessment_gagal_tanpa_bukti_saat_wajib_file()
{
    $this->actingAs($this->user);

    $indikatorFile = Indikator::create([
        'kategori_id'    => $this->kategori->id,
        'kode_indikator' => 'SI2',
        'pertanyaan'     => 'Program konservasi kampus',
        'poin_maksimal'  => 100,
        'tipe_jawaban'   => 'isian',
        'wajib_file'     => 1,
        'status'         => 'Aktif',
        'urutan'         => 2,
    ]);

    // Diisi jawabannya, tapi TIDAK ada file maupun link bukti sama sekali.
    $response = $this->from(route('assessment.create'))
        ->post(route('assessment.store'), [
            'aksi' => 'submit',
            'jawaban' => [
                $this->indikator->id => 100,
                $indikatorFile->id   => 'Sudah berjalan penuh',
            ],
        ]);

    $response->assertSessionHasErrors("file.$indikatorFile->id");
}

/** @test */
public function test_submit_assessment_berhasil_dengan_upload_file()
{
    Storage::fake('public');

    $this->actingAs($this->user);

    $indikatorFile = Indikator::create([
        'kategori_id'    => $this->kategori->id,
        'kode_indikator' => 'SI2',
        'pertanyaan'     => 'Program konservasi kampus',
        'poin_maksimal'  => 100,
        'tipe_jawaban'   => 'isian',
        'wajib_file'     => 1,
        'status'         => 'Aktif',
        'urutan'         => 2,
    ]);

    $file = UploadedFile::fake()->create('bukti-konservasi.pdf', 500, 'application/pdf');

    $response = $this->post(route('assessment.store'), [
        'aksi' => 'submit',
        'jawaban' => [
            $this->indikator->id => 100,
            $indikatorFile->id   => 'Sudah berjalan penuh',
        ],
        'file' => [
            $indikatorFile->id => $file,
        ],
    ]);

    $response->assertRedirect();

    $jawabanTersimpan = Jawaban::where('unit_id', $this->unit->id)
        ->whereHas('assessment', fn ($q) => $q->where('indikator_id', $indikatorFile->id))
        ->first();

    $this->assertNotNull($jawabanTersimpan);
    $this->assertNotNull($jawabanTersimpan->path_file);

    $this->assertTrue(Storage::disk('public')->exists($jawabanTersimpan->path_file));
}

/** @test */
public function test_submit_assessment_berhasil_dengan_link_bukti()
{
    $this->actingAs($this->user);

    $indikatorFile = Indikator::create([
        'kategori_id'    => $this->kategori->id,
        'kode_indikator' => 'SI2',
        'pertanyaan'     => 'Program konservasi kampus',
        'poin_maksimal'  => 100,
        'tipe_jawaban'   => 'isian',
        'wajib_file'     => 1,
        'status'         => 'Aktif',
        'urutan'         => 2,
    ]);

    // Tanpa upload file, cuma isi link — harus tetap valid (evidence
    // cukup salah satu: file ATAU link).
    $response = $this->post(route('assessment.store'), [
        'aksi' => 'submit',
        'jawaban' => [
            $this->indikator->id => 100,
            $indikatorFile->id   => 'Sudah berjalan penuh',
        ],
        'link' => [
            $indikatorFile->id => 'https://drive.google.com/file/d/contoh-bukti',
        ],
    ]);

    $response->assertRedirect();

    $jawabanTersimpan = Jawaban::where('unit_id', $this->unit->id)
        ->whereHas('assessment', fn ($q) => $q->where('indikator_id', $indikatorFile->id))
        ->first();

    $this->assertEquals(
        'https://drive.google.com/file/d/contoh-bukti',
        $jawabanTersimpan->link_bukti
    );
}

/** @test */
public function test_upload_file_ditolak_jika_format_tidak_sesuai()
{
    $this->actingAs($this->user);

    $indikatorFile = Indikator::create([
        'kategori_id'    => $this->kategori->id,
        'kode_indikator' => 'SI2',
        'pertanyaan'     => 'Program konservasi kampus',
        'poin_maksimal'  => 100,
        'tipe_jawaban'   => 'isian',
        'wajib_file'     => 1,
        'status'         => 'Aktif',
        'urutan'         => 2,
    ]);

    // .txt tidak termasuk mimes yang diizinkan (pdf,doc,docx,xls,xlsx)
    $fileTidakValid = UploadedFile::fake()->create('bukti.txt', 100, 'text/plain');

    $response = $this->post(route('assessment.store'), [
        'aksi' => 'submit',
        'jawaban' => [
            $this->indikator->id => 100,
            $indikatorFile->id   => 'Sudah berjalan penuh',
        ],
        'file' => [
            $indikatorFile->id => $fileTidakValid,
        ],
    ]);

    $response->assertSessionHasErrors("file.$indikatorFile->id");
}

/** @test */
public function test_upload_file_ditolak_jika_lebih_dari_2mb()
{
    $this->actingAs($this->user);

    $indikatorFile = Indikator::create([
        'kategori_id'    => $this->kategori->id,
        'kode_indikator' => 'SI2',
        'pertanyaan'     => 'Program konservasi kampus',
        'poin_maksimal'  => 100,
        'tipe_jawaban'   => 'isian',
        'wajib_file'     => 1,
        'status'         => 'Aktif',
        'urutan'         => 2,
    ]);

    // 2049 KB, sedikit di atas batas 2048 KB (2 MB)
    $fileTerlaluBesar = UploadedFile::fake()->create('bukti-besar.pdf', 2049, 'application/pdf');

    $response = $this->post(route('assessment.store'), [
        'aksi' => 'submit',
        'jawaban' => [
            $this->indikator->id => 100,
            $indikatorFile->id   => 'Sudah berjalan penuh',
        ],
        'file' => [
            $indikatorFile->id => $fileTerlaluBesar,
        ],
    ]);

    $response->assertSessionHasErrors("file.$indikatorFile->id");
}
}