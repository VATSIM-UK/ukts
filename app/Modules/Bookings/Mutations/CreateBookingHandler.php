<?php

namespace App\Modules\Bookings\Mutations;

use App\Modules\Bookings\Exceptions\OverlappingBookingException;
use App\Modules\Bookings\Services\BookingsService;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CreateBookingHandler
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Contains the result returned from the parent field. Always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself.
     * @return mixed
     *
     * @throws RatingRequirementNotMetException
     * @throws SpecialEndorsementNotAttainedException
     * @throws OverlappingBookingException
     * @throws BindingResolutionException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $service = app()->make(BookingsService::class);

        $args['user_id'] = Auth::user()->id;

        return $service->createBooking($args);
    }
}
