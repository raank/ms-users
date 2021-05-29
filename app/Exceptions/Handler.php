<?php

namespace App\Exceptions;

use Throwable;
use App\Exceptions\Exists;
use App\Exceptions\Notfound;
use Illuminate\Http\Response;
use App\Exceptions\Validation;
use App\Exceptions\Unauthorized;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @OA\Schema(
 *   schema="BadRequest",
 *   description="This information could not be processed",
 *   @OA\Property(property="message", type="string", description="Message of Response", example="This information could not be processed")
 * )
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class
    ];


    /**
     * A list of the exception types to response like a json.
     *
     * @var array
     */
    protected $httpReports = [
        Unauthorized::class,
        Validation::class,
        Notfound::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if (in_array(get_class($exception), $this->httpReports)) {
            return response()
                ->json([
                    'message' => $exception->getMessage()
                ], $exception->getCode());
        }

        if ($exception instanceof ValidationException) {
            return response()
                ->json(
                    array_merge(
                        [
                            'message' => __(sprintf('status.%d', $exception->status ?? Response::HTTP_UNPROCESSABLE_ENTITY)),
                            'errors' => $exception->getResponse()->original,
                        ],
                        $response ?? []
                    ),
                    $exception->status ?? Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()
                ->json([
                    'message' => __('status.404'),
                ], Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()
                ->json(
                    [
                        'message' => __('status.' . Response::HTTP_METHOD_NOT_ALLOWED),
                        'trace' => [
                            'trace' => $exception->getMessage(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine()
                        ]
                    ],
                    Response::HTTP_METHOD_NOT_ALLOWED
                );
        }

        return parent::render($request, $exception);
    }
}
