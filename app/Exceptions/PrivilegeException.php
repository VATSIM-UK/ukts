<?php

namespace App\Exceptions;

use DomainException;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Throwable;

class PrivilegeException extends DomainException implements RendersErrorsExtensions
{
    protected $defaultMessage = "You do not have permission to ";

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($this->defaultMessage.= $message, $code, $previous);
    }

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
        return 'auth';
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
            'code' => 401,
        ];
    }
}
