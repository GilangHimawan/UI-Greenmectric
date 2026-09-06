<?php

namespace Tests\Feature;

use App\Models\Core\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Indikator\Entities\Kategori;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KategoriTest extends TestCase
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
    public function test_tambah_kategori_berhasil()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('kategori.store'), [
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting and Infrastructure',
            'skor_maksimal' => 1800,
        ]);

        $response->assertRedirect(route('kategori.index'));

        $this->assertDatabaseHas('kategori', [
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting and Infrastructure',
            'skor_maksimal' => 1800,
        ]);
    }

    /** @test */
    public function test_tambah_kategori_gagal_kode_sudah_ada()
    {
        $this->actingAs($this->admin);

        Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting and Infrastructure',
            'skor_maksimal' => 1800,
        ]);

        $response = $this->from(route('kategori.create'))
            ->post(route('kategori.store'), [
                'kode_kategori' => 'SI',
                'nama_kategori' => 'Kategori Baru',
                'skor_maksimal' => 2000,
            ]);

        $response->assertSessionHasErrors('kode_kategori');

        $this->assertEquals(1, Kategori::count());
    }

    /** @test */
    public function test_edit_kategori_berhasil()
    {
        $this->actingAs($this->admin);

        $kategori = Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting and Infrastructure',
            'skor_maksimal' => 1800,
        ]);

        $response = $this->put(route('kategori.update', $kategori->id), [
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting & Infrastructure',
            'skor_maksimal' => 2000,
        ]);

        $response->assertRedirect(route('kategori.index'));

        $this->assertDatabaseHas('kategori', [
            'id' => $kategori->id,
            'nama_kategori' => 'Setting & Infrastructure',
            'skor_maksimal' => 2000,
        ]);
    }

    /** @test */
    public function test_edit_kategori_gagal_kode_sudah_digunakan()
    {
        $this->actingAs($this->admin);

        Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting',
            'skor_maksimal' => 1800,
        ]);

        $kategori = Kategori::create([
            'kode_kategori' => 'EC',
            'nama_kategori' => 'Energy',
            'skor_maksimal' => 2000,
        ]);

        $response = $this->from(route('kategori.edit', $kategori->id))
            ->put(route('kategori.update', $kategori->id), [
                'kode_kategori' => 'SI',
                'nama_kategori' => 'Energy',
                'skor_maksimal' => 2000,
            ]);

        $response->assertSessionHasErrors('kode_kategori');

        $this->assertDatabaseHas('kategori', [
            'id' => $kategori->id,
            'kode_kategori' => 'EC',
        ]);
    }

    /** @test */
    public function test_hapus_kategori_berhasil()
    {
        $this->actingAs($this->admin);

        $kategori = Kategori::create([
            'kode_kategori' => 'SI',
            'nama_kategori' => 'Setting',
            'skor_maksimal' => 1800,
        ]);

        $response = $this->delete(route('kategori.destroy', $kategori->id));

        $response->assertRedirect(route('kategori.index'));

        $this->assertDatabaseMissing('kategori', [
            'id' => $kategori->id,
        ]);
    }
}