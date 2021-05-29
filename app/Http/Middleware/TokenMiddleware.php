<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;

class TokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $key = 'x-app-token';
        $token = env('APP_TOKEN');

        if ($request->headers->has($key) === false) {
            return $this->unauthorized();
        }

        $decrypted = Crypt::decrypt(
            $request->headers
                ->get($key)
        );

        if ($decrypted !== $token) {
            return $this->unauthorized();
        }

        return $next($request);
    }

    /**
     * The unauthorized action.
     *
     * @return JsonResponse
     */
    private function unauthorized(): JsonResponse
    {
        $status = JsonResponse::HTTP_UNAUTHORIZED;

        return response()
            ->json(
                [
                    'message' => __('status.' . $status)
                ],
                $status
            );
    }
}
