<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualExaminationPermission extends Model
{
    protected $table = 'manual_examination_center_permissions';

    protected $fillable = [
        'center_id',
        'can_manual_examination',
    ];

    protected $casts = [
        'center_id' => 'integer',
        'can_manual_examination' => 'boolean',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(CarInspector::class, 'center_id');
    }
}
