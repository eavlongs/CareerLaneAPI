<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Session;
use App\Models\Account;
use App\ResponseHelper;
use App\Models\User;
use App\QueryHelper;

class EnsureIsUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = QueryHelper::getUser($request);

        if (!$user) {
            return ResponseHelper::buildUnauthorizedResponse();
        }

        $request->merge(["_auth_user_id" => $user->id]);

        return $next($request);
    }
}
