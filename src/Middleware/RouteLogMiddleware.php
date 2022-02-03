<?php

namespace Sagar290\Logable\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RouteLogMiddleware
{
    public $timer;

    public function handle($request, Closure $next)
    {
        app('logable')->setArrivingTime(microtime(true));

        return $next($request);
    }

    public function terminate($request, $response)
    {
        $logable = app('logable');

        $logable->setLeavingTime(microtime(true));

        $logable->log(json_encode([
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'body' => $request->all(),
            'response_code' => $response->getStatusCode(),
            'response_time' => Carbon::parse($logable->getTotalDuration())->format('Uv') . ' ms',
        ]));

    }
}
