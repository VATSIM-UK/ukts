<?php

namespace App\Modules\Booking\Exceptions;

use DomainException;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class OverlappingBookingException extends DomainException implements RendersErrorsExtensions
{
    protected $message = "Can't have overlapping bookings for the same position!";

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
            'code' => 422,
        ];
    }
}
