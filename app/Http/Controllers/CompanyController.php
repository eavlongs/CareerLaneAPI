<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Company;
use App\Models\Session;
use App\Models\User;
use App\ResponseHelper;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function getFeaturedCompanies()
    {
        $featuredCompanies = Company::withSum('job_posts as total_applicants', 'applicants')->withcount("job_posts as total_job_posts")->having('total_job_posts', ">=", 0)->havingNotNull('total_applicants')->orderBy('total_applicants', 'desc')->orderBy('total_job_posts')->get()->map(function ($company) {
            $company->total_applicants = (int) $company->total_applicants;
            return $company;
        });;

        return ResponseHelper::buildSuccessResponse([
            "companies" => $featuredCompanies
        ]);
    }
    public function companyProfileInformation(Request $request)
{
    $sessionId = $request->cookie('auth_session');
    $session = Session::where('id', $sessionId)->first();
    $account_id = $session->account_id;
    $company = Company::where('account_id', $account_id)->first();
    $account = Account::where('id', $account_id)->first();

    if (!$company) {
        return ResponseHelper::buildErrorResponse();
    }

    return ResponseHelper::buildSuccessResponse([
        'name' => $company->name,
        'description' => $company->description,
        'logo_url' => $company->logo_url,
        'links' => $company->links,
        'email' => $account->email,
        'is_verify' => $account->is_verify,
    ]);
}
    // public function getCompanies(Request $request) {

    // }a
}