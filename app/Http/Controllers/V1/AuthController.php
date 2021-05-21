<?php

namespace App\Http\Controllers\V1;

use App\Models\V1\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\V1\UserRepository;
use App\Http\Controllers\AuthControllerInterface;

class AuthController extends Controller implements AuthControllerInterface
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

        $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }

    /**
     * @inheritDoc
     */
    public function register(Request $request): JsonResponse
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

        $user = $this->repository
            ->store(
                $request->all()
            );
        

        $response = array_merge([
            'access_token' => Auth::attempt($request->only(['email', 'password'])),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ], $user->toArray());

        return $this->response(
            $response
        );
    }

    /**
     * @inheritDoc
     */
    public function login(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['string'],
        ]);

        $user = $this->repository
            ->findByField(
                'email',
                $request->get('email')
            );

        if (!$token = Auth::attempt($request->only(['email', 'password']))) {
            return $this->response(
                [
                    'message' => __('Unauthorized')
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $response = array_merge([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ], $user->toArray());

        return $this->response(
            $response
        );
    }

    /**
     * @inheritDoc
     */
    public function forgot(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = $this->repository
            ->findByField('email', $request->get('email'));

        return $this->response();
    }

    /**
     * @inheritDoc
     */
    public function check(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!isset($user)) {
            return $this->response(
                [
                    'message' => __('status.' . JsonResponse::HTTP_UNAUTHORIZED)
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        return $this->response(
            $user->toArray()
        );
    }

    /**
     * @inheritDoc
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!isset($user)) {
            return $this->response(
                [
                    'message' => __('Unauthorized')
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        return $this->response([
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
