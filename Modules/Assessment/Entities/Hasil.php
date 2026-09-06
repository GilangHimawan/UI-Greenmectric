<?php

namespace Modules\Assessment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Assessment\Entities\Assessment;
use Modules\Assessment\Entities\Jawaban;
use Modules\Indikator\Entities\Unit;

class Hasil extends Model
{
    protected $table = 'hasil_assessment';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'periode_id',
        'unit_id',
        'total_skor',
        'skor_maksimal',
        'persentase',
        'status',
        'path_file',
        'nama_file',
        'dibuat_pada',
        'diubah_pada'
    ];

    protected $casts = [
        'total_skor'    => 'integer',
        'skor_maksimal' => 'integer',
        'persentase'    => 'decimal:2',
        'dibuat_pada'   => 'datetime',
        'diubah_pada'   => 'datetime',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}