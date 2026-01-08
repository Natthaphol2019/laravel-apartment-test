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
        $middleware->redirectTo(
            function ($request) {
                    // ถ้า URL เริ่มต้นด้วย admin ให้ส่งไปหน้า adminLogin
                    if ($request->is('admin') || $request->is('admin/*')) {
                        return route('admin.loginForm');
                    }
                    
                    // ถ้าเป็นอย่างอื่น (เช่น tenant) ให้ส่งไปหน้า login ของ tenant
                    return route('tenant.loginForm');
                }
            );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
