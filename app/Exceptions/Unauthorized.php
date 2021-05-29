<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

/**
 * @OA\Schema(
 *   schema="Unauthorized",
 *   description="You are not authorized for this action",
 *   @OA\Property(property="message", type="string", description="Message of Response", example="You are not authorized for this action")
 * )
 */
class Unauthorized extends Exception
{
    /**
     * Define the CODE HTTP
     *
     * @var integer
     */
    protected const CODE = Response::HTTP_UNAUTHORIZED;

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