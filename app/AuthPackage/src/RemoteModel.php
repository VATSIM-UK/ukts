<?php


namespace VATSIMUK\Auth\Remote;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use VATSIMUK\Auth\Remote\GraphQL\Builder;
use VATSIMUK\Auth\Remote\RemoteEloquent\RemoteBuilder;

abstract class RemoteModel extends Model
{
    protected static $singleMethod;
    protected static $manyMethod;
    protected static $defaultFields = [];
    public $exists = true;

    /*
     * Default Function Overrides
     */

    public static function find($id, $fields = null)
    {
        $query = new Builder(static::$singleMethod, static::generateParams($fields), "id:$id");
        $response = $query->execute();
        return !$response->isEmpty() ? static::initModelWithData($response->getResults()) : null;
    }

    public static function findMany(array $ids, $fields = null)
    {
        $argument = "ids:" . json_encode($ids);
        $query = new Builder(static::$manyMethod, static::generateParams($fields), $argument);
        $response = $query->execute();

        if (!$response->isEmpty()) {
            return null;
        }

        $collection = new Collection();
        foreach ($response->getResults() as $user) {
            $collection->push(static::initModelWithData($user));
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
     * @return array
     */
    public static function generateParams($fields)
    {
        if ($fields == ['*']) {
            $fields = null;
        }
        return array_merge(['id'], $fields ? $fields : static::$defaultFields);
    }

    /**
     * Creates an instance of the model with the given data filled
     *
     * @param $data
     * @return RemoteModel
     */
    public static function initModelWithData($data)
    {
        $model = new static();

        return $model->fill((array)$data);
    }
}
