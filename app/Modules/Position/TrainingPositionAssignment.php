<?php

namespace App\Modules\Position;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingPositionAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = ['position_id'];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
