<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenAI;
use OpenAI\Client;
use OpenAI\Contracts\ClientContract;

/**
 * OpenAI 服务提供者
 *
 * @codeCoverageIgnore
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class OpenAIProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ClientContract::class, static function (): Client {
            $apiKey = settings('openai.api_key');
            $organization = settings('openai.organization');
            $project = settings('openai.project');
            $baseUri = settings('openai.base_uri');

            if (! is_string($apiKey) || ($organization !== null && ! is_string($organization))) {
                throw new \InvalidArgumentException('OpenAI API key is missing');
            }

            $client = OpenAI::factory()
                ->withApiKey($apiKey)
                ->withOrganization($organization)
                ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
                ->withHttpClient(new \GuzzleHttp\Client(['timeout' => settings('openai.request_timeout', 30)]));

            if (is_string($project)) {
                $client->withProject($project);
            }

            if (is_string($baseUri)) {
                $client->withBaseUri($baseUri);
            }

            return $client->make();
        });

        $this->app->alias(ClientContract::class, 'openai');
        $this->app->alias(ClientContract::class, Client::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
