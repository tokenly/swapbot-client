<?php

namespace Tokenly\SwapbotClient\Provider;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

/*
* SwapbotClientServiceProvider
*/
class SwapbotClientServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind('Tokenly\SwapbotClient\SwapbotClient', function($app) {
            $config = $this->bindConfig();
            \Illuminate\Support\Facades\Log::debug("\$config=".json_encode($config, 192));
            \Illuminate\Support\Facades\Log::debug("\env('SWAPBOT_CONNECTION_URL', 'http://swapbot.tokenly.com')=".json_encode(env('SWAPBOT_CONNECTION_URL', 'http://swapbot.tokenly.com'), 192));
            $swapbot_client = new \Tokenly\SwapbotClient\SwapbotClient($config['swapbot.connection_url']);
            return $swapbot_client;
        });
    }

    protected function bindConfig()
    {
        // simple config
        $config = [
            'swapbot.connection_url' => env('SWAPBOT_CONNECTION_URL', 'http://swapbot.tokenly.com'),
        ];

        return $config;
    }

}

