<?php

namespace App\Modules\Endorsement\Special;

use App\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialEndorsement extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(
            Position::class,
            'special_endorsement_positions',
            'endorsement_id',
            'position_id'
        );
    }
}
