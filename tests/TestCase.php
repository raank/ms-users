<?php

namespace Tests;

use Illuminate\Support\Facades\Crypt;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * The app token to Access.
     *
     * @var string
     */
    protected $appToken;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->appToken = Crypt::encrypt(env('APP_TOKEN'));
    }

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Making message.
     *
     * @param integer $status
     *
     * @return string
     */
    public function makeMessage(int $status): string
    {
        return __('status.' . $status);
    }
}
