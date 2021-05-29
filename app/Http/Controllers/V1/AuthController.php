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

/**
 * @OA\Schema(
 *  schema="v1.token",
 *  type="object",
 *  description="Response auth token",
 *  @OA\Property(property="token", type="string", description="Token access", example="abc1234defg"),
 *  @OA\Property(property="type", type="string", description="Type of Token", example="Bearer"),
 *  @OA\Property(property="expires", type="integer", description="Expires token in", example=3600)
 * )
 * 
 * @OA\Schema(
 *  schema="v1.auth_response",
 *  type="object",
 *  description="Response data of Authentication",
 *  @OA\Property(property="auth", ref="#/components/schemas/v1.token"),
 *  @OA\Property(property="user", ref="#/components/schemas/v1.model_user"),
 * )
 */
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
     * User Registering on Application.
     *
     * @OA\Post(
     *  tags={"v1.auth"},
     *  path="/v1/auth/register",
     *  @OA\Response(
     *      response="201",
     *      description="Information has been successfully registered",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Information has been successfully registered"),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/v1.auth_response"
     *              )
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="422",
     *      description="There is some incorrect information",
     *      @OA\JsonContent(ref="#/components/schemas/Validation"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="name", type="string", description="The name of user."),
     *              @OA\Property(property="email", type="string", description="The email of user."),
     *              @OA\Property(property="password", type="string", description="The password of user."),
     *              @OA\Property(property="password_confirmation", type="string", description="The password confirmation."),
     *              @OA\Property(property="username", type="string", description="The username of user."),
     *              @OA\Property(property="document", type="string", description="The document of user."),
     *              required={"name", "email", "username", "password", "password_confirmation"},
     *              example={
     *                  "name": "John Doe",
     *                  "email": "john@doe.com",
     *                  "password": "password123",
     *                  "password_confirmation": "password123",
     *                  "username": "john.doe",
     *                  "document": "12345678"
     *              }
     *          )
     *      )
     *  )
     * )
     *
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

        $auth = [
            'token' => Auth::attempt($request->only(['email', 'password'])),
            'type' => 'bearer',
            'expires' => auth()->factory()->getTTL() * 60
        ];

        return $this->response(
            compact('auth', 'user'),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * User login on Application.
     *
     * @OA\Post(
     *  tags={"v1.auth"},
     *  path="/v1/auth/login",
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/v1.auth_response"
     *              )
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="422",
     *      description="There is some incorrect information",
     *      @OA\JsonContent(ref="#/components/schemas/Validation"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="email", type="string", description="The email of user."),
     *              @OA\Property(property="password", type="string", description="The password of user."),
     *              required={"email", "password"},
     *              example={
     *                  "email": "john@doe.com",
     *                  "password": "password123"
     *              }
     *          )
     *      )
     *  )
     * )
     *
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

        $auth = [
            'token' => $token,
            'type' => 'bearer',
            'expires' => auth()->factory()->getTTL() * 60
        ];

        return $this->response(
            compact('auth', 'user')
        );
    }

    /**
     * User forgot password.
     *
     * @OA\Post(
     *  tags={"v1.auth"},
     *  path="/v1/auth/forgot",
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  ),
     *  @OA\Response(
     *      response="422",
     *      description="There is some incorrect information",
     *      @OA\JsonContent(ref="#/components/schemas/Validation"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="email", type="string", description="The email of user."),
     *              required={"email", "password"},
     *              example={
     *                  "email": "john@doe.com"
     *              }
     *          )
     *      )
     *  )
     * )
     *
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

        $this->messages
            ->store(
                $job->dispatch()
            );

        return $this->response();
    }

    /**
     * User reset password.
     *
     * @OA\Post(
     *  tags={"v1.auth"},
     *  path="/v1/auth/reset/{remember_token}",
     *  @OA\Parameter(
     *      name="remember_token",
     *      in="path",
     *      required=true,
     *      description="Remember token of User",
     *      example="ABc123DefG",
     *      @OA\Schema(
     *          type="string"
     *      )
     *  ),
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  ),
     *  @OA\Response(
     *      response="422",
     *      description="There is some incorrect information",
     *      @OA\JsonContent(ref="#/components/schemas/Validation"),
     *  ),
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="password", type="string", description="The password of user."),
     *              @OA\Property(property="password_confirmation", type="string", description="The password confirmation."),
     *              required={"password", "password_confirmation"},
     *              example={
     *                  "password": "password123",
     *                  "password_confirmation": "password123"
     *              }
     *          )
     *      )
     *  )
     * )
     *
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

        return $this->response();
    }

    /**
     * Checking if user is authenticated.
     *
     * @OA\Head(
     *  tags={"v1.auth"},
     *  path="/v1/auth/check",
     *  security={
     *      {"bearerAuth": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="401",
     *      description="You are not authorized for this action",
     *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  )
     * )
     *
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
     * User refresh token.
     *
     * @OA\Get(
     *  tags={"v1.auth"},
     *  path="/v1/auth/refresh",
     *  security={
     *      {"bearerAuth": {}}
     *  },
     *  @OA\Response(
     *      response="200",
     *      description="Successful action",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/v1.auth_response"
     *              )
     *          )
     *      )
     *  ),
     *  @OA\Response(
     *      response="400",
     *      description="This information could not be processed",
     *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
     *  ),
     *  @OA\Response(
     *      response="401",
     *      description="You are not authorized for this action",
     *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
     *  ),
     *  @OA\Response(
     *      response="404",
     *      description="This information could not be found",
     *      @OA\JsonContent(ref="#/components/schemas/Notfound"),
     *  )
     * )
     *
     * @inheritDoc
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!isset($user)) {
            return $this->unauthorized();
        }

        $auth = [
            'token' => auth()->refresh(),
            'type' => 'bearer',
            'expires' => auth()->factory()->getTTL() * 60
        ];

        return $this->response(
            compact('auth', 'user')
        );
    }
}
