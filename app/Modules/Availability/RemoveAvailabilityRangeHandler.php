<?php

namespace App\Modules\Availability;

use App\Modules\Availability\AvailabilityInPastException;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class RemoveAvailabilityRangeHandler
{
    /**
     * Remove date range
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @throws AvailabilityInPastException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $service = app()->make(AvailabilityService::class);

        $args['user_id'] = Auth::user()->id;

       return $service->removeAvailabilityRange($args);
    }
}
