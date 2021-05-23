<?php

namespace App\Repositories\V1;

use App\Models\V1\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Processors\BodyBuilder;
use App\Models\V1\User as Model;
use Illuminate\Support\Facades\Hash;
use App\Repositories\RepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements RepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(int $perPage = 20): LengthAwarePaginator
    {
        return Model::orderBy('created_at', 'DESC')
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function store(array $data)
    {
        $fillable = [];

        foreach ((new Model)->getFillable() as $key) {
            $fillable[$key] = $data[$key] ?? null;
        }

        return Model::create($fillable);
    }

    /**
     * @inheritDoc
     */
    public function find(string $id)
    {
        return Model::find($id);
    }

    /**
     * @inheritDoc
     */
    public function update(string $id, array $data)
    {
        return Model::find($id)
            ->update($data);
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id)
    {
        return Model::find($id)
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function search(Request $request): LengthAwarePaginator
    {
        return (new BodyBuilder($request->all()))
            ->builder(Model::class);
    }

    /**
     * Find user by field and value.
     *
     * @param string $field
     * @param mixed $value
     *
     * @return Model
     */
    public function findByField(string $field, $value)
    {
        return Model::where($field, '=', $value)->first();
    }
}