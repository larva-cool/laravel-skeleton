<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\DateTimeFormatter;
use App\Models\Traits\MultiFieldAggregate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * 模型基类
 *
 * @method [返回值类型] [方法名]([参数列表]) [可选描述]
 * @method Builder dateQuery(string $field, string|Carbon $start, string|Carbon|null $end = null)
 * @method array sumMultipleFields(array $fields) 多字段求和
 * @method array countMultipleFields(array $fields) 多字段计数
 * @method array averageMultipleFields(array $fields) 多字段平均
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    use DateTimeFormatter, MultiFieldAggregate;
}
