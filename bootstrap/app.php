<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

require_once __DIR__.'/../app/Helpers/organization_helpers.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            if (app()->isLocal()) {
                Route::middleware('web')
                    ->group(base_path('routes/testing.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetOrganizationContext::class,
            \App\Http\Middleware\SetOrganizationTimezone::class,
        ]);

        // Exclude Paystack webhook from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'webhooks/paystack',
        ]);

        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('admin/*') || $request->is('*/admin/*')) {
                return route('filament.admin.auth.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        \Sentry\Laravel\Integration::handles($exceptions);

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->is('admin/*') || $request->is('*/admin/*')) {
                     return redirect()->route('filament.admin.auth.login')->with('error', 'Session expired, please login again.');
                }
                
                return redirect()->route('login')->with('error', 'Page expired, please login again.');
            }
        });
    })->create();
