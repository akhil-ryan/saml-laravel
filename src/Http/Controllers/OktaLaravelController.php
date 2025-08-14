<?php

namespace Oktalogin\SamlOktaLogin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OneLogin\Saml2\Auth as SAuth;
use OneLogin\Saml2\Metadata;
use Illuminate\Support\Facades\Session;

class OktaLaravelController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = config('saml.model');
        $this->middleware(config('saml.middleware'));
    }

    public static function getSamlConfig($metadataUrl = null)
    {
        if ($metadataUrl) {
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->get($metadataUrl);
                $xmlContent = $response->getBody()->getContents();
                $xml = simplexml_load_string($xmlContent);

                $namespaces = $xml->getNamespaces(true);

                $entityID = $xml->attributes()->entityID ?? '';
                $entityID = json_decode(json_encode($entityID), true);
                $idp = $xml->children($namespaces['md'])->IDPSSODescriptor;

                $ssoLocations = [];
                if ($idp && $idp->SingleSignOnService) {
                    foreach ($idp->SingleSignOnService as $sso) {
                        $location = $sso->attributes()->Location ?? '';
                        if (!empty($location)) {
                            $ssoLocations[] = (string)$location;
                        }
                    }
                }
                $keyDescriptor = $idp->KeyDescriptor;
                $keyInfo = $keyDescriptor->children($namespaces['ds'])->KeyInfo;
                $x509Certificate = (string)$keyInfo->X509Data->X509Certificate ?? '';

                return [
                    'entityId' => $entityID[0],
                    'singleSignOnService' => [
                        'url' => $ssoLocations[0] ?? '',
                        'binding' => \OneLogin\Saml2\Constants::BINDING_HTTP_REDIRECT,
                    ],
                    'singleLogoutService' => [
                        'url' => $ssoLocations[0] ?? '',
                        'binding' => \OneLogin\Saml2\Constants::BINDING_HTTP_REDIRECT,
                    ],
                    'x509cert' => $x509Certificate,
                ];
            } catch (\Exception $e) {
                throw new \Exception("Error fetching or parsing SAML metadata: " . $e->getMessage());
            }
        }

        return [
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
        ];
    }

    public function oktaSamlLogin(Request $request)
    {
        $auth = new SAuth(config('saml'));
        $auth->login();
        return redirect()->to($auth->ssoUrl());
    }

    public function oktaSamlLoginResponse()
    {
        $auth = new SAuth(config('saml'));
        $auth->processResponse();
        if ($auth->isAuthenticated()) {
            $attributes = $auth->getAttributes();
            $email = $attributes['Email'][0] ?? null;
            $first_name = $attributes['FirstName'][0] ?? null;
            $last_name = $attributes['LastName'][0] ?? null;

            $nameId = $auth->getNameId();
            $sessionIndex = $auth->getSessionIndex();
            $nameIdFormat = $auth->getNameIdFormat();

            $userDataJson = config('saml.schema');
            $userData = json_decode($userDataJson, true);

            $finalUserData = array_map(function ($value) use ($first_name, $last_name, $email) {
                return str_replace(
                    ['$first_name', '$last_name', '$email'],
                    [$first_name, $last_name, $email],
                    $value
                );
            }, $userData);

            $instanceModel = new $this->userModel;
            if ($users = $instanceModel::where('email', $email)->first()) {
                $instanceModel::where('id', $users->id)->update($finalUserData);
            } else {
                $users = $instanceModel::create($finalUserData);
            }
            Auth::login($users);
            return redirect()->route(config('saml.home_url'))->with('info', "Login Successful. Welcome " . $first_name . " " . $last_name);
        } else {
            self::flushSession();
            return redirect()->route(config('saml.logout'))->with('error', 'SAML authentication failed');
        }
    }

    public function oktaSamlAcs(Request $request)
    {
        $auth = new SAuth(config('saml'));
        $auth->processResponse();
        if ($auth->isAuthenticated()) {
            $userAttributes = $auth->getAttributes();
            return redirect()->intended(route(config('saml.home_url')));
        } else {
            self::flushSession();
            return redirect()->route('Logout')->with('error', 'SAML authentication failed');
        }
    }

    public function oktaSamlMetadata(Request $request)
    {
        $settings = config('saml');
        $metadata = Metadata::builder($settings)->build();
        return response($metadata, 200, [
            'Content-Type' => 'text/xml',
        ]);
    }

    public function oktaSamlLogout(Request $request)
    {
        $auth = new SAuth(config('saml'));
        $auth->logout();
        self::flushSession();
        return redirect('/')->withSuccess('Logout Successful.');
    }

    public static function samlAuth()
    {
        $auth = new SAuth(config('saml'));
        $auth->login();
        return redirect()->to($auth->ssoUrl());
    }

    public static function samlLogin()
    {
        $auth = new SAuth(config('saml'));
        $auth->processResponse();
        if ($auth->isAuthenticated()) {
            return json_encode([
                'status' => true,
                'data' => $auth->getAttributes(),
                'message' => 'SAML authentication success.'
            ], 200);
        } else {
            self::flushSession();
            return json_encode([
                'status' => false,
                'message' => 'SAML authentication failed.'
            ], 401);
        }
    }

    public static function samlMetadata()
    {
        $settings = config('saml');
        $metadata = Metadata::builder($settings)->build();
        return response($metadata, 200, [
            'Content-Type' => 'text/xml',
        ]);
    }

    public static function samlAcs()
    {
        $auth = new SAuth(config('saml'));
        $auth->processResponse();
        if ($auth->isAuthenticated()) {
            $userAttributes = $auth->getAttributes();
            return redirect()->intended(route(config('saml.home_url')));
        } else {
            self::flushSession();
            abort(401, 'SAML authentication failed.');
        }
    }

    public static function samlLogout()
    {
        self::flushSession();
        $auth = new SAuth(config('saml'));
        $auth->logout();
        return redirect('/')->withSuccess('Logout Successful.');
    }

    public static function flushSession()
    {
        Session::flush();
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}