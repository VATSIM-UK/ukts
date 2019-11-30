<?php

namespace App\Modules\Endorsement\Special;

use App\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use VATSIMUK\Auth\Remote\RemoteEloquent\HasCustomInstanceCreation;

class Assignment extends Pivot
{
    use HasCustomInstanceCreation;

    protected $table = 'special_endorsement_assignments';

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
