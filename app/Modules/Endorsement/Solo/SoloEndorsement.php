<?php

namespace App\Modules\Endorsement\Solo;

use App\Modules\Position\Position;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use VATSIMUK\Support\Auth\Models\Concerns\HasCustomInstanceCreation;

class SoloEndorsement extends Model
{
    use HasCustomInstanceCreation;

    protected $fillable = ['user_id', 'position_id', 'expiry_date'];

    protected $dates = ['expiry_date'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereDate('expiry_date', '>', Carbon::now());
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(resolve(User::class), 'user_id');
    }
}
