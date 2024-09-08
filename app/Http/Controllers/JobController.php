<?php

namespace App\Http\Controllers;

use App\Enums\JobTypeEnum;
use App\Enums\LocationEnum;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobPost;
use App\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class JobController extends Controller
{
    public function createJob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "job_title" => "required|string|min:3|max:255",
            "category_id" => "required|string|min:1|max:255",
            "location" => "required|numeric|min:0|max:" . LocationEnum::_LENGTH->value - 1,
            "type" => "required|numeric|min:0|max:" . JobTypeEnum::_LENGTH->value - 1,
            "salary" => "numeric|min:0|max:10000000",
            "salary_start_range" => "required_if:salary,null|numeric|min:0|max:10000000",
            "salary_end_range" => "required_if:salary,null|numeric|min:0|max:10000000|gt:salary_start_range",
            "is_salary_negotiable" => "required|boolean",
            "description" => "required|string|min:1|max:3000",
            "deadline" => [
                "required",
                "date",
                function ($attribute, $value, $fail) {
                    $deadline = Carbon::parse($value);
                    $today = Carbon::today();
                    $oneYearFromNow = Carbon::today()->addYear();

                    if ($deadline->lt($today)) {
                        $fail('The ' . $attribute . ' must be at least today.');
                    }

                    if ($deadline->gt($oneYearFromNow)) {
                        $fail('The ' . $attribute . ' must be within a year from now.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return ResponseHelper::buildValidationErrorResponse($validator->errors());
        }

        $deadline = Carbon::parse($request->deadline);

        $job = JobPost::create([
            "title" => trim($request->job_title),
            'description' => trim($request->description),
            'location' => $request->location,
            'type' => $request->type,
            'salary' => $request->salary,
            'salary_start_range' => $request->salary_start_range,
            'salary_end_range' => $request->salary_end_range,
            'is_salary_negotiable' => $request->is_salary_negotiable,
            'original_deadline' => $deadline,
            'company_id' => $request->_auth_company_id,
            'category_id' => $request->category_id,
        ]);

        return ResponseHelper::buildSuccessResponse();
    }

    public function getCompanyJobs(Request $request)
    {
        $isActive = $request->get("is_active", true);
        $company_id = $request->company_id;

        $company = Company::where("id", $company_id)->first();

        if (!$company) {
            return ResponseHelper::buildErrorResponse("Company not found");
        }

        $jobs = JobPost::where("company_id", $company_id)
            ->where("is_active", $isActive)
            ->orderBy("created_at", "desc")
            ->get();

        return ResponseHelper::buildSuccessResponse([
            "jobs" => $jobs
        ]);
    }

    public function getJobCategories(Request $request)
    {
        $categories = Category::orderBy("name")->get();

        return ResponseHelper::buildSuccessResponse([
            "categories" => $categories
        ]);
    }
}
