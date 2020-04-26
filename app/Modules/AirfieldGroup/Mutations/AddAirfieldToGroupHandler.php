<?php

namespace App\Modules\AirfieldGroup\Mutations;

use App\Modules\Airfield\Airfield;
use App\Modules\AirfieldGroup\Exceptions\AirfieldGroupAlreadyAssignedException;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AddAirfieldToGroupHandler
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Airfield
    {
        $airfield = Airfield::find($args['icao']);

        throw_if($airfield->airfield_group_id != null, AirfieldGroupAlreadyAssignedException::class);

        $airfield->update(['airfield_group_id' => $args['airfieldGroup']]);

        return $airfield;
    }
}
