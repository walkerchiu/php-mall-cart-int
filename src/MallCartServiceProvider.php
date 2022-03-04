<?php

namespace WalkerChiu\MallCart;

use Illuminate\Support\ServiceProvider;
use WalkerChiu\MallCart\Models\Entities\Item;
use WalkerChiu\MallCart\Models\Observers\ItemObserver;
use WalkerChiu\MallCart\Models\Entities\Channel;
use WalkerChiu\MallCart\Models\Entities\ChannelLang;
use WalkerChiu\MallCart\Models\Observers\ChannelObserver;
use WalkerChiu\MallCart\Models\Observers\ChannelLangObserver;

class MallCartServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/mall-cart.php' => config_path('wk-mall-cart.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_mall_cart_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_mall_cart_table.php'
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-mall-cart');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-mall-cart'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-mall-cart.command.cleaner')
            ]);
        }

        config('wk-core.class.mall-cart.channel')::observe(config('wk-core.class.mall-cart.channelObserver'));
        config('wk-core.class.mall-cart.channelLang')::observe(config('wk-core.class.mall-cart.channelLangObserver'));
        config('wk-core.class.mall-cart.item')::observe(config('wk-core.class.mall-cart.itemObserver'));
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-mall-cart')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/mall-cart.php', 'wk-mall-cart'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/mall-cart.php', 'mall-cart'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
