<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\Admin;

use App\Models\Admin\AdminRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminRole>
 */
class AdminRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AdminRole::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'desc' => $this->faker->sentence,
            'rules' => '[]', // 默认空权限规则
        ];
    }

    /**
     * 赋予角色管理员权限
     *
     * @return static
     */
    public function withAdminRules()
    {
        return $this->state(function (array $attributes) {
            return [
                'rules' => '["admin.*"]', // 管理员所有权限
            ];
        });
    }
}
