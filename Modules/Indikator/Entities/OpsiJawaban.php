<?php
namespace Modules\Indikator\Entities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Indikator\Entities\Indikator;

class OpsiJawaban extends Model
{
    protected $table = 'opsi_jawaban';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'indikator_id',
        'urutan',
        'label',
        'nilai_skor',
    ];

    protected $casts = [
        'indikator_id' => 'integer',
        'urutan'       => 'integer',
        'nilai_skor'   => 'integer',
        'dibuat_pada'  => 'datetime',
        'diubah_pada'  => 'datetime',
    ];

    /**
     * Relasi ke indikator.
     */
    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'indikator_id');
    }
}
