<?php

namespace GSharedContacts\Http\Controllers\Auth;

use Carbon\Carbon;
use Google_Client;
use GSharedContacts\Google\SharedContactsInterface;
use GSharedContacts\Http\Controllers\Controller;
use GSharedContacts\User;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Log;
use Session;
use Validator;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /** @var SharedContactsInterface */
    public $contacts;
    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     *
     * @param SharedContactsInterface $contacts
     */
    public function __construct(SharedContactsInterface $contacts)
    {
        $this->contacts = $contacts;
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $URL        = 'https://www.google.com/accounts/AuthSubRequest';
        $parameters = [
            'next'    => route('oauth.redirect'),
            'hd'      => $request->get('domain'),
            'scope'   => 'http://www.google.com/m8/feeds/',
            'secure'  => 0,
            'session' => 1,
        ];


        // save domain in session:
        Session::put('hd', $request->get('domain'));

        $URL .= '?' . http_build_query($parameters);

        Log::debug('Going to redirect to', ['url' => $URL]);
        Log::debug('Domain', ['domain', $request->get('domain')]);

        return redirect($URL);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginOAuth2(Request $request)
    {
        Session::put('hd', $request->get('domain'));
        $client = new Google_Client();
        $client->setClientId(config('google.client_id'));
        $client->setClientSecret(config('google.secret'));
        $client->setRedirectUri(config('google.redirect_uri'));
        $client->setScopes(config('google.scopes'));
        $authUrl = $client->createAuthUrl();

        Log::debug('AuthController::loginAuth2() sends you to URL ', ['url' => $authUrl]);

        return redirect($authUrl);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        $this->contacts->logout();

        Session::flush();
        Session::regenerate();

        return redirect(route('index'));
    }

    /**
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function oauth2callback(Request $request)
    {
        Log::debug('Now in oauth2callback()');
        if ($request->get('code')) {
            $client = new Google_Client();
            $client->setClientId(config('google.client_id'));
            $client->setClientSecret(config('google.secret'));
            $client->setRedirectUri(config('google.redirect_uri'));
            $client->setScopes(config('google.scopes'));
            $client->authenticate($request->get('code'));
            Log::debug('Code in request is: ', ['code' => $request->get('code')]);
            Log::debug('Access token is: ', ['token' => $client->getAccessToken()]);
            Session::put('access_token', $client->getAccessToken());

            return redirect(route('home'));
        } else {
            return view('error')->with('message', 'Some Google error');
        }
    }

    /**
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function redirect(Request $request)
    {
        $tempToken = $request->get('token');
        if (is_null($tempToken)) {
            return view('error')->with('message', 'If you deny access to this tool, it won\'t work!');
        }
        session('tempToken', $tempToken);

        $realToken = $this->contacts->getToken($tempToken);
        Session::put('token', $realToken);
        $time = new Carbon;
        $time->addMonth();
        Session::put('time', $time);

        return redirect(route('home'));
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        return User::create(
            [
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
            ]
        );
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make(
            $data, [
                     'name'     => 'required|max:255',
                     'email'    => 'required|email|max:255|unique:users',
                     'password' => 'required|min:6|confirmed',
                 ]
        );
    }

}

