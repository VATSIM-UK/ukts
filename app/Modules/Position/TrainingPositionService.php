<?php

namespace App\Modules\Position;

use Illuminate\Database\Eloquent\Builder;

class TrainingPositionService
{
    public function checkHasExistingActivePositionAssignments(Position $position): bool
    {
        return is_null($this->findAssignmentByPosition($position)) ? false : true;
    }

    public function createAssignment(Position $position): TrainingPositionAssignment
    {
        throw_if($this->checkHasExistingActivePositionAssignments($position),
            PositionAlreadyAssignedForTrainingException::class);

        return TrainingPositionAssignment::create(['position_id' => $position->id]);
    }

    public function removeAssignment(Position $position): bool
    {
        throw_if(! $this->checkHasExistingActivePositionAssignments($position),
            PositionNotAssignedForTrainingException::class);

        return $this->findAssignmentByPosition($position)->delete();
    }

    private function findAssignmentByPosition(Position $position): ?TrainingPositionAssignment
    {
        return TrainingPositionAssignment::whereHas('position', function (Builder $builder) use (&$position) {
            $builder->where('id', $position->id);
        })->first();
    }
}
