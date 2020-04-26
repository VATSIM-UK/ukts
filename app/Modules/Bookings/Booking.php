<?php

namespace App\Modules\Bookings;

use App\Modules\Position\Position;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use VATSIMUK\Support\Auth\Models\Concerns\HasRemoteRelationships;

class Booking extends Model
{
    use HasRemoteRelationships;

    protected $fillable = ['user_id', 'position_id', 'from', 'to'];
    protected $attributes = ['network_type' => 0];
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
