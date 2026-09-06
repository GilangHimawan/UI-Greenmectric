<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Core\User;
use Modules\Indikator\Entities\Kategori;
use Modules\Indikator\Entities\Indikator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class IndikatorTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        $this->admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_aktif' => 'admin'
        ]);

        $this->admin->assignRole('admin');
    }

    /** @test */
    public function test_tambah_indikator_berhasil()
    {
        $this->actingAs($this->admin);

        $kategori = Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting and Infrastructure',
            'skor_maksimal' => 1800,
        ]);

        $response = $this->post(route('indikator.store'), [
            'kategori_id'    => $kategori->id,
            'kode_indikator' => 'SI-01',
            'pertanyaan'     => 'Luas ruang terbuka hijau',
            'tipe_jawaban'   => 'pilihan_ganda',
            'poin_maksimal'  => 300,
            'urutan'         => 1,
        ]);

        $response->assertRedirect(route('indikator.index'));

        $this->assertDatabaseHas('indikator', [
            'kode_indikator' => 'SI-01',
            'status' => 'Aktif'
        ]);
    }

    /** @test */
    public function test_tambah_indikator_gagal_kode_sudah_ada()
    {
        $this->actingAs($this->admin);

        $kategori = Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting and Infrastructure',
            'skor_maksimal' => 1800,
        ]);

        Indikator::create([
            'kategori_id'    => $kategori->id,
            'kode_indikator' => 'SI-01',
            'pertanyaan'     => 'Pertanyaan Lama',
            'tipe_jawaban'   => 'pilihan_ganda',
            'poin_maksimal'  => 300,
            'status'         => 'Aktif',
            'urutan'         => 1,
        ]);

        $response = $this->from(route('indikator.create'))
            ->post(route('indikator.store'), [
                'kategori_id'    => $kategori->id,
                'kode_indikator' => 'SI-01',
                'pertanyaan'     => 'Pertanyaan Baru',
                'tipe_jawaban'   => 'pilihan_ganda',
                'poin_maksimal'  => 300,
                'urutan'         => 2,
            ]);

        $response->assertSessionHasErrors('kode_indikator');

        $this->assertEquals(1, Indikator::count());
    }

    /** @test */
    public function test_edit_indikator_berhasil()
    {
        $this->actingAs($this->admin);

        $kategori = Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting and Infrastructure',
            'skor_maksimal' => 1800,
        ]);

        $indikator = Indikator::create([
            'kategori_id'    => $kategori->id,
            'kode_indikator' => 'SI-01',
            'pertanyaan'     => 'Pertanyaan Lama',
            'tipe_jawaban'   => 'pilihan_ganda',
            'poin_maksimal'  => 300,
            'status'         => 'Aktif',
            'urutan'         => 1,
        ]);

        $response = $this->put(route('indikator.update', $indikator->id), [
            'kategori_id'    => $kategori->id,
            'kode_indikator' => 'SI-01',
            'pertanyaan'     => 'Pertanyaan Baru',
            'tipe_jawaban'   => 'pilihan_ganda',
            'poin_maksimal'  => 500,
            'urutan'         => 1,
        ]);

        $response->assertRedirect(route('indikator.index'));

        $this->assertDatabaseHas('indikator', [
            'id'            => $indikator->id,
            'pertanyaan'    => 'Pertanyaan Baru',
            'poin_maksimal' => 500,
        ]);
    }

    /** @test */
    public function test_edit_indikator_gagal_kode_sudah_digunakan()
    {
        $this->actingAs($this->admin);

        $kategori = Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting and Infrastructure',
            'skor_maksimal' => 1800,
        ]);

        Indikator::create([
            'kategori_id'    => $kategori->id,
            'kode_indikator' => 'SI-01',
            'pertanyaan'     => 'Pertanyaan 1',
            'tipe_jawaban'   => 'pilihan_ganda',
            'poin_maksimal'  => 300,
            'status'         => 'Aktif',
            'urutan'         => 1,
        ]);

        $indikator = Indikator::create([
            'kategori_id'    => $kategori->id,
            'kode_indikator' => 'SI-02',
            'pertanyaan'     => 'Pertanyaan 2',
            'tipe_jawaban'   => 'pilihan_ganda',
            'poin_maksimal'  => 400,
            'status'         => 'Aktif',
            'urutan'         => 2,
        ]);

        $response = $this->from(route('indikator.edit', $indikator->id))
            ->put(route('indikator.update', $indikator->id), [
                'kategori_id'    => $kategori->id,
                'kode_indikator' => 'SI-01',
                'pertanyaan'     => 'Pertanyaan Baru',
                'tipe_jawaban'   => 'pilihan_ganda',
                'poin_maksimal'  => 500,
                'urutan'         => 2,
            ]);

        $response->assertSessionHasErrors('kode_indikator');

        $this->assertDatabaseHas('indikator', [
            'id' => $indikator->id,
            'kode_indikator' => 'SI-02',
        ]);
    }

    /** @test */
    public function test_hapus_indikator_berhasil()
    {
        $this->actingAs($this->admin);

        $kategori = Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting and Infrastructure',
            'skor_maksimal' => 1800,
        ]);

        $indikator = Indikator::create([
            'kategori_id'    => $kategori->id,
            'kode_indikator' => 'SI-01',
            'pertanyaan'     => 'Pertanyaan',
            'tipe_jawaban'   => 'pilihan_ganda',
            'poin_maksimal'  => 300,
            'status'         => 'Aktif',
            'urutan'         => 1,
        ]);

        $response = $this->delete(route('indikator.destroy', $indikator->id));

        $response->assertRedirect(route('indikator.index'));

        $this->assertDatabaseHas('indikator', [
            'id' => $indikator->id,
            'status' => 'Nonaktif',
        ]);
    }
}