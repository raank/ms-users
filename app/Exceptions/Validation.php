<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

/**
 * @OA\Schema(
 *  schema="Validation",
 *  description="There is some incorrect information",
 *  @OA\Property(property="message", type="string", description="Message of Response"),
 *  @OA\Property(property="errors", type="object", description="Errors of Request"),
 *  example={
 *      "message": "There is some incorrect information",
 *      "errors": {
 *          "field": {
 *              "Message of Validation"
 *          }
 *      }
 *  }
 * )
 */
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