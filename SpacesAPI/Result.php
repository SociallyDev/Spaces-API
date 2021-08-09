<?php

namespace SpacesAPI;

use Aws\Api\DateTimeResult;

/**
 * AWS Results parser
 */
class Result
{
    /**
     * Convert AWS result object into plain, multidimensional array
     *
     * @param $data
     *
     * @return array|mixed
     */
    public static function parse($data) {
        if (gettype($data) == "object" && get_class($data) == \Aws\Result::class) {
            $data = $data->toArray();
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::parse($value);
                continue;
            }

            if (gettype($value) == "object" && get_class($value) == DateTimeResult::class) {
                $data[$key] = strtotime($value);
            }
        }

        return $data;
    }
}
