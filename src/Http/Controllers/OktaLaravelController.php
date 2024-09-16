<?php

namespace Oktalogin\SamlOktaLogin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OneLogin\Saml2\Auth as SAuth;
use OneLogin\Saml2\Metadata;

class OktaLaravelController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = config('saml.model');
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

            if ($users = $this->userModel::where(['email' => $email])->first()) {
                $this->userModel::where('id', Auth::user()->id)->update(config('saml.schema'));
            } else {
                $users = $this->userModel::create(config('saml.schema'));
            }
            Auth::login($users);
            return redirect()->to(config('saml.home_url'))->with('info', "Login Successful. Welcome " . $first_name . " " . $last_name);
        } else {
            return redirect()->route('saml.logout')->with('error', 'SAML authentication failed');
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
        Auth::logout();
        \Illuminate\Support\Facades\Session::flush();
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
            abort(401, 'SAML authentication failed.');
        }
    }

    public static function samlLogout()
    {
        $auth = new SAuth(config('saml'));
        $auth->logout();
        Auth::logout();
        \Illuminate\Support\Facades\Session::flush();
        return redirect('/')->withSuccess('Logout Successful.');
    }
}
