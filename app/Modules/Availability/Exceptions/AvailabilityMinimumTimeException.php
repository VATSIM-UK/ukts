<?php

namespace App\Modules\Availability\Exceptions;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Throwable;

class AvailabilityMinimumTimeException extends Exception implements RendersErrorsExtensions
{
    protected $defaultMessage = 'The minimum availability time is ';

    public function __construct($msg = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($this->defaultMessage .= ($msg .= ' minutes'), $code, $previous);
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
