<?php

namespace App\Http\Controllers;

use App\Models\User;
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
}
