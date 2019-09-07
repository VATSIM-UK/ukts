<?php

namespace App\Modules\Endorsement\Special;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Assignment extends Pivot
{
    protected $table = "special_endorsement_assignments";

    protected $fillable = [
        'user_id',
        'endorsement_id',
        'granted_by'
    ];

    public function endorsement(): BelongsTo
    {
        return $this->belongsTo(SpecialEndorsement::class);
    }
}
