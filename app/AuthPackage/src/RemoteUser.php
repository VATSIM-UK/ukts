<?php


namespace VATSIMUK\Auth\Remote;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use VATSIMUK\Auth\Remote\GraphQL\Builder;

class RemoteUser extends Model
{
    protected static $unguarded = true;

    private static $defaultFields = [
        "name_first",
        "name_last",
    ];

    /*
     * Default Function Overrides
     */

    public static function find($id, $fields = null)
    {
        $query = new Builder('user', self::generateParams($fields), "id:$id");
        $response = $query->execute();
        return !$response->isEmpty() ? self::initModelWithData($response->getResults()) : null;
    }

    public static function findMany(array $ids, $fields = null)
    {
        $argument = "ids:" . json_encode($ids);
        $query = new Builder('users', self::generateParams($fields), $argument);
        $response = $query->execute();

        if (!$response->isEmpty()) {
            return null;
        }

        $collection = new Collection();
        foreach ($response->getResults() as $user) {
            $collection->push(self::initModelWithData($user));
        }
        return $collection;
    }

    /**
     * Sets the Builder class to use
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|Model|RemoteBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new RemoteBuilder($query);
    }


    /**
     * Generates a list of fields to get for the user model, using defaults or supplied list of fields
     *
     * @param $fields
     * @return string
     */
    private static function generateParams($fields)
    {
        if ($fields == ['*']) {
            $fields = null;
        }
        return array_merge(['id'], $fields ? $fields : self::$defaultFields);
    }


    /**
     * Creates an instance of the model with the given data filled
     *
     * @param $data
     * @return RemoteUser
     */
    private static function initModelWithData($data)
    {
        $model = new self();

        return $model->fill((array)$data);
    }
}
