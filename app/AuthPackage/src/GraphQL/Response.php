<?php


namespace VATSIMUK\Auth\Remote\GraphQL;


class Response
{
    private $query;
    private $jsonResponse;

    public function __construct($jsonResponse, $query)
    {
        $this->jsonResponse = $jsonResponse;
        $this->query = $query;
    }

    public function hasErrors()
    {
        return isset($this->jsonResponse->errors);
    }

    public function isEmpty()
    {
        if (!$this->hasErrors() && $this->jsonResponse->data->{$this->query->getMethod()}) {
            return false;
        }
        return true;
    }

    /*
     * Getters
     */

    public function getResults()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->jsonResponse->data->{$this->query->getMethod()};
    }

    public function getErrors()
    {
        if (!$this->hasErrors()) {
            return null;
        }
        return $this->jsonResponse->errors;
    }
}
