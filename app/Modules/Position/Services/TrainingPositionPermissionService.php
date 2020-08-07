<?php

namespace App\Modules\Position\Services;

use App\Modules\Position\Exceptions\PermissionsAlreadyGrantedException;
use App\Modules\Position\Exceptions\PermissionsAlreadyRevokedException;
use App\Modules\Position\TrainingPosition;
use App\User;

class TrainingPositionPermissionService
{
    public function grantPermissions(User $user, TrainingPosition $position): bool
    {
        throw_if(
            $position->students()->where('user_id', '==', $user->id),
            PermissionsAlreadyGrantedException::class
        );

        return $position->students()->save($user);
    }

    public function revokePermissions(User $user, TrainingPosition $position): bool
    {
        throw_if(
            $position->students()->where('user_id', '==', $user->id),
            PermissionsAlreadyRevokedException::class
        );

        return $position->students()->detach($user);
    }
}
