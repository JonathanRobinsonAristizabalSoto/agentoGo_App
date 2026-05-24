<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Report or log an exception.
     */
    public function report(Throwable $exception): void
    {
        // Enviar a Sentry si está configurado y la excepción debe reportarse
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            try {
                app('sentry')->captureException($exception);
            } catch (Throwable $e) {
                // No bloquear el flujo si Sentry falla
            }
        }

        parent::report($exception);
    }
}
