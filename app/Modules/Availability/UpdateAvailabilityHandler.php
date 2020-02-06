<?php

namespace App\Modules\Availability;

use App\Exceptions\OverlappingAvailabilityException;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UpdateAvailabilityHandler
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @throws OverlappingAvailabilityExceptionException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $service = app()->make(AvailabilityService::class);

        $args['user_id'] = Auth::user()->id;

        $service->updateExistingAvailability($args);

        return Availability::findOrFail($args['id']);
    }
}
