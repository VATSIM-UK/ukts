<?php

namespace App\Modules\Bookings;

use App\Exceptions\OverlappingBookingException;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CreateBookingHandler
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     * @throws RatingRequirementNotMetException
     * @throws SpecialEndorsementNotAttainedException
     * @throws OverlappingBookingException
     * @throws BindingResolutionException
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $service = app()->make(BookingsServiceInterface::class);

        $args['user_id'] = Auth::user()->id;

        return $service->createBooking($args);
    }
}
