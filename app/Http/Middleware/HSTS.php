<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HSTS
{

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Solo agregar headers HSTS si no es una respuesta de descarga de archivo
        if (!($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse)) {
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubdomains');
        }

        return $response;
    }
}
