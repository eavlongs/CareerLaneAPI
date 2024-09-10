<?php

namespace App;

use \Illuminate\Database\Eloquent\Builder;

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

    public static function sort(Builder $queryBuilder, $cleanedSortParams)
    {
        if (empty($cleanedSortParams)) {
            return;
        }

        foreach ($cleanedSortParams as $column => $direction) {
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
}
