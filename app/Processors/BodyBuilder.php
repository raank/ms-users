<?php

namespace App\Processors;

class BodyBuilder
{
    protected $body;

    public function __construct(array $body)
    {
        $this->body = $body;
    }

    public function builder(string $model)
    {
        $body = $this->body;

        foreach ($body as $key => $values) {
            if (method_exists($this, $key)) {
                $body[$key] = call_user_func_array([$this, sprintf('treat%s', ucfirst($key))], [$values]);
            }
        }

        $builder = $model::whereNotNull(
            (new $model)->getKeyName()
        );

        foreach ($body as $method => $values) {
            if ($method === 'where') {
                \Log::info($method, $values);
                foreach ($values as $where) {
                    [$field, $operator, $value] = $where;

                    $builder->where(
                        $field,
                        $operator,
                        $value
                    );
                }
            }
        }

        return $builder->paginate(1);
    }

    /**
     * If where in body.
     *
     * @param array $body
     *
     * @return void
     */
    private function treatWhere($values)
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
}