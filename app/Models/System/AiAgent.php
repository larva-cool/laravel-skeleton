<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Models\System;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OpenAI\Contracts\ClientContract;

/**
 * AI 智能体
 *
 * @property int $id ID
 * @property string $name 名称
 * @property string $description 描述
 * @property string $model 模型 ID
 * @property string $prompt 系统提示词
 * @property int $max_tokens 模型回答最大长度（单位 token）。取值范围为 [1, 4096]。
 * @property float $temperature 随机性和多样性，取值范围为 [0, 2]。
 * @property float $top_p 采样概率阈值，取值范围为 [0, 1]。
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @property Carbon $deleted_at 删除时间
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AiAgent extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ai_agents';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id', 'name', 'description', 'model', 'prompt', 'max_tokens', 'temperature', 'top_p',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'max_tokens' => 4096,
        'temperature' => 0.7,
        'top_p' => 0.5,
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
            'description' => 'string',
            'model' => 'string',
            'prompt' => 'string',
            'max_tokens' => 'integer',
            'temperature' => 'float',
            'top_p' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * 创建补全
     */
    public function completions($prompt, $openai = null)
    {
        $openai = $openai ?? app(ClientContract::class);

        return $openai->completions()->create([
            'model' => $this->model,
            'messages' => [
                [// 系统提示词
                    'role' => 'system',
                    'content' => $this->prompt,
                ],
                [// 用户提示词
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => $this->max_tokens,
            'temperature' => $this->temperature,
            'top_p' => $this->top_p,
        ]);
    }

    /**
     * 创建聊天
     */
    public function chat($prompt, $openai = null)
    {
        $openai = $openai ?? app(ClientContract::class);

        return $openai->chat()->create([
            'model' => $this->model,
            'messages' => [
                [// 系统提示词
                    'role' => 'system',
                    'content' => $this->prompt,
                ],
                [// 用户提示词
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => $this->max_tokens,
            'temperature' => $this->temperature,
            'top_p' => $this->top_p,
        ]);
    }
}
