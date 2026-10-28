<?php

use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: ['payments/sslcommerz/return/*', 'payments/sslcommerz/ipn']);
        $middleware->trimStrings(except: ['smtp_password', 'store_password', 'api_key', 'secret_key', 'client_secret', 'client_password', 'api_token']);
        $middleware->append(SecurityHeaders::class);
        $middleware->web(append: [HandleInertiaRequests::class]);
        $middleware->alias(['admin' => AdminOnly::class]);
        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin*') ? '/admin/login' : '/login');
        $middleware->redirectUsersTo(fn ($request) => $request->user()?->is_admin ? '/admin/dashboard' : '/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash(['smtp_password', 'store_password', 'api_key', 'secret_key', 'client_secret', 'client_password', 'api_token']);
        $exceptions->respond(function ($response) {
            $status = $response->getStatusCode();
            if ($status === 419) {
                return back()->with('error', 'Your session expired. Please refresh and try again.');
            }
            if (in_array($status, [403, 404, 429]) || (! app()->hasDebugModeEnabled() && in_array($status, [500, 503]))) {
                return Inertia::render('Error', ['status' => $status])->toResponse(request())->setStatusCode($status);
            }

            return $response;
        });
    })->create();
