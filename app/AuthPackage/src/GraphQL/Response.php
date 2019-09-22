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

    /**
     * Returns whether the response contains errors
     * @return bool
     */
    public function hasErrors()
    {
        return isset($this->jsonResponse->errors);
    }

    /**
     * Returns whether the body of the data response is empty
     * @return bool
     */
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

    /**
     * Gets the results from the query
     * @return \stdClass|null
     */
    public function getResults()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->jsonResponse->data->{$this->query->getMethod()};
    }


    /**
     * Gets the errors from the query
     * @return array|null
     */
    public function getErrors()
    {
        if (!$this->hasErrors()) {
            return null;
        }
        return $this->jsonResponse->errors;
    }
}
