<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\ResponseHelper;

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
}
