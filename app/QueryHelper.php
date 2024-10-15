<?php

namespace App;

use Illuminate\Http\Request;
use \Illuminate\Database\Eloquent\Builder;
use App\Models\Session;
use App\Models\Account;
use App\Models\User;
use App\Models\Company;

class QueryHelper
{
    public static function paginate(Builder $queryBuilder, $paginationParams)
    {
        if (!(isset($paginationParams['page']) && isset($paginationParams['limit']))) {
            return null;
        }

        if ($paginationParams['limit'] < 0) {
            return null;
        }
        $paginator = $queryBuilder->paginate($paginationParams['limit'], ['*'], 'page', $paginationParams['page']);

        $paginationMetaData = [
            "per_page" => $paginator->perPage(),
            "current_page" => $paginator->currentPage(),
            "last_page" => $paginator->lastPage(),
            "total" => $paginator->total(),
        ];

        return $paginationMetaData;
    }

    public static function sort(Builder $queryBuilder, $cleanedSortParams, $paramPrefixMap = [])
    {
        if (empty($cleanedSortParams)) {
            return;
        }

        foreach ($cleanedSortParams as $column => $direction) {
            if (isset($paramPrefixMap[$column])) {
                $column = $paramPrefixMap[$column];
            }
            $queryBuilder->orderBy($column, $direction);
        }
    }

    public static function filter(Builder $queryBuilder, array $filterParams, array $filterOptionString)
    {
        foreach ($filterOptionString as $option) {
            $result = explode("|", $option);
            if (count($result) != 3) {
                continue;
            }

            $param = $result[0];
            $column = $result[1];
            $operator = $result[2];

            if (!isset($filterParams[$param])) {
                continue;
            }

            $value = $filterParams[$param];

            if ($operator == "like") {
                $queryBuilder->whereRaw("$column LIKE ? COLLATE utf8mb4_general_ci", ["%$value%"]);
            } else {
                $queryBuilder->where($column, $operator, $value);
            }
        }
    }

    public static function getAccount(Request $request)
    {
        $session_id = $request->cookie('auth_session');
        $session = Session::firstWhere('id', $session_id);
        if (!$session) {
            return null;
        }

        $account = Account::firstWhere('id', $session->account_id);
        if (!$account) {
            return null;
        }

        return $account;
    }

    public static function getUser(Request $request)
    {
        $account = self::getAccount($request);

        if (!$account) {
            return null;
        }

        $user = User::firstWhere('account_id', $account->id);

        return $user;
    }

    public static function getCompany(Request $request)
    {
        $account = self::getAccount($request);

        if (!$account) {
            return null;
        }

        $company = Company::firstWhere('account_id', $account->id);

        return $company;
    }
}
