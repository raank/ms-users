<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

/**
 * @OA\Schema(
 *   schema="Notfound",
 *   description="This information could not be found",
 *   @OA\Property(property="message", type="string", description="Message of Response", example="This information could not be found")
 * )
 */
class Notfound extends Exception
{
    /**
     * Define the CODE HTTP
     *
     * @var integer
     */
    protected const CODE = Response::HTTP_NOT_FOUND;

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