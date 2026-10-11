<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Di belakang reverse proxy (Caddy/Docker): percayai X-Forwarded-* agar URL & cookie memakai https.
        $middleware->trustProxies(at: '*');

        // Site-to-Control-Center push reports authenticate with a bearer token, not a session.
        $middleware->validateCsrfTokens(except: ['api/cc/report']);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);
        $middleware->prepend(\App\Http\Middleware\AddCorrelationId::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Route-model-binding misses (e.g. a stale client list referencing
        // an already-deleted record) otherwise leak Laravel's raw
        // "No query results for model [...]" message to the frontend.
        // A direct ->findOrFail() throws ModelNotFoundException itself;
        // implicit route-model binding (e.g. the {user} in api/users/{user})
        // has that exception already wrapped into a NotFoundHttpException
        // by Illuminate\Routing\Middleware\SubstituteBindings before it
        // ever reaches here, so both forms need handling.
        $friendlyNotFound = fn (Request $request) => $request->is('api/*')
            ? response()->json([
                'message' => 'This record no longer exists. It may have already been deleted.',
            ], 404)
            : null;

        $exceptions->render(fn (ModelNotFoundException $e, Request $request) => $friendlyNotFound($request));

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($friendlyNotFound) {
            if ($e->getPrevious() instanceof ModelNotFoundException) {
                return $friendlyNotFound($request);
            }
        });
    })->create();
