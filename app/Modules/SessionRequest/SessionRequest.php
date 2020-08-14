<?php

namespace App\Modules\SessionRequest;

use App\Modules\Bookings\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessionRequest extends Model
{
    use SoftDeletes;

    public $guarded = [];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isTaken(): bool
    {
        return $this->accepted_at !== null;
    }
}
