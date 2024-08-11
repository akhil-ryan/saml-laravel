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
        $this->userModel = config('saml.user_model');
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

            $username = explode("@", $email)[0];

            $id = "okta";

            $users = $this->userModel::where(['email' => $email])->first();
            if ($users) {
                \Illuminate\Support\Facades\Auth::login($users);
                $this->userModel::where(['id' => Auth::user()->id])->increment('loginCount');
                $this->userModel::where('id', Auth::user()->id)->update(['provider_id' => $id, 'fname' => $first_name, 'lname' => $last_name]);
                $info = "logged In";
            } else {
                $user = $this->userModel::create([
                    'username' => $username,
                    'email' => $email
                ]);
                $users = $this->userModel::where(['email' => $email])->first();
                $this->userModel::where('email', $email)->update(['provider_id' => $id, 'fname' => $first_name, 'lname' => $last_name]);
                Auth::login($users);
            }
            return redirect()->route(config('saml.home_url'));
        } else {
            return redirect()->route('Logout')->with('error', 'SAML authentication failed');
        }
    }

    public function oktaSamlAcs(Request $request)
    {
        $auth = new SAuth(config('saml'));
        $auth->processResponse();
        if ($auth->isAuthenticated()) {
            $userAttributes = $auth->getAttributes();
            return redirect()->intended(route('user.index'));
        } else {
            abort(401, 'SAML authentication failed.');
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
        \Illuminate\Support\Facades\Session::flush();
        Auth::logout();
        return redirect('/')->withSuccess('Logout Successful.');
    }
}
