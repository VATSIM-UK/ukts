<?php

namespace App\Modules\Position\Mutations;

use App\User;
use App\Modules\Position\TrainingPosition;
use App\Modules\Position\Services\TrainingPositionSessionService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class RevokeSessionRightsHandler
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
        $user = User::findOrFail($args['user_id']);
        $position = TrainingPosition::findOrFail($args['training_position_id']);

        return app()->make(TrainingPositionSessionService::class)->revokePermissions($user, $position);
    }
}
