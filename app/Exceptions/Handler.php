<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    public function render($request, \Throwable $e)
    {
//        if ($request->is('api/*') && !config('app.debug')) {
        if ($request->is('api/*')) {
            return $this->handleApiException($e);
        }

        return parent::render($request, $e);
    }

    public function handleApiException(\Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return $this->error('Validation failed', 422, $e->errors());
        } elseif ($e instanceof AuthenticationException) {
            return $this->error('Unauthenticated', 401);
        } elseif ($e instanceof NotFoundHttpException) {
            return $this->error('Resource not found', 404);
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            return $this->error('Method not allowed', 405);
        } elseif ($e instanceof HttpException) {
            return $this->error($e->getMessage() ?: 'Http error occurred', $e->getStatusCode());
        }

        return $this->error($e->getMessage(), 500);
    }

}
