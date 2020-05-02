<?php

namespace App\Modules\Position\Exceptions;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class PermissionsAlreadyRevokedException extends Exception implements RendersErrorsExtensions
{
    protected $message = 'This user never had any rights on this position.';

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
            'code' => 200,
        ];
    }
}
