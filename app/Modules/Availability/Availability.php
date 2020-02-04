<?php

namespace App\Modules\Availability;

use App\Modules\Position\Position;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use VATSIMUK\Auth\Remote\RemoteEloquent\HasCustomInstanceCreation;

class Availability extends Model
{
    use HasCustomInstanceCreation;

    protected $table = 'availability';
    protected $fillable = ['user_id', 'position_id', 'from', 'to'];
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
