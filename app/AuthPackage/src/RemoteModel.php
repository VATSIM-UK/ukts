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

    public static function find($id, $columns = null, $token = null)
    {
        $query = new Builder(static::$singleMethod, static::generateParams($columns), "id:$id");
        $response = $query->execute($token);
        return !$response->isEmpty() ? static::initModelWithData($response->getResults()) : null;
    }

    public static function findMany(array $ids, $columns = null, $token = null)
    {
        $argument = "ids:" . json_encode($ids);
        $query = new Builder(static::$manyMethod, static::generateParams($columns), $argument);
        $response = $query->execute($token);

        if (!$response->isEmpty()) {
            return null;
        }

        $collection = new Collection();
        foreach ($response->getResults() as $user) {
            $collection->push(static::initModelWithData($user));
        }
        return $collection;
    }

    public function fresh($columns = null)
    {
        return static::find($this->id, $columns);
    }

    /**
     * Sets the Builder class to use
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|Model|RemoteBuilder
     */
    public function newEloquentBuilder($query)
    {
        if($this->auth_token){
            return new RemoteBuilder($query, $this->auth_token);
        }
        return new RemoteBuilder($query);
    }


    /**
     * Generates a list of fields to get for the user model, using defaults or supplied list of fields
     *
     * @param $columns
     * @return array
     */
    public static function generateParams($columns)
    {
        if ($columns == ['*']) {
            $columns = null;
        }

        if ($columns) {
            $columns = array_filter($columns, function ($item){
                return !is_null($item) && $item != '';
            });
        }

        return array_unique(array_merge(['id'], $columns ? $columns : static::$defaultFields));
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
