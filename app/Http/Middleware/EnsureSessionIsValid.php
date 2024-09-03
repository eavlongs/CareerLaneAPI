<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Session;
use App\Models\Account;
use App\ResponseHelper;

class EnsureSessionIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session_id = $request->cookie('auth_session');
        $session = Session::firstWhere('id', $session_id);
        if (!$session) {
            return ResponseHelper::buildUnauthorizedResponse();
        }

        $account = Account::firstWhere('id', $session->account_id);
        if (!$account) {
            return ResponseHelper::buildUnauthorizedResponse();
        }
        return $next($request);
    }
}
