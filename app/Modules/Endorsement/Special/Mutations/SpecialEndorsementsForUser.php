<?php

namespace App\Modules\Endorsement\Special\Mutations;

use App\Modules\Endorsement\Special\Assignment;
use App\Modules\Endorsement\Special\Exceptions\EndorsementAlreadyGrantedException;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class SpecialEndorsementsForUser
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if ((!$user = User::find($args['user_id']))) {
            throw new \InvalidArgumentException("User does not exist");
        }
        return $user->specialEndorsements;
    }
}
