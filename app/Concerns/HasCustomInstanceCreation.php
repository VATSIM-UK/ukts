<?php


namespace App\Concerns;


trait HasCustomInstanceCreation
{
    /**
     * Create a new model instance for a related model.
     *
     * @param  string  $class
     * @return mixed
     */
    protected function newRelatedInstance($class)
    {
        if(is_object($class)){
            return $class;
        }

        return tap(new $class, function ($instance) {
            if (! $instance->getConnectionName()) {
                $instance->setConnection($this->connection);
            }
        });
    }
}
