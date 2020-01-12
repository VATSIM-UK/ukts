<?php

namespace App\Modules\Position;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class PositionNotAssignedForTrainingException extends Exception implements RendersErrorsExtensions
{
    protected $message = 'The given position has not been assigned for training so cannot be deleted.';

    /**
     * Returns true when exception message is safe to be displayed to a client.
     *
     * @return bool
     *
     * @api
     */
    public function isClientSafe()
    {
        return true;
    }

    /**
     * Returns string describing a category of the error.
     *
     * Value "graphql" is reserved for errors produced by query parsing or validation, do not use it.
     *
     * @return string
     *
     * @api
     */
    public function getCategory()
    {
        return 'validation';
    }

    /**
     * Return the content that is put in the "extensions" part
     * of the returned error.
     *
     * @return array
     */
    public function extensionsContent(): array
    {
        return [
            'code' => 404,
        ];
    }
}
