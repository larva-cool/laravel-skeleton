<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User\Nickname;
use App\Support\FileHelper;
use Illuminate\Database\Seeder;

/**
 * 随机昵称数据填充
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class NicknameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '-1');
        // 写入随机昵称
        $data = FileHelper::json(database_path('data/nickname-20251129.json'));
        $nicknames = [];
        foreach ($data as $key => $val) {
            $nicknames[] = ['nickname' => $val];
            // 1000个一组写入数据库
            if ($key % 1000 === 0) {
                Nickname::insert($nicknames);
                $nicknames = [];
            }
        }
        Nickname::insert($nicknames);
        // 释放内存
        $data = $nicknames = null;
        unset($data, $nicknames);
    }
}
