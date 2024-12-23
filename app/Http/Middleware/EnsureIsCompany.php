<?php

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Session;
use App\ResponseHelper;
use App\Models\Company;
use App\QueryHelper;

class EnsureIsCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $checkVerificationStr = 'true'): Response
    {
        $checkVerification = filter_var($checkVerificationStr, FILTER_VALIDATE_BOOLEAN);

        $account = QueryHelper::getAccount($request);
        $company = QueryHelper::getCompany($request);

        if (!$account || !$company) {
            return ResponseHelper::buildUnauthorizedResponse();
        }

        // check if account is verified
        if ($checkVerification && !isset($account->email_verified_at)) {
            return ResponseHelper::buildUnauthorizedResponse("Please verify your account first!");
        }

        $request->merge(["_auth_company_id" => $company->id, "_auth_account_id" => $account->id]);
        return $next($request);
    }
}
