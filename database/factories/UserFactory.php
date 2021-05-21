<?php

namespace Database\Factories;

use App\Models\V1\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'type' => 'default',
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'username' => $this->faker->username,
            'password' => '1234@mudar',
            'active' => true,
            'document' => $this->faker->postcode,
            'remember_token' => Str::random(32),
            'deleted_at' => null
        ];
    }
}
