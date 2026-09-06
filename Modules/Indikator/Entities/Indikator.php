<?php
namespace Modules\Indikator\Entities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Indikator\Entities\Kategori;
use Modules\Assessment\Entities\Assessment;
use Modules\Indikator\Entities\OpsiJawaban;

class Indikator extends Model
{
        protected $table = 'indikator';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'kategori_id',
        'kode_indikator',
        'pertanyaan',
        'poin_maksimal',
        'tipe_jawaban',
        'wajib_file',
        'status',
        'urutan',
        'dibuat_pada',
        'diubah_pada'
    ];

    protected $casts = [
        'kategori_id'    => 'integer',
        'pertanyaan'     => 'string',
        'kode_indikator'  => 'string',
        'poin_maksimal'  => 'integer',
        'wajib_file'     => 'boolean',
        'urutan'         => 'integer',
        'dibuat_pada'    => 'datetime',
        'diubah_pada'    => 'datetime',
    ];

    /**
     * Relasi ke kategori.
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Relasi ke assessment.
     */
    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'indikator_id');
    }
    public function opsiJawaban()
    {
        return $this->hasMany(OpsiJawaban::class, 'indikator_id')->orderBy('urutan');
    }

}