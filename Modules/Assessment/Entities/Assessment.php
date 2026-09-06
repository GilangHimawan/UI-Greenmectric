<?php

namespace Modules\Assessment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Indikator\Entities\Indikator;

/**
 * Model: Assessment
 *
 * Rekap skor per kategori per periode.
 * Karena 1 kategori = 1 unit, kombinasi (periode_id, kategori_id)
 * sudah cukup unik tanpa perlu unit_id.
 * Data ini di-upsert otomatis oleh AssessmentService
 * setiap ketua_regu menyimpan jawaban.
 */
class Assessment extends Model
{
    protected $table = 'assessment';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'periode_id',
        'indikator_id',
        'total_skor',
        'skor_maksimal',
        'persentase',
        'path_file',
        'nama_file',
    ];

    protected $casts = [
        'persentase' => 'decimal:2',
        'dibuat_pada' => 'datetime',
        'diubah_pada' => 'datetime',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }

    public function jawaban()
    {
        return $this->hasMany(Jawaban::class, 'assessment_id');
    }
}