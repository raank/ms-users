<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Define constants to Documentation.
|--------------------------------------------------------------------------
*/

define('APP_URL', sprintf('%s/api', env('APP_URL')));

/**
 * Informations of API.
 *
 * @OA\Info(
 *     title="ms-users",
 *     description="This is a micro authentication service and user crud. ",
 *     version="1.0",
 *     @OA\Contact(
 *          email="raank92@gmail.com"
 *     )
 * )
 *
 * Constrantes to API.
 * @OA\Schemes(format={"https", "http"})
 * @OA\Server(url=APP_URL)
 * 
 * Tags
 * @OA\Tag(name="v1.auth", description="Authentication routes")
 *
 * Security
 * @OA\SecurityScheme(
 *  securityScheme="Bearer",
 *  type="apiKey",
 *  name="Authorization",
 *  in="header"
 * )
 * 
 * @OA\SecurityScheme(
 *  securityScheme="bearerAuth",
 *  type="http",
 *  name="Authorization",
 *  in="header",
 *  scheme="bearer"
 * )
 */
