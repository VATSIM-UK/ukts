<?php

namespace App\Modules\Session;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use SoftDeletes;

    protected $fillable = ['position_id'];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(resolve(User::class));
    }

    public function mentors(): HasOne
    {
        return $this->hasOne(resolve(User::class));
    }
}
