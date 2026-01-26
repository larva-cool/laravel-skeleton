<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\Admin;

use App\Enum\StatusSwitch;
use App\Models\Traits;
use App\Models\User;
use App\Models\User\LoginHistory;
use App\Support\UserHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * 管理员模型
 *
 * @property int $id 管理员ID
 * @property int $user_id 用户ID
 * @property string $username 用户名
 * @property string|null $email 邮件地址
 * @property string|null $phone 手机号
 * @property string $name 昵称
 * @property StatusSwitch $status 状态：0:active,1:frozen
 * @property string $socket_id Socket ID
 * @property string $password 密码哈希
 * @property string $remember_token 记住我 Token
 * @property string $last_login_ip 最后登录IP
 * @property int $login_count 登录次数
 * @property bool $is_super 是否是超级管理员
 * @property Carbon $last_login_at 最后登录时间
 * @property Carbon $created_at 注册时间
 * @property Carbon $updated_at 最后更新时间
 * @property Carbon|null $deleted_at 删除时间
 * @property AdminRole[] $roles 关联的角色模型
 * @property User $user 关联的用户模型
 * @property-read string $avatar 头像URL（来自关联的User模型）
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Admin extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    use Traits\DateTimeFormatter;
    use Traits\HasApiTokens;
    use Traits\MultiFieldAggregate;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id', 'username', 'email', 'phone', 'name', 'status', 'socket_id', 'password', 'is_super', 'last_login_ip',
        'login_count', 'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => StatusSwitch::ENABLED->value,
        'is_super' => false,
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'user',
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
            'user_id' => 'integer',
            'username' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'name' => 'string',
            'status' => StatusSwitch::class,
            'socket_id' => 'string',
            'password' => 'hashed',
            'is_super' => 'boolean',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        parent::booted();
        static::creating(function ($model) {
            $user = UserHelper::findOrCreatePhone($model->phone);
            $model->user_id = $user?->id;
        });
    }

    /**
     * 获取头像
     */
    protected function avatar(): Attribute
    {
        $this->loadMissing('user');

        return Attribute::make(
            get: function ($value, $attributes) {
                return $this->user->avatar.'?time='.time();
            },
            set: function ($value, $attributes) {
                $this->user->updateQuietly(['avatar' => $value]);
            }
        );
    }

    /**
     * Admin has and belongs to many user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the login histories relation.
     */
    public function loginHistories(): MorphMany
    {
        return $this->morphMany(LoginHistory::class, 'user')->latest('login_at ');
    }

    /**
     * A user has and belongs to many roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_users', 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * 获取用户组ID
     */
    public function getRoleIds(): array
    {
        return $this->roles()->pluck('id')->all();
    }

    /**
     * 获取手机号
     *
     * @param  \Illuminate\Notifications\Notification|null  $notification
     */
    public function routeNotificationForPhone($notification): ?string
    {
        return $this->phone ?: null;
    }

    /**
     * 判断当前用户是否为超级管理员
     *
     * 遍历用户的所有角色，如果任一角色的权限字段包含*号，则认为是超级管理员
     */
    public function isSuperAdmin(): bool
    {
        // 首先检查is_super字段，如果为true则直接返回true
        if ($this->is_super ?? false) {
            return true;
        }

        // 遍历用户的角色
        $this->load(['roles']);
        foreach ($this->roles as $role) {
            // 检查角色的权限字段（rules）是否包含*号
            if (is_string($role->rules) && str_contains($role->rules, '*')) {
                return true;
            }
        }

        return false;
    }
}
