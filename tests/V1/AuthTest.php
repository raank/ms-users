<?php

use Faker\Factory;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    protected $faker;

    public function __construct()
    {
        $this->faker = (new Factory());
    }

    /**
     * A basic test register.
     *
     * @return void
     */
    public function testRegister()
    {
        $this->json('POST', '/api/v1/auth/register', [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'username' => $this->faker->username,
            'document' => '0000',
            'password' => '1234@abcd',
            'password_confirmation' => '1234@abcd'
        ]);
    }
}
