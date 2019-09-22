<?php


namespace VATSIMUK\Auth\Remote;


use Illuminate\Database\Eloquent\Builder;

class RemoteBuilder extends Builder
{
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

        return $this->model::findMany($ids, $columns);
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
        return $this->model::find($this->query->wheres[0]['value'], $columns);
    }

    private function getColumnNameWithoutTable($column)
    {
        return explode('.', $column)[1];
    }
}
