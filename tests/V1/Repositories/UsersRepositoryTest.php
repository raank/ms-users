<?php

namespace Tests\V1\Api;

use Faker\Factory;
use Tests\TestCase;
use App\Models\V1\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use App\Repositories\V1\UserRepository;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UsersRepositoryTest extends TestCase
{
    /**
     * The factory of Faker.
     *
     * @var mixed
     */
    protected $faker;

    /**
     * The User Repository Class.
     *
     * @var UserRepository
     */
    protected $repository;

    /**
     * The constructor method.
     */
    public function __construct()
    {
        $this->faker = Factory::create();
        $this->repository = (new UserRepository);

        parent::__construct();
    }

    /**
     * Testing all items from repository.
     *
     * @return void
     */
    public function testAll()
    {
        $perPage = 15;

        $model = User::all();

        $repository = $this->repository
            ->all($perPage)
            ->toArray();
        
        $this->assertEquals($perPage, $repository['per_page']);
        $this->assertEquals(1, $repository['current_page']);
        $this->assertEquals($model->count(), $repository['total']);
        $this->assertArrayHasKey('links', $repository);
    }

    /**
     * Testing of Storing Item.
     *
     * @return User
     */
    public function testStore(): User
    {
        $data = [
            'name' => $this->faker->name,
            'username' => $this->faker->username,
            'email' => $this->faker->email,
            'active' => true,
            'document' => '123456789'
        ];

        $repository = $this->repository
            ->store($data);

        $this->assertEquals($data['name'], $repository->name);
        $this->assertNull($repository->deleted_at);
        $this->assertTrue($repository->active);

        return $repository;
    }

    /**
     * The finder user Testing.
     *
     * @param array ...$args
     * @depends testStore
     * @return User
     */
    public function testFind(...$args): User
    {
        $user = Arr::first($args);

        $this->assertEquals(User::class, get_class($user));

        $repository = $this->repository
            ->find($user->_id);

        $this->assertEquals($user, $repository);

        return $repository;
    }

    /**
     * The update user Testing.
     *
     * @param array ...$args
     * @depends testFind
     * @return User
     */
    public function testUpdate(...$args): User
    {
        $data = [
            'name' => $this->faker->name
        ];

        $user = Arr::first($args);

        $this->assertEquals(User::class, get_class($user));

        $repository = $this->repository
            ->update($user->_id, $data);

        $this->assertTrue($repository);

        return $user;
    }

    /**
     * The destroy user Testing.
     *
     * @param array ...$args
     * @depends testUpdate
     * @return void
     */
    public function testDestroy(...$args)
    {
        $user = Arr::first($args);

        $this->assertEquals(User::class, get_class($user));

        $repository = $this->repository
            ->destroy($user->_id);

        $this->assertTrue($repository);
    }
}
