<?php 

return [
    /**
     * Run this middleware only for URLs starting with specified string. Default null.
     * Setting this option is required only if you want to setup the middleware
     * in Application::middleware() instead of using router's scoped middleware.
     * It would normally be set to same value as that of server.base_url below.
     * 
     * @var string|null
     */
    'path' => null,

    /**
     * URL part to be omitted from source path.
     * 
     * @see http://glide.thephpleague.com/1.0/config/source-and-cache/#set-a-base-url
     * 
     * @var string|null
     */
    'base_url' => '/images/',

    /**
     * @see http://glide.thephpleague.com/1.0/config/security/
     * 
     * @var array
     */
    'security' => [
        /**
         * Boolean indicating whether secure URLs should be used to prevent URL parameter manipulation.
         * 
         * @var bool
         */
        'secure' => false,

        /**
         * Signing key used to generate / validate URLs if `secure` is `true`.
         * 
         * @var string|null
         */
        'key' => null,
    ],

    /**
     * Cache duration.
     * 
     * @var string
     */
    'cache_time' => '+1 days',

    /**
     * Any response headers you may want to set.
     * 
     * @var array<string, string>
     */
    'headers' => [],

    /**
     * Allowed query string params. 
     * If for e.g. you are only using glide presets then you can set allowed params as `['p']` to prevent users from using any other image manipulation params.
     * 
     * @var string[]
     */
    'allowed_params' => [],
];
