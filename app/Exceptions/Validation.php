<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class Validation extends Exception
{
    /**
     * Define the CODE HTTP
     *
     * @var integer
     */
    protected const CODE = Response::HTTP_UNPROCESSABLE_ENTITY;

    /**
     * The constructor method.
     *
     * @param string|null $message
     */
    public function __construct(string $message = null)
    {
        parent::__construct(
            $message ?? __('status.' . self::CODE),
            self::CODE
        );
    }
}