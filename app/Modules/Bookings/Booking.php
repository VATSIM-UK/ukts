<?php

namespace App\Modules\Bookings;

use App\Modules\Position\Position;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use VATSIMUK\Support\Auth\Models\Concerns\HasRemoteRelationships;

class Booking extends Model
{
    use HasRemoteRelationships, SoftDeletes;

    const NETWORK_TYPE_LIVE = 0;
    const NETWORK_TYPE_SWEATBOX = 1;

    protected $fillable = ['user_id', 'position_id', 'from', 'to', 'network_type'];
    protected $dates = ['from', 'to'];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(resolve(User::class));
    }
}
