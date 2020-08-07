<?php

namespace App\Modules\Session;

use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use VATSIMUK\Support\Auth\Models\Concerns\HasRemoteRelationships;

class Assignment extends Pivot
{
    use HasRemoteRelationships;

    protected $table = 'session_permission_assignments';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'endorsement_id',
        'granted_by',
    ];

    public function endorsement(): BelongsTo
    {
        return $this->belongsTo(SpecialEndorsement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(resolve(User::class));
    }

    public function granter(): BelongsTo
    {
        return $this->belongsTo(resolve(User::class), 'granted_by');
    }
}
