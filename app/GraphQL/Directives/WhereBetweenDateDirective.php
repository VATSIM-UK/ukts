<?php

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgBuilderDirective;

class WhereBetweenDateDirective extends BaseDirective implements ArgBuilderDirective
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
        return $builder->whereBetween(
            $this->directiveArgValue('key', $this->definitionNode->name->value),
            $values
        );
    }
}
