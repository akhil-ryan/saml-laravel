<?php

return [
    'strict' => true,
    'debug' => true,
    'baseurl' => env('APP_URL') . env('SAML_LOGIN_URL'),
    'sp' => [
        'entityId' => env('SAML_SA_ENTITY'),
        'assertionConsumerService' => [
            'url' => env('APP_URL') . env('SAML_ACS_URL'),
            'binding' => \OneLogin\Saml2\Constants::BINDING_HTTP_POST,
        ],
        'singleLogoutService' => [
            'url' => env('APP_URL') . env('SAML_LOGOUT_URL'),
            'binding' => \OneLogin\Saml2\Constants::BINDING_HTTP_REDIRECT,
        ],
        'NameIDFormat' => \OneLogin\Saml2\Constants::NAMEID_UNSPECIFIED
    ],
    'idp' => [
        'entityId' => env('SAML_ENTITY'),
        'singleSignOnService' => [
            'url' => env('SAML_HOME_URL') . env('SAML_SSO'),
            'binding' => \OneLogin\Saml2\Constants::BINDING_HTTP_REDIRECT,
        ],
        'singleLogoutService' => [
            'url' => env('SAML_HOME_URL'),
            'binding' => \OneLogin\Saml2\Constants::BINDING_HTTP_REDIRECT,
        ],
        'x509cert' => env('SAML_CERT'),
    ],
    'enable_route' => env('SAML_ROUTE_ENABLE'),
    'routes' => [
        'oktaSamlMetadata' => '/metadata',
        'oktaSamlLogin' => '/login/{token?}',
        'oktaSamlLoginResponse' => '/login/{token}',
        'oktaSamlAcs' => '/acs',
        'oktaSamlLogout' => '/logout'
    ],
    'model' => \App\Models\User::class,
    'home_url' => env('SAML_REDIRECT_URL'),
    'logout_url' => env('SAML_LOGOUT_URL'),
    'middleware' => env('SAML_MIDDLEWARE'),
    'schema' => env('SAML_SCHEMA')
];