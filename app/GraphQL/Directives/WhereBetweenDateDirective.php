<?php

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Between\WhereBetweenDirective;

class WhereBetweenDateDirective extends WhereBetweenDirective
{
    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name(): string
    {
        return 'whereBetweenDate';
    }

    /**
     * Apply a "WHERE BETWEEN" clause.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
     * @param  mixed  $values
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function handleBuilder($builder, $values)
    {
        $values['to'] = $values['to']->endOfDay();
        return parent::handleBuilder($builder, $values);
    }
}
