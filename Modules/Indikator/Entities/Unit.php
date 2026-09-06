<?php

namespace Modules\Indikator\Entities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Indikator\Entities\Kategori;
use Modules\Assessment\Entities\Jawaban;

Class Unit extends Model
{
        protected $table = 'unit';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nama_unit',
        'kode_unit',
        'kategori_id',
    ];

    protected $casts = [
        'kategori_id' => 'integer',
        'dibuat_pada' => 'datetime',
        'diubah_pada' => 'datetime',
    ];

    /**
     * Relasi ke kategori.
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Relasi ke jawaban assessment.
     */
    public function jawabanAssessments()
    {
        return $this->hasMany(Jawaban::class, 'unit_id');
    }
}

   