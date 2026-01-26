<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\System\Area;
use App\Support\FileHelper;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $district = FileHelper::json(database_path('data/district-20250328.json'));
        $districts = [];
        foreach ($district['result'] as $item) {// 省
            $areaId = Area::insertGetId([
                'name' => $item['fullname'],
                'area_code' => $item['id'],
                'lat' => $item['location']['lat'],
                'lng' => $item['location']['lng'],
            ]);
            foreach ($item['districts'] as $dit) {// 市
                $cityArea = Area::create([
                    'parent_id' => $areaId,
                    'name' => $dit['fullname'],
                    'area_code' => $dit['id'],
                    'lat' => $dit['location']['lat'],
                    'lng' => $dit['location']['lng'],
                ]);
                if (isset($dit['districts'])) {// 区
                    foreach ($dit['districts'] as $subDit) {
                        $districts[] = [
                            'parent_id' => $cityArea->id,
                            'name' => $subDit['fullname'],
                            'area_code' => $subDit['id'],
                            'lat' => $subDit['location']['lat'],
                            'lng' => $subDit['location']['lng'],
                        ];
                    }
                }
            }
        }
        Area::insert($districts);
    }
}
