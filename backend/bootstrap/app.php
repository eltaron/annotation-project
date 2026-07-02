<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Uploaded file is too large. Check your PHP upload_max_filesize and post_max_size settings.'], 413);
            }
            return back()->with('error', 'Uploaded file is too large. Current limit: ' . ini_get('upload_max_filesize') . '. Increase upload_max_filesize and post_max_size in your php.ini.');
        });
    })->create();
