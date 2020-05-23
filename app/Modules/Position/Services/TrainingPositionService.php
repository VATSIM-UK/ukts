<?php

namespace App\Modules\Position\Services;

use App\Modules\Position\Exceptions\PositionAlreadyAssignedForTrainingException;
use App\Modules\Position\Exceptions\PositionNotAssignedForTrainingException;
use App\Modules\Position\Position;
use App\Modules\Position\TrainingPosition;
use Illuminate\Database\Eloquent\Builder;

class TrainingPositionService
{
    public function checkHasExistingActivePositionAssignments(Position $position): bool
    {
        return is_null($this->findAssignmentByPosition($position)) ? false : true;
    }

    public function createAssignment(Position $position): TrainingPosition
    {
        throw_if(
            $this->checkHasExistingActivePositionAssignments($position),
            PositionAlreadyAssignedForTrainingException::class
        );

        return TrainingPosition::create(['position_id' => $position->id]);
    }

    public function removeAssignment(Position $position): bool
    {
        throw_if(
            ! $this->checkHasExistingActivePositionAssignments($position),
            PositionNotAssignedForTrainingException::class
        );

        return $this->findAssignmentByPosition($position)->delete();
    }

    private function findAssignmentByPosition(Position $position): ?TrainingPosition
    {
        return TrainingPosition::whereHas('position', function (Builder $builder) use (&$position) {
            $builder->where('id', $position->id);
        })->first();
    }
}
