<?php

namespace Tests\V1\Api;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UsersApiTest extends TestCase
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
     * Testing index route with pagination.
     *
     * @return void
     */
    public function testIndex()
    {
        $status = Response::HTTP_OK;

        $this
            ->json(
                'GET',
                '/api/v1/users',
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => $this->makeMessage($status),
                'current_page' => 1
            ])
            ->seeStatusCode($status);
    }

    /**
     * Testing search route with pagination.
     *
     * @return void
     */
    public function testSearch()
    {
        $status = Response::HTTP_OK;

        $this
            ->json(
                'POST',
                '/api/v1/users/search',
                [
                    'where' => [
                        ['email', 'LIKE', '@']
                    ],
                    
                    'whereNotNull' => ['email'],
                    'whereNull' => ['deleted_at'],
                    'orderBy' => [
                        ['field' => 'created_at', 'order' => 'DESC']
                    ],

                    //'whereBetween' => [
                    //    ['field' => ['FROM', 'TO']]
                    //]
                ],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => $this->makeMessage($status),
                'current_page' => 1
            ])
            ->seeStatusCode($status);
    }

    /**
     * Testing storing user route.
     *
     * @return array
     */
    public function testStore()
    {
        $status = Response::HTTP_OK;

        $this
            ->json(
                'POST',
                '/api/v1/users',
                [
                    'name' => $this->faker->name,
                    'email' => $this->faker->email,
                    'username' => $this->faker->username,
                    'document' => '123456789',
                    'password' => '12345',
                    'password_confirmation' => '12345'
                ],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => $this->makeMessage($status),
            ])
            ->seeStatusCode($status);
        
        return json_decode(
            $this->response->getContent(),
            true
        );
    }

    /**
     * Testing show user route.
     *
     * @depends testStore
     * @return array
     */
    public function testShow(...$args)
    {
        $data = Arr::first($args);
        $_id = Arr::get($data, 'data._id');

        $status = Response::HTTP_OK;

        $this
            ->json(
                'GET',
                '/api/v1/users/' . $_id,
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => $this->makeMessage($status)
            ])
            ->seeStatusCode($status);
        
        return json_decode(
            $this->response->getContent(),
            true
        );
    }

    /**
     * Testing show user error route.
     *
     * @return void
     */
    public function testShowNotFound(...$args)
    {
        $data = Arr::first($args);

        $status = Response::HTTP_NOT_FOUND;

        $this
            ->json(
                'GET',
                '/api/v1/users/faker-id',
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => $this->makeMessage($status)
            ])
            ->seeStatusCode($status);
    }

    /**
     * Testing update user route.
     *
     * @depends testShow
     * @return void
     */
    public function testUpdate(...$args)
    {
        $data = Arr::first($args);
        $_id = Arr::get($data, 'data._id');

        $status = Response::HTTP_OK;

        $this
            ->json(
                'PUT',
                '/api/v1/users/' . $_id,
                [
                    'name' => $this->faker->name
                ],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => $this->makeMessage($status),
            ])
            ->seeStatusCode($status);
    }

    /**
     * Testing destroy user route.
     *
     * @depends testShow
     * @return void
     */
    public function testDestroy(...$args)
    {
        $data = Arr::first($args);
        $_id = Arr::get($data, 'data._id');

        $status = Response::HTTP_OK;

        $this
            ->json(
                'DELETE',
                '/api/v1/users/' . $_id,
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => $this->makeMessage($status),
            ])
            ->seeStatusCode($status);
    }
}
