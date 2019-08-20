<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = ['user_id', 'position_id', 'from', 'to'];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
