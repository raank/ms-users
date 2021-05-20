<?php

namespace App\Repositories\V1;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User as Model;
use App\Processors\BodyBuilder;
use App\Repositories\RepositoryInterface;

class UserRepository implements RepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function all(int $perPage = 20): array
    {
        return Model::orderBy('created_at', 'DESC')
            ->paginate($perPage)
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function store(array $data): array
    {
        $fillable = [];

        foreach ((new Model)->getFillable() as $key) {
            $fillable[$key] = $data[$key] ?? null;
        }

        return Model::create($fillable)
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function find(string $id): array
    {
        return Model::find($id)
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function update(string $id, array $data): array
    {
        $fillable = [];

        foreach ((new Model)->getFillable() as $key) {
            $fillable[$key] = $data[$key] ?? null;
        }

        Model::find($id)
            ->update($fillable);

        return Model::find($id)->toArray();
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): bool
    {
        return Model::find($id)
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function search(Request $request): array
    {
        return (new BodyBuilder($request->all()))
            ->builder(Model::class)
            ->toArray();
    }
}