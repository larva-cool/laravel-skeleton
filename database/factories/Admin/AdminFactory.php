<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace Database\Factories\Admin;

use App\Enum\StatusSwitch;
use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'username' => $this->faker->userName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'name' => $this->faker->name,
            'status' => StatusSwitch::ENABLED->value,
            'password' => bcrypt('password'), // 默认密码为 'password'
            'is_super' => false,
            'last_login_ip' => $this->faker->ipv4,
            'login_count' => $this->faker->numberBetween(1, 100),
            'last_login_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * 超级管理员状态
     *
     * @return static
     */
    public function superAdmin()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_super' => true,
            ];
        });
    }

    /**
     * 冻结状态
     *
     * @return static
     */
    public function frozen()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => StatusSwitch::DISABLED->value,
            ];
        });
    }
}
