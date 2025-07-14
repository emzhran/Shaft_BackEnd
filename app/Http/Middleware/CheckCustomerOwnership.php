<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCustomerOwnership
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        $user = auth()->user();
        
       if (!$user || $user->role_id !== 2) {
        return response()->json(['message' => 'Anda tidak memiliki izin untuk melakukan tindakan ini.'], 403);
        }


        return $next($request);
    }
}
