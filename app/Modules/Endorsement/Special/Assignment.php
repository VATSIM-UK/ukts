<?php

namespace App\Modules\Endorsement\Special;

use App\User;
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function granter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
