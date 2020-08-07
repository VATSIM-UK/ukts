<?php

namespace App\Modules\Position\Mutations;

use App\Modules\Position\Services\TrainingPositionPermissionService;
use App\Modules\Position\TrainingPosition;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class RevokePermissionsHandler
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Contains the result returned from the parent field. Always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself.
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = resolve(User::class)->findOrFail($args['user_id']);
        $position = TrainingPosition::findOrFail($args['training_position_id']);

        return app()->make(TrainingPositionPermissionService::class)->revokePermissions($user, $position);
    }
}
