<?php


namespace VATSIMUK\Auth\Remote\GraphQL;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Cache;

class Builder
{
    private $method;
    private $columns;
    private $action;
    private $arguments;

    private $executedQuery;

    public function __construct(string $method, array $columns, string $arguments = null, string $action = 'query')
    {
        $this->method = $method;
        $this->arguments = $arguments;
        $this->columns = $columns;
        $this->action = $action;
    }

    /*
     * Action Functions
     */

    /**
     * Executes the query
     * @return Response|null
     */
    public function execute($token = null)
    {
        // Get / generate machine-machine authentication token
        if (!$token) {
            $token = $this->getAuthAccessToken();
            if (!$token) {
                return null;
            }
        }

        // Create client
        $client = new Client([
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);

        // Execute query
        $this->executedQuery = $this->getGraphQLQuery();

        try {
            $response = json_decode(
                $client->post(config('ukauth.root_url') . config('ukauth.graphql_path'),
                    ['form_params' => [
                        'query' => $this->getGraphQLQuery()
                    ]])->getBody()->getContents()
            );
        } catch (ConnectException $e) {
            //TODO: Log to Bugsnag
            return null;
        }

        return new Response($response, $this);
    }

    /**
     * Generates or fetches Auth service token
     * @return string|null
     */
    private function getAuthAccessToken()
    {
        $token = Cache::get('AUTH_API_TOKEN');

        if (!$token) {
            $guzzle = new Client;

            try {
                $response = $guzzle->post(config('ukauth.root_url') . config('ukauth.oauth_path') . '/token', [
                    'form_params' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => config('ukauth.machine_client_id'),
                        'client_secret' => config('ukauth.machine_client_secret'),
                        'scope' => '*'
                    ]
                ]);

                $token = json_decode((string)$response->getBody(), true)['access_token'];
                Cache::put('AUTH_API_TOKEN', $token, \DateInterval::createFromDateString("1 day"));
            } catch (\Exception $e) {
                // TODO: Log Exception. Likely either connection issue or output issue
                return null;
            }
        }
        return $token;
    }

    /*
     * Computed Functions
     */

    /**
     * Generate the complete GraphQL query
     * @return string
     */
    public function getGraphQLQuery()
    {
        $query = $this->action . " {\n";
        $query .= $this->method . ($this->arguments ? " ($this->arguments){\n" : " {\n");
        $query .= $this->getColumns();
        $query .= "}\n}";
        return $query;
    }

    /*
     * Generates the body of the GraphQL request
     * @return string
     */
    public function getColumns()
    {
        return $this->buildColumns($this->columns);
    }

    /*
     * Helper Functions
     */

    /**
     * Iterates through array of columns supplied, and converts into GraphQL query format
     * @param array $rawColumns
     * @return string
     */
    private function buildColumns(array $rawColumns)
    {
        $columnString = '';
        foreach ($rawColumns as $key => $column) {
            if (is_array($column)) {
                $columnString .= "$key {\n";
                $columnString .= $this->buildColumns($column);
                $columnString .= "}\n";
            } else {
                $columnString .= "$column\n";
            }
        }
        return $columnString;
    }

    /*
     * Getters
     */

    /**
     * Gets the GraphQL method
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
