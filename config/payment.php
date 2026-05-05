<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Provider
    |--------------------------------------------------------------------------
    |
    | This option defines the default payment provider that will be used
    | when no specific provider is requested.
    |
    */
    'default_provider' => env('PAYMENT_DEFAULT_PROVIDER', 'jaib'),

    /*
    |--------------------------------------------------------------------------
    | Payment Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure all the payment providers supported by your
    | application. Each provider can have its own configuration settings.
    |
    */
    'providers' => [
        'jaib' => [
            'enabled' => env('JAIB_ENABLED', true),
            'credentials' => [
                'user' => env('JAIB_USER'),
                'pass' => env('JAIB_PASS'),
                'agent_code' => env('JAIB_AGENT_CODE'),
            ],
            'api_url' => env('JAIB_API_URL', 'https://www.api2.e-jaib.com:5088'),
            'supported_currencies' => ['YER', 'USD'],
            'features' => [
                'refund' => true,
                'status_check' => true,
                'code_validation' => false,
                'password_change' => true,
            ],
            'timeout' => env('JAIB_TIMEOUT', 30),
        ],

        'jawali' => [
            'enabled' => env('JAWALI_ENABLED', true),
            'credentials' => [
                'username' => env('JAWALI_USERNAME'),
                'password' => env('JAWALI_PASS'),
                'org_id' => env('JAWALI_ORGID'),
                'agent_id' => env('JAWALI_AGENT_ID'),
                'agent_pwd' => env('JAWALI_AGENT_PWD'),
            ],
            'api_url' => env('JAWALI_API_URL', 'https://app.wecash.com.ye:8493'),
            'supported_currencies' => ['YER'],
            'features' => [
                'refund' => false,
                'status_check' => true,
                'code_validation' => true,
                'enquiry' => true,
            ],
            'timeout' => env('JAWALI_TIMEOUT', 30),
        ],

        'floosak' => [
            'enabled' => env('FLOOSAK_ENABLED', true),
            'credentials' => [
                'short_code' => env('FLOOSAK_SHORT_CODE'),
                'phone_number' => env('FLOOSAK_PHONE_NUMBER'),
                'api_key' => env('FLOOSAK_API_KEY'),
            ],
            'api_url' => env('FLOOSAK_API_URL', 'https://staging.fintech-expert.net'),
            'supported_currencies' => ['YER', 'USD'],
            'features' => [
                'refund' => false,
                'status_check' => false,
                'code_validation' => false,
                'two_step_payment' => true, // Request then confirm
            ],
            'timeout' => env('FLOOSAK_TIMEOUT', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Types
    |--------------------------------------------------------------------------
    |
    | Define the supported payment types and their configurations.
    |
    */
    'payment_types' => [
        'cart_payment' => [
            'name' => 'Cart Payment',
            'description' => 'Payment for items in shopping cart',
            'enabled' => true,
            'required_fields' => ['combined_order_id', 'user_id', 'provider'],
        ],
        'wallet_payment' => [
            'name' => 'Wallet Recharge',
            'description' => 'Recharge user wallet',
            'enabled' => env('WALLET_PAYMENT_ENABLED', true),
            'required_fields' => ['amount', 'user_id', 'provider'],
        ],
        'order_re_payment' => [
            'name' => 'Order Re-payment',
            'description' => 'Re-pay for existing order',
            'enabled' => true,
            'required_fields' => ['order_id', 'user_id', 'provider'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Configure currency-specific settings and provider mappings.
    |
    */
    'currencies' => [
        'YER' => [
            'name' => 'Yemeni Rial',
            'symbol' => 'YER',
            'decimal_places' => 0,
            'preferred_providers' => ['jaib', 'jawali', 'floosak'],
        ],
        'USD' => [
            'name' => 'US Dollar',
            'symbol' => 'USD',
            'decimal_places' => 2,
            'preferred_providers' => ['jaib', 'floosak', 'jawali'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configure fallback behavior when primary providers fail.
    |
    */
    'fallback' => [
        'enabled' => env('PAYMENT_FALLBACK_ENABLED', true),
        'providers' => [
            'jaib' => ['jawali', 'floosak'],
            'jawali' => ['jaib'],
            'floosak' => ['jaib'],
        ],
        'max_attempts' => env('PAYMENT_MAX_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure payment-specific logging settings.
    |
    */
    'logging' => [
        'enabled' => env('PAYMENT_LOGGING_ENABLED', true),
        'level' => env('PAYMENT_LOG_LEVEL', 'info'),
        'channel' => env('PAYMENT_LOG_CHANNEL', 'daily'),
        'log_requests' => env('PAYMENT_LOG_REQUESTS', true),
        'log_responses' => env('PAYMENT_LOG_RESPONSES', true),
        'mask_sensitive_data' => env('PAYMENT_MASK_SENSITIVE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for authentication tokens and other data.
    |
    */
    'cache' => [
        'enabled' => env('PAYMENT_CACHE_ENABLED', true),
        'ttl' => env('PAYMENT_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('PAYMENT_CACHE_PREFIX', 'payment_'),
        'store' => env('PAYMENT_CACHE_STORE', null), // Use default cache store
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security settings for payment processing.
    |
    */
    'security' => [
        'encrypt_tokens' => env('PAYMENT_ENCRYPT_TOKENS', true),
        'mask_phone_numbers' => env('PAYMENT_MASK_PHONE_NUMBERS', true),
        'rate_limiting' => [
            'enabled' => env('PAYMENT_RATE_LIMITING_ENABLED', true),
            'max_attempts' => env('PAYMENT_MAX_ATTEMPTS_PER_MINUTE', 60),
            'decay_minutes' => env('PAYMENT_RATE_LIMIT_DECAY', 1),
        ],
        'ip_whitelist' => env('PAYMENT_IP_WHITELIST', ''),
        'allowed_user_agents' => [
            'mobile_app',
            'web_app',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Define validation rules for payment requests.
    |
    */
    'validation' => [
        'phone_number' => [
            'required' => true,
            'regex' => '/^(\+967|967|00967)?[0-9]{9}$/',
            'format' => '+967XXXXXXXXX',
        ],
        'amount' => [
            'min' => env('PAYMENT_MIN_AMOUNT', 1),
            'max' => env('PAYMENT_MAX_AMOUNT', 1000000),
        ],
        'currency_codes' => ['YER', 'USD'],
        'request_id' => [
            'format' => '/^[0-9]{8}-[0-9]{6}[0-9]{2}[A-Z]$/',
            'unique' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhooks for payment status updates.
    |
    */
    'webhooks' => [
        'enabled' => env('PAYMENT_WEBHOOKS_ENABLED', false),
        'url' => env('PAYMENT_WEBHOOK_URL'),
        'secret' => env('PAYMENT_WEBHOOK_SECRET'),
        'events' => [
            'payment.completed',
            'payment.failed',
            'payment.refunded',
            'payment.cancelled',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional Providers
    |--------------------------------------------------------------------------
    |
    | Register additional payment providers that can be loaded at runtime.
    |
    */
    'additional_providers' => [
        // Example:
        // 'custom_provider' => [
        //     'class' => '\App\Services\Payment\Providers\CustomProvider',
        //     'config' => [
        //         'api_key' => env('CUSTOM_PROVIDER_API_KEY'),
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for testing and development environments.
    |
    */
    'testing' => [
        'enabled' => env('PAYMENT_TESTING_ENABLED', false),
        'mock_responses' => env('PAYMENT_MOCK_RESPONSES', false),
        'test_providers' => [
            'test_provider' => [
                'class' => '\App\Services\Payment\Providers\TestProvider',
                'always_succeed' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Alerts
    |--------------------------------------------------------------------------
    |
    | Configure monitoring and alerting for payment systems.
    |
    */
    'monitoring' => [
        'enabled' => env('PAYMENT_MONITORING_ENABLED', false),
        'health_check_interval' => env('PAYMENT_HEALTH_CHECK_INTERVAL', 300), // 5 minutes
        'alert_on_failure_rate' => env('PAYMENT_ALERT_FAILURE_RATE', 10), // percent
        'notification_channels' => [
            'slack' => env('PAYMENT_SLACK_WEBHOOK'),
            'email' => env('PAYMENT_ALERT_EMAIL'),
        ],
    ],
];
