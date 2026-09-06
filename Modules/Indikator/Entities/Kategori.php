<?php

namespace Modules\Indikator\Entities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kategori extends Model
{
    protected $table = 'kategori';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'kode_kategori',
        'nama_kategori',
        'skor_maksimal',
    ];

    protected $casts = [
        'skor_maksimal' => 'integer',
        'dibuat_pada'   => 'datetime',
        'diubah_pada'   => 'datetime',
    ];

    /**
     * Satu kategori memiliki banyak indikator.
     */
    public function indikators()
    {
        return $this->hasMany(Indikator::class, 'kategori_id');
    }
}
