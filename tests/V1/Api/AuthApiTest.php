<?php

namespace Tests\V1\Api;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthApiTest extends TestCase
{
    /**
     * The factory of Faker.
     *
     * @var mixed
     */
    protected $faker;

    /**
     * The constructor method.
     */
    public function __construct()
    {
        $this->faker = Factory::create();

        parent::__construct();
    }

    /**
     * A basic test environment.
     *
     * @return void
     */
    public function testEnvironment()
    {
        $this->assertEquals('testing', env('APP_ENV'));
    }

    /**
     * Testing register endpoint.
     *
     * @return array
     */
    public function testRegister(): array
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'username' => $this->faker->username,
            'password' => '123@mudar',
            'password_confirmation' => '123@mudar'
        ];

        $status = Response::HTTP_CREATED;

        $this->json('POST', '/api/v1/auth/register', $data)
            ->seeJson([
                'message' => $this->makeMessage($status)
            ])->seeStatusCode($status);

        return $data;
    }

    /**
     * Testing register endpoint with errors.
     *
     * @param mixed ...$args
     * 
     * @depends testRegister
     * @return array
     */
    public function testRegisterError(...$args): array
    {
        $data = Arr::first($args);

        $status = Response::HTTP_UNPROCESSABLE_ENTITY;

        $this->json('POST', '/api/v1/auth/register', $data)
            ->seeJson([
                'message' => $this->makeMessage($status)
            ])->seeStatusCode($status);

        return $data;
    }

    /**
     * Testing login endpoint.
     *
     * @depends testRegisterError
     * @return array
     */
    public function testLogin(...$args)
    {
        $data = Arr::first($args);
        $credentials = Arr::only($data, ['email', 'password']);

        $status = Response::HTTP_OK;

        $this->json('POST', '/api/v1/auth/login', $credentials)
            ->seeJson([
                'message' => $this->makeMessage($status)
            ])->seeStatusCode($status);
    }

    /**
     * Testing login endpoint with error on email.
     *
     * @return array
     */
    public function testLoginErrorEmail()
    {
        $status = Response::HTTP_UNPROCESSABLE_ENTITY;

        $this
            ->json(
                'POST',
                '/api/v1/auth/login',
                [
                    'email' => 'email@example.com',
                    'password' => '1234'
                ]
            )
            ->seeJson([
                'message' => $this->makeMessage($status)
            ])->seeStatusCode($status);
    }

    /**
     * Teting check token.
     *
     * @depends testRegister
     * @return void
     */
    public function testCheckAndRefresh(...$args)
    {
        $data = Arr::first($args);
        $credentials = Arr::only($data, ['email', 'password']);
        $status = Response::HTTP_OK;

        $login = $this
            ->json(
                'POST',
                '/api/v1/auth/login',
                $credentials
            );

        $this
            ->seeJson([
                'message' => $this->makeMessage($status)
            ])
            ->seeStatusCode($status);

        if ($login->response->getStatusCode() === $status) {
            $response = json_decode($login->response->getContent(), true);
    
            $token = $response['data']['auth']['token'];
            
            $this
                ->json(
                    'HEAD',
                    '/api/v1/auth/check',
                    [],
                    ['Authorization' => 'Bearer ' . $token]
                )
                ->seeStatusCode(Response::HTTP_OK);

            $this
                ->json(
                    'HEAD',
                    '/api/v1/auth/refresh',
                    [],
                    ['Authorization' => 'Bearer ' . $token]
                )
                ->seeStatusCode(Response::HTTP_OK);

        }
    }

    /**
     * Teting check token.
     *
     * @depends testRegister
     * @return void
     */
    public function testForgot(...$args)
    {
        $data = Arr::first($args);
        $email = Arr::get($data, 'email');

        $status = Response::HTTP_OK;

        $this
            ->json(
                'POST',
                '/api/v1/auth/forgot',
                compact('email')
            )
            ->seeJson([
                'message' => $this->makeMessage($status)
            ])
            ->seeStatusCode($status);
    }

    /**
     * Teting check token.
     *
     * @depends testRegister
     * @return void
     */
    public function testResetPassword(...$args)
    {
        $data = Arr::first($args);
        $email = Arr::get($data, 'email');
        $rememberToken = \App\Models\V1\User::where('email', '=', $email)
            ->first()
            ->remember_token;

        $status = Response::HTTP_OK;

        $this
            ->json(
                'POST',
                '/api/v1/auth/reset/' . $rememberToken,
                [
                    'password' => '12345',
                    'password_confirmation' => '12345'
                ]
            )
            ->seeJson([
                'message' => $this->makeMessage($status)
            ])
            ->seeStatusCode($status);
    }
}
