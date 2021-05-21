<?php

namespace App\Http\Controllers;

use App\Models\V1\User;
use Illuminate\Http\Response;
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

        return response()
            ->json(
                $response,
                $statusCode
            );
    }
}
