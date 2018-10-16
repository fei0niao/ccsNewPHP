<?php

namespace App\Http\Middleware;

use Closure;

class addClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->offsetSet("client_id",env("CLIENT_ID"));
        $request->offsetSet("client_secret",env("CLIENT_SECRET"));
        return $next($request);
    }
}
