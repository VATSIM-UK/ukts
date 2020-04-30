<?php

namespace App\Modules\Availability\Mutations;

use App\Modules\Availability\Availability;
use App\Modules\Availability\Exceptions\OverlappingAvailabilityException;
use App\Modules\Availability\Services\AvailabilityService;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AvailabilityHandler
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case,
     * it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name,
     * path to the field from the root, and more.
     * @return Availability
     * @throws BindingResolutionException
     */
    public function update($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $service = app()->make(AvailabilityService::class);

        $args['user_id'] = Auth::user()->id;

        $service->updateExistingAvailability($args);

        return Availability::findOrFail($args['id']);
    }

    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case,
     * it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state,
     * the field name, path to the field from the root, and more.
     * @return mixed
     * @throws OverlappingAvailabilityException
     * @throws BindingResolutionException
     */
    public function create($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $service = app()->make(AvailabilityService::class);

        $args['user_id'] = Auth::user()->id;

        return $service->createAvailability($args);
    }

    /**
     * Remove date range.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case,
     * it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo $resolveInfo Information about the query itself, such as the execution state,
     * the field name, path to the field from the root, and more.
     * @return mixed
     * @throws BindingResolutionException
     */
    public function remove($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $service = app()->make(AvailabilityService::class);

        $args['user_id'] = Auth::user()->id;

        return $service->removeAvailabilityRange($args);
    }
}
