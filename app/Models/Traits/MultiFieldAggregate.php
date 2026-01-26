<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * 多字段聚合
 *
 * @method array sumMultipleFields(array $fields) 多字段求和
 * @method array countMultipleFields(array $fields) 多字段计数
 * @method array averageMultipleFields(array $fields) 多字段平均
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
trait MultiFieldAggregate
{
    /**
     * 对多个字段进行求和统计
     *
     * @param  array  $fields  要统计的字段数组
     * @return array 包含每个字段求和结果的关联数组
     */
    public function scopeSumMultipleFields(Builder $query, array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $sum = $query->sum($field);
            $result[$field] = $sum;
        }

        return $result;
    }

    /**
     * 对多个字段进行计数统计
     *
     * @param  array  $fields  要统计的字段数组
     * @return array 包含每个字段计数结果的关联数组
     */
    public function scopeCountMultipleFields(Builder $query, array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $count = $query->whereNotNull($field)->count($field);
            $result[$field] = $count;
        }

        return $result;
    }

    /**
     * 对多个字段进行平均值统计
     *
     * @param  array  $fields  要统计的字段数组
     * @return array 包含每个字段平均值结果的关联数组
     */
    public function scopeAverageMultipleFields(Builder $query, array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $average = $query->avg($field);
            $result[$field] = $average;
        }

        return $result;
    }
}
