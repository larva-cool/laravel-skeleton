<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Admin;

use App\Models\Traits;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * 管理员角色表
 *
 * @property int $id ID
 * @property string $name 角色名
 * @property string $desc 描述
 * @property string $rules 权限规则
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AdminRole extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id', 'name', 'desc', 'rules',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'desc' => 'string',
            'rules' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 管理员
     */
    public function administrators(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'admin_role_users', 'role_id', 'user_id');
    }
}
