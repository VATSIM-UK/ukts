<?php

namespace App\Modules\Availability;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use VATSIMUK\Support\Auth\Models\Concerns\HasRemoteRelationships;

class Availability extends Model
{
    use HasRemoteRelationships;

    protected $table = 'availability';
    protected $fillable = ['user_id', 'from', 'to'];
    protected $dates = ['from', 'to'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include popular users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOld($query)
    {
        return $query->where('to', '<', Carbon::now());
    }
}
