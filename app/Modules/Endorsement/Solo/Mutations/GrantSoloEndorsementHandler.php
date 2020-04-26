<?php

namespace App\Modules\Endorsement\Solo\Mutations;

use App\Modules\Bookings\Exceptions\OverlappingBookingException;
use App\Modules\Endorsement\Solo\Services\SoloEndorsementService;
use App\Modules\Position\Position;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class GrantSoloEndorsementHandler
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @throws OverlappingBookingException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /** @var SoloEndorsementService $service */
        $service = app()->make(SoloEndorsementService::class);

        $position = Position::find($args['position_id']);
        $user = resolve(User::class)->findOrFail($args['user_id']);

        return $service->grantSoloEndorsement($position, $user);
    }
}
