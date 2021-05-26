<?php

namespace App\Http\Controllers\V1;

use App\Models\V1\User;
use App\Processors\AwsSQS;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\SendMailMailgunSQS;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Repositories\V1\UserRepository;
use App\Repositories\V1\MessageRepository;
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
     * @var MessageRepository
     */
    protected $messages;

    /**
     * Create a new controller instance.
     *
     * @param UserRepository $repository
     *
     * @return void
     */
    public function __construct(
        UserRepository $repository,
        MessageRepository $messages
    ) {
        $this->repository = $repository;
        $this->messages = $messages;

        $this->middleware(
            'auth:api',
            [
                'except' => [
                    'register',
                    'login',
                    'forgot',
                    'reset'
                ]
            ]
        );
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
            'password_confirmation' => ['required']
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
            $response, JsonResponse::HTTP_ACCEPTED
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
            return $this->invalidCredentials();
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

        $user = $this->repository->findByField('email', $request->get('email'));

        $request->merge([
            'url' => route('api.v1.auth.reset', [
                'token' => $user->remember_token
            ])
        ]);

        /** @var SendMailMailgunSQS | Sendmail with SQS */
        $job = (new SendMailMailgunSQS($user, $request->all()))
            ->setSubject(__('Reset your password!'))
            ->setMessageGroupId('forgot')
            ->setTemplate('forgot-password')
            ->onQueue(
                config('aws.sqs.queue')
            )
            ->setMessageAttributes(
                Arr::only(
                    $user->toArray(),
                    ['name', 'email', 'username']
                )
            );

        return $this->response(
            $this->messages
                ->store(
                    $job->dispatch()
                )
        );
    }

    /**
     * @inheritDoc
     */
    public function reset(Request $request, string $token): JsonResponse
    {
        $this->validate($request, [
            'password' => ['string', 'confirmed'],
            'password_confirmation' => ['required']
        ]);

        $request->merge([
            'remember_token' => Str::random(32)
        ]);

        $user = $this->repository
            ->findByField('remember_token', $token);

        if (!isset($user)) {
            return $this->notfound();
        }

        $updated = $this->repository
            ->update(
                $user->_id,
                $request->only([
                    'password',
                    'remember_token'
                ])
            );

        return $this->response(
            compact('updated')
        );
    }

    /**
     * @inheritDoc
     */
    public function check(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!isset($user)) {
            return $this->unauthorized();
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
            return $this->unauthorized();
        }

        return $this->response([
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
