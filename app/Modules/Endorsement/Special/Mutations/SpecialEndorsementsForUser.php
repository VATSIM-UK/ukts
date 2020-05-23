<?php

namespace App\Modules\Endorsement\Special\Mutations;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class SpecialEndorsementsForUser
{
    private $userInstance;

    public function __construct(User $user)
    {
        // Inject user through service container
        $this->userInstance = $user;
    }

    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself.
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (! $user = $this->userInstance::find($args['user_id'])) {
            throw new \InvalidArgumentException('User does not exist');
        }

        return $user->specialEndorsements;
    }
}
