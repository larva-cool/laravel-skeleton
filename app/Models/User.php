<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enum\SocialProvider;
use App\Enum\UserStatus;
use App\Models\User\Address;
use App\Models\User\LoginHistory;
use App\Models\User\UserExtra;
use App\Models\User\UserGroup;
use App\Models\User\UserProfile;
use App\Models\User\UserSocial;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * 用户模型
 *
 * @property int $id 用户ID
 * @property int $group_id 用户组ID
 * @property string $username 用户名
 * @property string|null $email 邮件地址
 * @property string|null $phone 手机号
 * @property string $name 昵称
 * @property string $avatar 头像访问 Url
 * @property UserStatus $status 状态
 * @property int $available_points 可用积分
 * @property int $available_coins 可用金币
 * @property string $socket_id Socket ID
 * @property string $device_id 设备ID
 * @property string $password 密码哈希
 * @property string $pay_password 支付密码哈希
 * @property string $remember_token 记住我 Token
 * @property Carbon|null $vip_expiry_at VIP过期时间
 * @property Carbon $created_at 注册时间
 * @property Carbon $updated_at 最后更新时间
 * @property Carbon|null $deleted_at 删除时间
 *
 * 只读属性
 * @property-read string $socket_status Socket 状态
 * @property-read string|null $phone_text 手机号文本
 * @property-read string $status_label 状态文本
 *
 * 关系对象
 * @property UserGroup $group 用户组
 * @property UserProfile $profile 个人信息
 * @property UserExtra $extra 用户扩展信息
 * @property \Illuminate\Database\Eloquent\Collection<int,LoginHistory> $loginHistories 登录历史
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    use Traits\DateTimeFormatter;
    use Traits\MultiFieldAggregate;

    // 默认头像
    public const DEFAULT_AVATAR = 'img/avatar.png';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'group_id', 'username', 'email', 'phone', 'name', 'avatar', 'status', 'available_points', 'available_coins',
        'socket_id', 'device_id', 'password', 'vip_expiry_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password', 'pay_password', 'remember_token',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => UserStatus::STATUS_ACTIVE->value,
        'available_points' => 0,
        'available_coins' => 0,
        'vip_expiry_at' => null,
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
            'group_id' => 'integer',
            'username' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'name' => 'string',
            'avatar' => 'string',
            'status' => UserStatus::class,
            'available_points' => 'integer',
            'available_coins' => 'integer',
            'device_id' => 'string',
            'socket_id' => 'string',
            'password' => 'hashed',
            'pay_password' => 'hashed',
            'vip_expiry_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * 获取昵称
     */
    protected function name(): Attribute
    {
        return Attribute::make(get: function (?string $value, $attributes) {
            return $value ?: $attributes['username'];
        });
    }

    /**
     * 获取手机号
     */
    protected function phoneText(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, $attributes) => mobile_replace($attributes['phone'])
        )->shouldCache();
    }

    /**
     * 在线状态
     */
    protected function socketStatus(): Attribute
    {
        return Attribute::make(get: function ($value, $attributes) {
            return ! empty($attributes['socket_id']) ? 'online' : 'offline';
        });
    }

    /**
     * 获取状态标签
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status->label()
        )->shouldCache();
    }

    /**
     * Get the group relation.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    /**
     * Get the profile relation.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    /**
     * Get the extra relation.
     */
    public function extra(): HasOne
    {
        return $this->hasOne(UserExtra::class, 'user_id', 'id');
    }

    /**
     * Get the socials' relation.
     */
    public function socials(): HasMany
    {
        return $this->hasMany(UserSocial::class)->orderBy('id');
    }

    /**
     * Get the wechat mp relation.
     */
    public function wechatMp(): HasOne
    {
        return $this->socials()->one()->where('provider', SocialProvider::WECHAT_MP->value);
    }

    /**
     * Get the wechat app relation.
     */
    public function wechatApp(): HasOne
    {
        return $this->socials()->one()->where('provider', SocialProvider::WECHAT_APP->value);
    }

    /**
     * Get the wechat mini program relation.
     */
    public function wechatMiniProgram(): HasOne
    {
        return $this->socials()->one()->where('provider', SocialProvider::WECHAT_MINI_PROGRAM->value);
    }

    /**
     * Get the address relation.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class)->orderBy('id');
    }

    /**
     * Get the default address relation.
     */
    public function defaultAddress(): HasOne
    {
        return $this->addresses()->one()->where('is_default', true)->latestOfMany();
    }

    /**
     * Get the login histories relation.
     */
    public function loginHistories(): MorphMany
    {
        return $this->morphMany(LoginHistory::class, 'user')->latest('login_at ');
    }
}
