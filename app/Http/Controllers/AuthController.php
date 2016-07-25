<?php
namespace GContacts\Http\Controllers;

use Carbon\Carbon as Carbon;
use GContacts\Google\SharedContactsInterface;
use Google_Client;
use URL;
use Config;
use Redirect;
use View;
use Input;
use Session;

/**
 * Class AuthController
 */
class AuthController extends Controller
{
    /**
     * @param SharedContactsInterface $contacts
     */
    public function __construct(SharedContactsInterface $contacts)
    {
        $this->contacts = $contacts;
    }


    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login()
    {
        $URL        = 'https://www.google.com/accounts/AuthSubRequest';
        $parameters = [
            'next'    => URL::Route('oauth.redirect'),
            'hd'      => Input::get('domain'),
            'scope'   => 'http://www.google.com/m8/feeds/',
            'secure'  => 0,
            'session' => 1
        ];

        // save domain in session:
        Session::put('hd', Input::get('domain'));

        $URL .= '?' . http_build_query($parameters);
        return Redirect::to($URL);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginOAuth2()
    {
        Session::put('hd', Input::get('domain'));
        $client = new Google_Client();
        $client->setClientId(Config::get('google.client_id'));
        $client->setClientSecret(Config::get('google.secret'));
        $client->setRedirectUri(Config::get('google.redirect_uri'));
        $client->setScopes(Config::get('google.scopes'));
        $authUrl = $client->createAuthUrl();
        return Redirect::to($authUrl);
    }

    /**
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function oauth2callback()
    {
        if (Input::get('code')) {
            $client = new Google_Client();
            $client->setClientId(Config::get('google.client_id'));
            $client->setClientSecret(Config::get('google.secret'));
            $client->setRedirectUri(Config::get('google.redirect_uri'));
            $client->setScopes(Config::get('google.scopes'));
            $client->authenticate(Input::get('code'));
            Session::put('access_token', json_decode($client->getAccessToken()));
            return Redirect::route('home');
        } else {
            return view('error')->with('message', 'Some Google error');
        }
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        $this->contacts->logout();

        Session::flush();
        Session::regenerate();
        return Redirect::route('index');
    }

    /**
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        $tempToken = Input::get('token');
        if (is_null($tempToken)) {
            return View::make('error')->with('message', 'If you deny access to this tool, it won\'t work!');
        }
        Session::put('tempToken', $tempToken);

        $realToken = $this->contacts->getToken($tempToken);
        Session::put('token', $realToken);
        $time = new Carbon;
        $time->addMonth();
        Session::put('time', $time);
        return Redirect::route('home');
    }

} 