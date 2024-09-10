<?php

namespace App;

use Illuminate\Http\Request;

class RequestHelper
{
    public static function getPaginationParams(Request $request)
    {
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);

        return [
            'page' => $page,
            'limit' => $limit
        ];
    }

    public static function getSortParams(Request $request, $model)
    {
        if (!self::checkIfModelIsValid($model)) {
            return [];
        }
        $sort = $request->query('sort');
        $sortParams = [];

        if ($sort) {
            $sortParams = explode(',', $sort);
        }
        $cleanedSortParams = self::cleanSortParams($sortParams, $model);
        return $cleanedSortParams;
    }

    protected static function cleanSortParams($sortParams, $model)
    {
        // check if model is an instance of Illuminate\Database\Eloquent\Model
        if (!self::checkIfModelIsValid($model)) {
            return [];
        }
        // sort=created_at = created_at asc
        // sort=-created_at = created_at desc
        $cleanedSortParams = [];
        foreach ($sortParams as $sortParam) {
            if (substr($sortParam, 0, 1) === '-') {
                // check whether column exists
                if (!in_array(substr($sortParam, 1), $model->getFillable())) {
                    continue;
                }
                $cleanedSortParams[substr($sortParam, 1)] = 'desc';
            } else {
                if (!in_array($sortParam, $model->getFillable())) {
                    continue;
                }
                $cleanedSortParams[$sortParam] = 'asc';
            }
        }

        return $cleanedSortParams;
    }

    public static function getFilterParams(Request $request, array $keys)
    {
        $filters = [];
        foreach ($keys as $key) {
            $value = $request->query($key);
            if ($value) {
                $filters[$key] = $value;
            }
        }
        return $filters;
    }

    protected static function checkIfModelIsValid($model)
    {
        return $model instanceof \Illuminate\Database\Eloquent\Model;
    }
}
