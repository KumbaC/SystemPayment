<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function __construct(protected ActivityLogService $activityLog) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! auth()->check()) {
            return $response;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        if ($request->routeIs('login', 'logout')) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $routeName = $request->route()?->getName() ?? $request->path();
        $this->activityLog->log(
            strtolower($request->method()),
            "Acción {$request->method()} en {$routeName}",
            properties: ['route' => $routeName, 'input' => $request->except(['password', '_token', '_method'])]
        );

        return $response;
    }
}
