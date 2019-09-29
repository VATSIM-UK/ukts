<?php


namespace VATSIMUK\Auth\Remote\RemoteEloquent;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class RemoteBuilder extends Builder
{
    private $token;
    public function __construct(QueryBuilder $query, $token = null)
    {
        $this->token = $token;
        parent::__construct($query);
    }

    public function get($columns = ['*'])
    {
        // Only support IDs for now
        $ids = [];
        foreach ($this->query->wheres as $where) {
            if ($this->getColumnNameWithoutTable($where['column']) != "id") {
                continue;
            }

            if (isset($where['values'])) {
                $ids = array_merge($ids, $where['values']);
            } else {
                $ids[] = $where['value'];
            }
        }

        if ($this->query->columns) {
            $columns = $this->query->columns;
        }

        return $this->model::findMany($ids, $columns, $this->token);
    }

    /**
     * Execute the query and get the first result.
     *
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|object|static|null
     */
    public function first($columns = ['*'])
    {
        // Only support IDs for now
        if ($this->getColumnNameWithoutTable($this->query->wheres[0]['column']) != "id") {
            return null;
        }
        return $this->model::find($this->query->wheres[0]['value'], $columns, $this->token);
    }

    private function getColumnNameWithoutTable($column)
    {
        return explode('.', $column)[1];
    }
}
