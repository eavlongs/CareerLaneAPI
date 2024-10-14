<?php

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Session;
use App\ResponseHelper;
use App\Models\Company;

class EnsureIsCompany
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

        $company = Company::firstWhere('account_id', $account->id);
        if (!$company) {
            return ResponseHelper::buildUnauthorizedResponse();
        }

        // check if account is verified
        if (!isset($account->email_verified_at)) {
            return ResponseHelper::buildUnauthorizedResponse("Please verify your account first!");
        }

        $request->merge(["_auth_company_id" => $company->id, "_auth_account_id" => $account->id]);
        return $next($request);
    }
}