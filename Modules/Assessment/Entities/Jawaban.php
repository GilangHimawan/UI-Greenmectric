<?php

namespace Modules\Assessment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Assessment\Entities\Assessment;
use Modules\Indikator\Entities\Unit;
use app\Models\Core\User;


class Jawaban extends Model
{
    protected $table = 'jawaban_assessment';

    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'assessment_id',
        'unit_id',
        'dijawab_oleh',
        'jawaban',
        'skor_diperoleh',
        'path_file',
        'nama_file_asli',
        'link_bukti',
        // Snapshot: dibekukan saat jawaban disimpan, dipakai oleh laporan
        // supaya perubahan master indikator tidak mengubah laporan lama.
        'kode_indikator_snapshot',
        'pertanyaan_snapshot',
        'tipe_jawaban_snapshot',
        'poin_maksimal_snapshot',
        'opsi_label_snapshot',
    ];

    protected $casts = [
        'dibuat_pada' => 'datetime',
        'diubah_pada' => 'datetime',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function penjawab()
    {
        return $this->belongsTo(User::class, 'dijawab_oleh');
    }
}