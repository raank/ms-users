<?php

namespace App\Processors;

use Jenssegers\Mongodb\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class BodyBuilder
{
    /**
     * The body to Processing.
     *
     * @var array
     */
    protected $body;

    /**
     * Per page items.
     *
     * @var integer
     */
    protected $perPage;

    /**
     * @param array $body
     * @param integer $perPage
     */
    public function __construct(array $body, int $perPage = 10)
    {
        $this->body = $body;
        $this->perPage = $perPage;
    }

    /**
     * Mounting the builder.
     *
     * @param string $model
     *
     * @return LengthAwarePaginator
     */
    public function builder(string $model): LengthAwarePaginator
    {
        $body = $this->body;

        foreach ($body as $key => $values) {
            if (method_exists($this, sprintf('treat%s', ucfirst($key)))) {
                $body[$key] = call_user_func_array([$this, sprintf('treat%s', ucfirst($key))], [$values]);
            }
        }

        $builder = $model::whereNotNull(
            (new $model)->getKeyName()
        );

        foreach ($body as $method => $values) {
            $method = sprintf('query%s', ucfirst($method));
            if (method_exists($this, $method)) {
                $this->{$method}($builder, $values);
            }
        }

        return $builder->paginate(1);
    }

    /**
     * If where in body.
     *
     * @param array $body
     *
     * @return array
     */
    private function treatWhere($values): array
    {
        $query = [];

        foreach ($values as $idx => $where) {
            if (count($where) === 3) {
                [$field, $operator, $value] = $where;

                if (strtoupper($operator) === 'LIKE' && strpos($value, '%') === false) {
                    $value = '%' . $value . '%';
                }

                $query[$idx] = [$field, $operator, $value];
            } elseif (count($where) === 2) {
                [$field, $value] = $where;

                $query[$idx] = [$field, '=', $value];
            }
        }

        return $query;
    }

    /**
     * If where in body.
     *
     * @param array $body
     *
     * @return array
     */
    private function treatWhereDate($values): array
    {
        $query = [];

        foreach ($values as $idx => $where) {
            if (count($where) === 3) {
                [$field, $operator, $value] = $where;

                if (strtoupper($operator) === 'LIKE' && strpos($value, '%') === false) {
                    $value = '%' . $value . '%';
                }

                $query[$idx] = [$field, $operator, $value];
            } elseif (count($where) === 2) {
                [$field, $value] = $where;

                $query[$idx] = [$field, '=', $value];
            }
        }

        return $query;
    }

    /**
     * Builder to where.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryWhere(Builder &$builder, array $values): void
    {
        foreach ($values as $where) {
            [$field, $operator, $value] = $where;

            $builder->where(
                $field,
                $operator,
                $value
            );
        }
    }

    /**
     * Builder to where not null.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryWhereNotNull(Builder &$builder, array $values): void
    {
        foreach ($values as $value) {
            $builder->whereNotNull($value);
        }
    }

    /**
     * Builder to where null.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryWhereNull(Builder &$builder, array $values): void
    {
        foreach ($values as $value) {
            $builder->whereNull($value);
        }
    }

    /**
     * Builder to where betweeen.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryWhereBetween(Builder &$builder, array $values): void
    {
        foreach ($values as $row) {
            foreach ($row as $field => $array) {
                if (count($array) === 2) {
                    $builder->orWhereBetween($field, $array);
                }
            }
        }
    }

    /**
     * Builder to order by.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryOrderBy(Builder &$builder, array $values): void
    {
        foreach ($values as $item) {
            $builder->orderBy($item['field'], $item['order'] ?? 'DESC');
        }
    }

    /**
     * Builder to where date.
     *
     * @param Builder $builder
     * @param array $values
     *
     * @return void
     */
    private function queryWhereDate(Builder &$builder, array $values): void
    {
        foreach ($values as $where) {
            [$field, $operator, $value] = $where;

            $builder->whereDate(
                $field,
                $operator,
                $value
            );
        }
    }
}