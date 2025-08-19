<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array<string, string>
     * @phpstan-var array<string, class-string>
     */
    public array $aliases = [
        // 'csrf' => CSRF::class,
        // 'toolbar' => DebugToolbar::class,
        // 'honeypot' => Honeypot::class,
        // 'invalidchars' => InvalidChars::class,
        // 'secureheaders' => SecureHeaders::class,
        'authFilter' => \App\Filters\AuthFilter::class,
        // 'pagecache'     => \App\Filters\PageCache::class,
        // 'apiauthfilter' => \App\Filters\ApiAuthFilter::class,
        // 'cors'     => App\Filters\Cors::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array<string, array<string, array<string, string>>>|array<string, array<string>>
     * @phpstan-var array<string, list<string>>|array<string, array<string, array<string, string>>>
     */
    public array $globals = [
        'before' => [
            // 'honeypot',
            // 'csrf',
            // 'invalidchars',
            // 'cors',
            // 'forcehttps', // Force Global Secure Requests
            // 'pagecache',
            'authFilter' => ['except' => ['public/*','/api/generateToken','/api/generateGuestToken']],
        ],
        'after' => [
            // 'toolbar',
            // 'pagecache',   
            // 'performance', 
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     */
    public array $methods = [
       
    ];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     */
    // public array $filters = [];

    public $filters = [
        'authFilter' => ['before' => ['api/*']],
    // 'except' => ['api/getCategoriesdetails','api/validateCaptcha','api/getProductsdetails','api/bestsellingproducts','api/getcategoriesproduct','api/getcategorielist','api/catprodlist','api/wishlist','api/searchproduct','api/req_bulk_order','api/autogenbulk','api/sendNotification','api/getNotifications']],
    //     // 'apiauthfilter' => ['before' => ['api/*']],
    ];
}
