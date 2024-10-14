<?php

namespace App\Http\Controllers;

use App\Enums\JobTypeEnum;
use App\Enums\LocationEnum;
use App\Models\Category;
use App\Models\Company;
use App\Models\JobPost;
use App\Models\Province;
use App\RequestHelper;
use App\ResponseHelper;
use App\QueryHelper;
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
                    $deadline = Carbon::parse($value)->startOfDay();
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

        $deadline = Carbon::parse($request->deadline)->startOfDay();

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

    public function updateJob(Request $request)
    {
        $job = JobPost::where("id", $request->id)->first();

        if (!$job) {
            return ResponseHelper::buildNotFoundResponse("Job not found");
        }

        if ($job->company_id !== $request->_auth_company_id) {
            return ResponseHelper::buildUnauthorizedResponse("You are not authorized to update this job");
        }

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
            "extended_deadline" => [
                "required",
                "date",
                function ($attribute, $value, $fail) use ($job) {
                    $extended_deadline = Carbon::parse($value)->startOfDay();
                    $today = Carbon::today();
                    $oneYearFromNow = Carbon::today()->addYear();

                    if ($extended_deadline->lt($today)) {
                        $fail('The ' . $attribute . ' must be at least today.');
                    }

                    if ($extended_deadline->gt($oneYearFromNow)) {
                        $fail('The ' . $attribute . ' must be within a year from now.');
                    }

                    if ($extended_deadline->lt(Carbon::parse($job->original_deadline)->startOfDay())) {
                        $fail('The ' . $attribute . ' must be after the original deadline.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return ResponseHelper::buildValidationErrorResponse($validator->errors());
        }

        $extended_deadline = Carbon::parse($request->extended_deadline)->startOfDay();

        if ($extended_deadline->eq(Carbon::parse($job->original_deadline)->startOfDay())) {
            $extended_deadline = null;
        }

        $job->title = trim($request->job_title);
        $job->description = trim($request->description);
        $job->location = $request->location;
        $job->type = $request->type;
        $job->salary = $request->salary;
        $job->salary_start_range = $request->salary_start_range;
        $job->salary_end_range = $request->salary_end_range;
        $job->is_salary_negotiable = $request->is_salary_negotiable;
        $job->extended_deadline = $extended_deadline;
        $job->category_id = $request->category_id;
        $job->save();

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

        foreach ($jobs as $job) {
            $job->logo_url = $company->logo_url;
        }

        return ResponseHelper::buildSuccessResponse([
            "jobs" => $jobs
        ]);
    }

    public function getJob(Request $request)
    {
        $job = JobPost::where("id", $request->id)->first();

        if (!$job) {
            return ResponseHelper::buildErrorResponse("Job not found");
        }

        $company = Company::where("id", $job->company_id)->first();
        $province = Province::where("id", $company->province_id)->first();
        $category = Category::where("id", $job->category_id)->first();

        return ResponseHelper::buildSuccessResponse([
            "job" => [
                "id" => $job->id,
                "title" => $job->title,
                "description" => $job->description,
                "location" => $job->location,
                "type" => $job->type,
                "salary" => $job->salary,
                "salary_start_range" => $job->salary_start_range,
                "salary_end_range" => $job->salary_end_range,
                "is_salary_negotiable" => $job->is_salary_negotiable,
                "original_deadline" => $job->original_deadline,
                "extended_deadline" => $job->extended_deadline ?? $job->original_deadline,
                "is_active" => $job->is_active,
                "applicants" => $job->applicants,
                "company_id" => $job->company_id,
                "category_id" => $job->category_id,
                "category_name" => $category->name,
                "company_name" => $company->name,
                "logo_url" => $company->logo_url,
                "company_location" => $province->name,
                "created_at" => $job->created_at,
                "updated_at" => $job->updated_at,
            ]
        ]);
    }

    public function getJobs(Request $request)
    {
        $query = $request->query("q", "");
        $paginationParams = RequestHelper::getPaginationParams($request);
        $sortParams = RequestHelper::getSortParams($request, new JobPost());
        $filterParams = RequestHelper::getFilterParams($request, ["c_id", "location", "type", "min_salary", "p_id"]);

        // return $sortParams;
        $queryBuilder = JobPost::query();

        $queryBuilder->join("companies", "job_posts.company_id", "=", "companies.id");
        $queryBuilder->join("provinces", "companies.province_id", "=", "provinces.id");

        if (isset($filterParams["p_id"])) {
            $queryBuilder->where("companies.province_id", $filterParams["p_id"]);
        }

        QueryHelper::filter($queryBuilder, $filterParams, [
            "c_id|company_id|=",
            "location|location|=",
            "type|type|=",
            "min_salary|salary|>="
        ]);

        $queryBuilder->where(function ($_queryBuilder) use ($query) {
            $_queryBuilder->whereRaw("title LIKE ? COLLATE utf8mb4_general_ci", ["%$query%"]);
            $_queryBuilder->orWhereRaw("job_posts.description LIKE ? COLLATE utf8mb4_general_ci", ["%$query%"]);
        });

        $queryBuilder->select("job_posts.*", "companies.logo_url", "companies.name as company_name", "provinces.name as company_location");

        QueryHelper::sort($queryBuilder, $sortParams, [
            "created_at" => "job_posts.created_at",
        ]);
        $metaData = QueryHelper::paginate($queryBuilder, $paginationParams);

        $jobs = $queryBuilder->get();

        foreach ($jobs as $job) {
            $keysToUnset = ["applicants", "category_id", "description", "original_deadline", "extended_deadline", "salary", "salary_start_range", "salary_end_range", "is_salary_negotiable"];
            ResponseHelper::unsetKeysFromData($job, $keysToUnset);
        }

        return ResponseHelper::buildSuccessResponse([
            "jobs" => $jobs,
            "meta" => $metaData
        ]);
    }

    public function markJobAsInactive(Request $request)
    {
        $job = JobPost::where('id', $request->id)->first();
        if (!$job) {
            return ResponseHelper::buildNotFoundResponse("Job Not Found");
        }
        $job->is_active = false;
        $job->save();
        return ResponseHelper::buildSuccessResponse();
    }

    public function getJobCategories(Request $request)
    {
        $categories = Category::orderBy("name")->get();

        return ResponseHelper::buildSuccessResponse([
            "categories" => $categories
        ]);
    }
}
