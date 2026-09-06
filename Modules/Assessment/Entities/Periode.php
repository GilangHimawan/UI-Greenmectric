<?php

namespace Modules\Assessment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Core\User;

/**
 * Model: Periode
 *
 * Periode/masa berlakunya assessment.
 * Admin membuka (status → aktif) dan menutup (status → ditutup) periode.
 * Selama status "aktif", ketua_regu bisa mengisi jawaban.
 *
 * Status:
 *   draft   → periode dibuat, belum dibuka untuk pengisian
 *   aktif   → sedang berjalan, ketua_regu bisa mengisi
 *   ditutup → sudah berakhir, tidak bisa diisi lagi
 */
class Periode extends Model
{
    protected $table = 'periode_assessment';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'tahun',
        'status',
        'dibuka_pada',
        'dibuka_oleh',
        'ditutup_pada',
        'ditutup_oleh',
        'dibuat_pada',
        'diubah_pada'
    ];

    protected $casts = [
        'tahun' => 'integer',
        'dibuka_pada' => 'datetime',
        'ditutup_pada' => 'datetime',
        'dibuat_pada' => 'datetime',
        'diubah_pada' => 'datetime',
    ];

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'periode_id');
    }

    public function pembuka()
    {
        return $this->belongsTo(User::class, 'dibuka_oleh');
    }

    public function penutup()
    {
        return $this->belongsTo(User::class, 'ditutup_oleh');
    }

    // ── Helper ────────────────────────────────────────────

    /** Cari periode yang sedang berstatus aktif */
    public static function aktif(): ?self
    {
        return static::where('status', 'aktif')->latest('tahun')->first();
    }
}