<?php

namespace App\Modules\Position\Mutations;

use App\Modules\Position\Position;
use App\Modules\Position\Services\TrainingPositionService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CreateAssignmentHandler
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
        $position = Position::findOrFail($args['position_id']);

        return app()->make(TrainingPositionService::class)->createAssignment($position);
    }
}
