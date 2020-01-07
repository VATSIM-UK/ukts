<?php

namespace App\Modules\Position;

use Illuminate\Database\Eloquent\Builder;

class TrainingPositionService
{
    public function checkExistingActivePositionAssignments(Position $position): bool
    {
        return TrainingPositionAssignment::whereHas('position', function (Builder $builder) use (&$position) {
            $builder->where('id', $position->id);
        })->get()->isEmpty();
    }

    public function createAssignment(Position $position)
    {
        throw_if(! $this->checkExistingActivePositionAssignments($position),
            PositionAlreadyAssignedForTrainingException::class);

        return TrainingPositionAssignment::create(['position_id' => $position->id]);
    }
}
