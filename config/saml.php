<?php

return [
    'strict' => true,
    'debug' => true,
    'baseurl' => 'APP_BASE_URL',
    'sp' => [
        'entityId' => 'ENTITY_ID',
        'assertionConsumerService' => [
            'url' => 'APP_CONSUME_URL',
            'binding' => \OneLogin\Saml2\Constants::BINDING_HTTP_POST,
        ],
        'singleLogoutService' => [
            'url' => 'APP_LOGOUT_URL',
            'binding' => \OneLogin\Saml2\Constants::BINDING_HTTP_REDIRECT,
        ],
        'NameIDFormat' => \OneLogin\Saml2\Constants::NAMEID_UNSPECIFIED,
    ],
    'idp' => [
        'entityId' => 'ENTITY_ID_URL',
        'singleSignOnService' => [
            'url' => 'SAML_SSO_URL',
            'binding' => \OneLogin\Saml2\Constants::BINDING_HTTP_REDIRECT,
        ],
        'singleLogoutService' => [
            'url' => 'SAML_LOGOUT_URL',
            'binding' => \OneLogin\Saml2\Constants::BINDING_HTTP_REDIRECT,
        ],
        'x509cert' => 'SAML_CERT',
    ],
    'enable_route' => true,
    'routes' => [
        'oktaSamlMetadata' => '/okta/metadata',
        'oktaSamlLogin' => '/okta/login/{token?}',
        'oktaSamlLoginResponse' => '/okta/login/{token}',
        'oktaSamlAcs' => '/okta/acs',
        'oktaSamlLogout' => '/okta/logout',
    ],
    'user_model' => \App\Models\User::class,
    'home_url' => '/home',
];