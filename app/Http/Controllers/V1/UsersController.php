<?php

namespace App\Http\Controllers\V1;

use App\Models\V1\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\V1\UserRepository;
use App\Http\Controllers\UsersControllerInterface;

class UsersController extends Controller implements UsersControllerInterface
{
    /**
     * The users repository.
     *
     * @var UserRepository
     */
    protected $repository;

    /**
     * Create a new controller instance.
     *
     * @param UserRepository $repository
     *
     * @return void
     */
    public function __construct(
        UserRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function index(Request $request): JsonResponse
    {
        return $this->response(
            $this->repository
                ->all(
                    (int) $request->query
                        ->get('perPage', 10)
                )
        );
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'type' => $request->get('type', 'default'),
            'active' => $request->get('active', true),
            'password' => $request->get('password', null),
            'remember_token' => $request->get('remember_token', Str::random(32))
        ]);

        $this->validate($request, [
            'name' => ['required', 'string'],
            'username' => ['string', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'document' => ['string'],
            'active' => ['boolean'],
            'password' => ['string', 'confirmed'],
        ]);

        return $this->response(
            $this->repository
                ->store(
                    $request->all()
                )
        );
    }

    /**
     * @inheritDoc
     */
    public function show(string $id): JsonResponse
    {
        $this->exists($id, User::class);

        return $this->response(
            $this->repository->find($id)
        );
    }

    /**
     * @inheritDoc
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $this->exists($id, User::class);

        $request->merge([
            'type' => $request->get('type', 'default'),
            'active' => $request->get('active', true),
            'password' => $request->get('password', null),
            'remember_token' => $request->get('remember_token', Str::random(32))
        ]);

        $this->validate($request, [
            'name' => ['required', 'string'],
            'username' => ['string', 'unique:users,username,' . $id . ',_id'],
            'email' => ['required', 'email', 'unique:users,email,' . $id . ',_id'],
            'document' => ['string'],
            'active' => ['boolean'],
            'password' => ['string', 'confirmed'],
        ]);

        return $this->response(
            $this->repository
                ->update(
                    $id,
                    $request->all()
                )
        );
    }

    /**
     * @inheritDoc
     */
    public function destroy(string $id): JsonResponse
    {
        $this->exists($id, User::class);

        $deleted = $this->repository
            ->destroy($id);

        return $this->response(
            compact('deleted')
        );
    }

    /**
     * @inheritDoc
     */
    public function search(Request $request): JsonResponse
    {
        return $this->response(
            $this->repository
                ->search($request)
        );
    }
}
