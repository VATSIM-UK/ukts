<?php

namespace App\Modules\Endorsement\Special\Mutations;

use App\Modules\Endorsement\Special\Assignment;
use App\Modules\Endorsement\Special\Exceptions\EndorsementAlreadyGrantedException;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AssignSpecialEndorsement
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     * @throws EndorsementAlreadyGrantedException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (Assignment::where([
                'user_id' => $args['user_id'],
                'endorsement_id' => $args['endorsement_id']
            ])->first() !== null) {
            throw new EndorsementAlreadyGrantedException();
        }
        return Assignment::create([
            'user_id' => $args['user_id'],
            'endorsement_id' => $args['endorsement_id'],
            'granted_by' => $args['granted_by']
        ]);
    }
}
