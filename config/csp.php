<?php

return [

    /*
     * A policy will determine which CSP headers will be set. A valid policy
     * extends `Spatie\Csp\Policies\Policy`
     */
    'policy' => \App\Support\Csp\Policies\FilamentPolicy::class,

    /*
     * This policy which will be used in report only mode.
     */
    'report_only_policy' => '',

    /*
     * This configuration will be used to generate the nonce.
     */
    'nonce' => [
        'generator' => Spatie\Csp\Nonce\RandomString::class,
    ],

    /*
     * You can specify which headers should be added.
     * Supports:
     *  - Content-Security-Policy
     *  - Content-Security-Policy-Report-Only
     */
    'enabled' => env('CSP_ENABLED', true),

    /*
     * The policy will be applied to all responses by Default.
     * You can turn it off here.
     */
    'auto_inject' => true,

];
