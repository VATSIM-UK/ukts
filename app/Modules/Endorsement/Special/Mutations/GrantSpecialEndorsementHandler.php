<?php

namespace App\Modules\Endorsement\Special\Mutations;

use App\Modules\Endorsement\Special\EndorsementRequest;
use App\Modules\Endorsement\Special\Exceptions\EndorsementAlreadyGrantedException;
use App\Modules\Endorsement\Special\Services\SpecialEndorsementAssignment;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class GrantSpecialEndorsementHandler
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
        $request = EndorsementRequest::findOrFail($args['request_id']);
        $actioner = resolve(User::class)::findOrFail($args['actioner_id']);

        return (new SpecialEndorsementAssignment($request, $actioner))->handle();
    }
}
