<?php

namespace App\Http\Controllers;

use App\Models\V1\User;
use App\Exceptions\Notfound;
use Illuminate\Http\Response;
use App\Exceptions\Validation;
use App\Exceptions\Unauthorized;
use Illuminate\Http\JsonResponse;
use Jenssegers\Mongodb\Eloquent\Model;
use Laravel\Lumen\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Controller extends BaseController
{
    /**
     * Checking if exists an document.
     *
     * @param mixed $value
     * @param Model $class
     *
     * @throws NotFoundHttpException
     *
     * @return void
     */
    public function exists($value, $class = User::class): void
    {
        if (!$class::where((new $class())->getKeyName(), '=', $value)->first()) {
            throw new NotFoundHttpException('What you are looking for was not found!');
        }
    }

    /**
     * The response to return.
     *
     * @param mixed $data
     * @param integer $statusCode
     *
     * @return JsonResponse
     */
    public function response($data = [], $statusCode = Response::HTTP_OK)
    {
        $response = [];

        if (is_object($data)) {
            $data = $data->toArray();
        }
        
        $response['message'] = $data['message'] ?? __('status.' . $statusCode);
        $response['data'] = $data['data'] ?? $data;

        if (array_key_exists('data', $data)) {

            unset($data['data']);

            foreach ($data as $key => $value) {
                $response[$key] = $value;
            }
        }

        if (empty($response['data'])) {
            unset($response['data']);
        }

        return response()
            ->json(
                $response,
                $statusCode
            );
    }

    /**
     * The invalid credentials exception.
     *
     * @throws Validation
     *
     * @return void
     */
    public function invalidCredentials()
    {
        throw new Validation(
            __('invalid.credentials')
        );
    }

    /**
     * The unauthorized exception.
     *
     * @throws Unauthorized
     *
     * @return void
     */
    public function unauthorized()
    {
        throw new Unauthorized();
    }

    /**
     * The unauthorized exception.
     *
     * @throws Notfound
     *
     * @return void
     */
    public function notfound()
    {
        throw new Notfound();
    }
}
