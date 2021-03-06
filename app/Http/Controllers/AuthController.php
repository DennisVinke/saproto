<?php

namespace Proto\Http\Controllers;

use Illuminate\Http\Request;

use PragmaRX\Google2FA\Google2FA;

use Adldap\Adldap;
use Adldap\Connections\Provider;

use Proto\Models\AchievementOwnership;
use Proto\Models\Address;
use Proto\Models\Alias;
use Proto\Models\Bank;
use Proto\Models\EmailListSubscription;
use Proto\Models\PasswordReset;
use Proto\Models\RfidCard;
use Proto\Models\User;
use Proto\Models\Member;
use Proto\Models\HashMapItem;

use Auth;
use Proto\Models\WelcomeMessage;
use Redirect;
use Hash;
use Mail;
use Session;

class AuthController extends Controller
{

    /******************************************************
     * These are the regular, non-static methods serving as entry point to the AuthController
     *
     *
     *
     */

    /*
     * Present the login page.
     */
    public function getLogin(Request $request)
    {


        if (Auth::check()) {
            if ($request->has('SAMLRequest')) {
                return AuthController::handleSAMLRequest(Auth::user(), $request->input('SAMLRequest'));
            }
            return Redirect::route('homepage');
        } else {
            if ($request->has('SAMLRequest')) {
                Session::flash('incoming_saml_request', $request->get('SAMLRequest'));
            }
            return view('auth.login');
        }

    }

    /**
     * Handle a submitted log-in form. Returns the application's response.
     *
     * @param Request $request The request object, needed for the log-in data.
     * @param Google2FA $google2fa The Google2FA object, because this is apparently the only way to access it.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postLogin(Request $request, Google2FA $google2fa)
    {

        Session::keep('incoming_saml_request');

        if (Auth::check()) { // User is already logged in

            AuthController::postLoginRedirect($request);

        } else { // User is not yet logged in.

            // Catch a login form submission for two factor authentication.
            if ($request->session()->has('2fa_user')) {
                return AuthController::handleTwofactorSubmit($request, $google2fa);
            }

            // Otherwise this is a regular login.
            return AuthController::handleRegularLogin($request);

        }

    }

    /**
     * Handle a request to the log-out URL.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getLogout()
    {
        Auth::logout();
        return Redirect::route('homepage');
    }

    /**
     * Handle a request for the register-an-account page.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getRegister(Request $request)
    {
        if (Auth::check()) {
            $request->session()->flash('flash_message', 'You already have an account. To register an account, please log off.');
            return Redirect::route('user::dashboard');
        }

        if ($request->wizard) Session::flash('wizard', true);

        return view('users.register');
    }

    /**
     * Handle a submission of the register-an-account page.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRegister(Request $request)
    {
        if (Auth::check()) {
            $request->session()->flash('flash_message', 'You already have an account. To register an account, please log off.');
            return Redirect::route('user::dashboard');
        }

        $request->session()->flash('register_persist', $request->all());

        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'name' => 'required|string',
            'calling_name' => 'required|string',
            'birthdate' => 'required|date_format:Y-m-d',
            'gender' => 'required|in:1,2,9',
            'nationality' => 'required|string',
            'phone' => 'required|regex:(\+[0-9]{8,16})',
            'g-recaptcha-response' => 'required|recaptcha'
        ]);

        $user = User::create($request->except('g-recaptcha-response'));

        if (Session::get('wizard')) {

            HashMapItem::create([
                'key' => 'wizard',
                'subkey' => $user->id,
                'value' => 1
            ]);
        }

        $user->save();

        AuthController::makeLdapAccount($user);

        $email = $user->email;
        $name = $user->mail;

        Mail::queueOn('high', 'emails.registration', ['user' => $user], function ($m) use ($email, $name) {
            $m->replyTo('board@proto.utwente.nl', 'Study Association Proto');
            $m->to($email, $name);
            $m->subject('Account registration at Study Association Proto');
        });

        AuthController::dispatchPasswordEmailFor($user);

        EmailListController::autoSubscribeToLists('autoSubscribeUser', $user);

        if (!Auth::check()) {
            $request->session()->flash('flash_message', 'Your account has been created. You will receive an e-mail with instructions on how to set your password shortly.');
            return Redirect::route('homepage');
        }
    }

    /**
     * Handle a request to delete the current user account.
     *
     * @param Request $request
     * @param $id The user id.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id != Auth::id() && !Auth::user()->can('board')) {
            abort(403);
        }

        if ($user->member) {
            $request->session()->flash('flash_message', 'You cannot deactivate your account while you are a member.');
            return Redirect::back();
        }

        Address::where('user_id', $user->id)->delete();
        Bank::where('user_id', $user->id)->delete();
        EmailListSubscription::where('user_id', $user->id)->delete();
        AchievementOwnership::where('user_id', $user->id)->delete();
        Alias::where('user_id', $user->id)->delete();
        RfidCard::where('user_id', $user->id)->delete();
        WelcomeMessage::where('user_id', $user->id)->delete();

        if ($user->photo) {
            $user->photo->delete();
        }

        $user->password = null;
        $user->remember_token = null;
        $user->birthdate = null;
        $user->gender = null;
        $user->nationality = null;
        $user->phone = null;
        $user->website = null;
        $user->utwente_username = null;
        $user->tfa_totp_key = null;

        $user->phone_visible = 0;
        $user->address_visible = 0;
        $user->receive_sms = 0;

        $user->save();

        $user->delete();

        $request->session()->flash('flash_message', 'Your account has been deactivated.');
        return Redirect::route('homepage');
    }

    /**
     * Handle a request to see the begin-with-password-reset page.
     *
     * @return mixed
     */
    public function getEmail()
    {
        return view('auth.passreset_mail');
    }

    /**
     * Handle a submission of the begin-with-password-reset page.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEmail(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user !== null) {

            AuthController::dispatchPasswordEmailFor($user);

            $request->session()->flash('flash_message', 'We\'ve dispatched an e-mail to you with instruction to reset your password.');
            return Redirect::route('login::show');

        } else {
            $request->session()->flash('flash_message', 'We could not find a user with the e-mail address you entered.');
            return Redirect::back();
        }
    }

    /**
     * Handle a request to see the continue-with-password-reset page.
     *
     * @param Request $request
     * @param $token The reset token, as e-mailed to the user.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getReset(Request $request, $token)
    {
        PasswordReset::where('valid_to', '<', date('U'))->delete();
        $reset = PasswordReset::where('token', $token)->first();
        if ($reset !== null) {
            return view('auth.passreset_pass', ['reset' => $reset]);
        } else {
            $request->session()->flash('flash_message', 'This reset token does not exist or has expired.');
            return Redirect::route('login::resetpass');
        }
    }

    /**
     * Handle a submission of the continue-with-password-reset page.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postReset(Request $request)
    {
        PasswordReset::where('valid_to', '<', date('U'))->delete();
        $reset = PasswordReset::where('token', $request->token)->first();
        if ($reset !== null) {

            if ($request->password !== $request->password_confirmation) {
                $request->session()->flash('flash_message', 'Your passwords don\'t match.');
                return Redirect::back();
            } elseif (strlen($request->password) < 10) {
                $request->session()->flash('flash_message', 'Your new password should be at least 10 characters long.');
                return Redirect::back();
            }

            $reset->user->setPassword($request->password);

            PasswordReset::where('token', $request->token)->delete();

            $request->session()->flash('flash_message', 'Your password has been changed.');
            return Redirect::route('login::show');

        } else {
            $request->session()->flash('flash_message', 'This reset token does not exist or has expired.');
            return Redirect::route('login::resetpass');
        }
    }

    public function passwordChangeGet(Request $request)
    {
        if (!Auth::check()) {
            $request->session()->flash('flash_message', 'Please log-in first.');
            return Redirect::route('login::show');
        }
        return view('auth.passchange');
    }

    /**
     * Handle a submitted password change form.
     *
     * @param Request $request The request object.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordChangePost(Request $request)
    {

        if (!Auth::check()) {
            $request->session()->flash('flash_message', 'Please log-in first.');
            return Redirect::route('login::show');
        }

        $user = Auth::user();

        $pass_old = $request->get('old_password');
        $pass_new1 = $request->get('new_password1');
        $pass_new2 = $request->get('new_password2');

        $user_verify = AuthController::verifyCredentials($user->email, $pass_old);

        if ($user_verify && $user_verify->id === $user->id) {
            if ($pass_new1 !== $pass_new2) {
                $request->session()->flash('flash_message', 'The new passwords do not match.');
                return view('auth.passchange');
            } elseif (strlen($pass_new1) < 10) {
                $request->session()->flash('flash_message', 'Your new password should be at least 10 characters long.');
                return view('auth.passchange');
            } else {
                $user->setPassword($pass_new1);
                $request->session()->flash('flash_message', 'Your password has been changed.');
                return Redirect::route('user::dashboard');
            }
        }

        $request->session()->flash('flash_message', 'Old password incorrect.');
        return view('auth.passchange');

    }

    /**
     * Display the password sync form to users to allow them to sync their password between services.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordSyncGet(Request $request)
    {
        if (!Auth::check()) {
            $request->session()->flash('flash_message', 'Please log-in first.');
            return Redirect::route('login::show');
        }
        return view('auth.sync');
    }

    /**
     * Process a request to synchronize ones password.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordSyncPost(Request $request)
    {
        if (!Auth::check()) {
            $request->session()->flash('flash_message', 'Please log-in first.');
            return Redirect::route('login::show');
        }

        $pass = $request->get('password');
        $user = Auth::user();

        $user_verify = AuthController::verifyCredentials($user->email, $pass);

        if ($user_verify && $user_verify->id === $user->id) {
            $user->setPassword($pass);
            $request->session()->flash('flash_message', 'Your password was successfully synchronized.');
            return Redirect::route('user::dashboard');
        } else {
            $request->session()->flash('flash_message', 'Password incorrect.');
            return view('auth.sync');
        }

        return view('auth.sync');
    }


    /**
     * Handle a request for UTwente SSO auth.
     *
     * @return Redirect
     */
    public function startUtwenteAuth()
    {
        Session::reflash();
        return redirect('saml2/login');
    }

    /**
     * This is where we land after a successfull UTwente SSO auth.
     * We do the authentication here because only using the Event handler for the SAML login doesn't let us do the proper redirects.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function utwenteAuthPost()
    {

        if (!Session::has('utwente_sso_user')) {
            return Redirect::route('login::show');
        }

        $remoteUser = Session::get('utwente_sso_user');
        $remoteData = [
            'uid' => $remoteUser['urn:mace:dir:attribute-def:uid'][0],
            'surname' => $remoteUser['urn:mace:dir:attribute-def:sn'][0],
            'mail' => $remoteUser['urn:mace:dir:attribute-def:mail'][0],
            'displayname' => $remoteUser['urn:mace:dir:attribute-def:displayName'][0],
            'utwente_role' => $remoteUser['urn:mace:dir:attribute-def:eduPersonAffiliation'][0],
            'givenname' => $remoteUser['urn:mace:dir:attribute-def:givenName'][0],
            'commonname' => $remoteUser['urn:mace:dir:attribute-def:cn'][0],
        ];
        $remoteUsername = $remoteData['uid'];

        // We can be here for two reasons:
        // Reason 1: we were trying to link a UTwente account to a user
        if (Session::has('link_utwente_to_user')) {
            $user = Session::get('link_utwente_to_user');
            $user->utwente_username = $remoteUsername;
            $user->save();
            Session::flash('flash_message', 'We linked your UTwente account ' . $remoteUsername . ' to your Proto account.');
            if (Session::has('link_wizard')) {
                return Redirect::route('becomeamember');
            } else {
                return Redirect::route('user::dashboard', ['id' => $user->id]);
            }
        }

        // Reason 2: we were trying to login using a UTwente account
        Session::keep('incoming_saml_request');
        $localUser = User::where('utwente_username', $remoteUsername)->first();

        if ($localUser == null) {
            Session::flash('flash_message', 'Could not find a Proto account for your UTwente account ' . $remoteUsername . ', ' . $remoteData["givenname"] . '. Did you link it already?');
            return Redirect::route('login::show');
        }

        return AuthController::continueLogin($localUser);

    }

    /**
     * Handle a request for a user's username
     *
     * @return Redirect
     */
    public function requestUsername(Request $request)
    {
        if ($request->has('email')) {
            $user = User::whereEmail($request->get('email'))->first();
            if ($user) {
                if ($user->member) {
                    Session::flash('flash_message', 'Your Proto username is <strong>' . $user->member->proto_username . '</strong>');
                    Session::flash('login_username', $user->member->proto_username);
                } else {
                    Session::flash('flash_message', 'Only members have a Proto username. You can login using your e-mail address.');
                }
            } else {
                Session::flash('flash_message', 'We could not find a user with that e-mail address.');
            }
            return Redirect::route('login::show');
        }
        return view('auth.username');
    }

    /******************************************************
     * These are the static helper functions of the AuthController for more overview and modularity. Heuh!
     *
     *
     *
     */

    /**
     * This static function takes a supplied username and password, and returns the associated user if the combination is valid. Accepts either Proto username or e-mail and password.
     *
     * @param $username The e-mail address or Proto username.
     * @param $password The password.
     * @return User The user associated with the credentials, or null if no user could be found or credentials are invalid.
     */
    public static function verifyCredentials($username, $password)
    {

        $user = User::where('email', $username)->first();
        if ($user == null) {
            $member = Member::where('proto_username', $username)->first();
            $user = ($member ? $member->user : null);
        }

        if ($user != null && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;

    }

    /**
     * Login the supplied user and perform post-login checks and redirects. Returns the application's response.
     *
     * @param User $user The user to be logged in.
     * @param Request $request The request object, needed to handle some checks.
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function loginUser(User $user)
    {
        Auth::login($user, true);
        if (Session::has('incoming_saml_request')) {
            return AuthController::handleSAMLRequest(Auth::user(), Session::get('incoming_saml_request'));
        }
        return AuthController::postLoginRedirect();
    }

    /**
     * The login has been completed (succesfull or not). Return where the user is supposed to be redirected.
     *
     * @param Request $request The request object.
     */
    private static function postLoginRedirect()
    {
        return Redirect::route('homepage');
    }

    /**
     * Handle the submission of a regular log-in form with username and password. Return the application's response.
     *
     * @param Request $request Thje request object for the data.
     * @return \Illuminate\Http\RedirectResponse
     */
    private static function handleRegularLogin(Request $request)
    {

        $username = $request->input('email');
        $password = $request->input('password');

        $user = AuthController::verifyCredentials($username, $password);

        if ($user) {
            return AuthController::continueLogin($user);
        }

        $request->session()->flash('flash_message', 'Invalid username of password provided.');
        return Redirect::route('login::show');

    }

    /**
     * We know a user has identified itself, but we still need to check for other stuff like SAML or Two Factor Authentication. We do this here.
     *
     * @param User $user The username to be logged in.
     * @param Request $request Thje request object for the data.
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function continueLogin(User $user)
    {

        // Catch users that have 2FA enabled.
        if ($user->tfa_totp_key) {
            Session::flash('2fa_user', $user);
            return view('auth.2fa');
        } else {
            return AuthController::loginUser($user);
        }

    }

    /**
     * Handle the submission of two factor authentication data. Return the application's response.
     *
     * @param Request $request The request object for the data.
     * @param Google2FA $google2fa The Google2FA object, because this is apparently the only way to access it.
     * @return \Illuminate\Http\RedirectResponse
     */
    private static function handleTwofactorSubmit(Request $request, Google2FA $google2fa)
    {

        $user = $request->session()->get('2fa_user');

        /*
         * Time based Two Factor Authentication (Google2FA)
         */
        if ($user->tfa_totp_key && $request->has('2fa_totp_token') && $request->input('2fa_totp_token') != '') {

            // Verify if the response is valid.
            if ($google2fa->verifyKey($user->tfa_totp_key, $request->input('2fa_totp_token'))) {
                return AuthController::loginUser($user);
            } else {
                $request->session()->flash('flash_message', 'Your code is invalid. Please try again.');
                $request->session()->reflash();
                return view('auth.2fa');
            }

        }

        /*
         * Something we don't recognize
         */
        $request->session()->flash('flash_message', 'Please complete the requested challenge.');
        $request->session()->reflash();
        return view('auth.2fa');

    }

    /**
     * Static helper function that will prepare an LDAP account associated with a new local user.
     *
     * @param $user The user to make the LDAP account for.
     */
    public static function makeLdapAccount($user)
    {

        $ad = new Adldap();
        $provider = new Provider(config('adldap.proto'));
        $ad->addProvider('proto', $provider);
        $ad->connect('proto');

        $ldapuser = $provider->make()->user();
        $ldapuser->cn = "user-" . $user->id;
        $ldapuser->description = $user->id;
        $ldapuser->save();

    }

    /**
     * Static helper function that will dispatch a password reset e-mail for a user.
     *
     * @param User $user The user to submit the e-mail for.
     */
    public static function dispatchPasswordEmailFor(User $user)
    {

        $reset = PasswordReset::create([
            'email' => $user->email,
            'token' => str_random(128),
            'valid_to' => strtotime('+1 hour')
        ]);

        $name = $user->name;
        $email = $user->email;

        Mail::queueOn('high', 'emails.password', ['token' => $reset->token, 'name' => $user->calling_name], function ($message) use ($name, $email) {
            $message
                ->to($email, $name)
                ->from('webmaster@' . config('proto.emaildomain'), 'Have You Tried Turning It Off And On Again committee')
                ->subject('Your password reset request for S.A. Proto.');
        });

    }

    /**
     * Static helper function to handle a SAML request.
     * The function expects an authed user for which to complete the SAML request.
     * This function assumes the user has already been authenticated one way or another.
     *
     * @param $user The (currently logged in) user to complete the SAML request for.
     * @param $saml The SAML data (deflated and encoded).
     * @return \Illuminate\Http\RedirectResponse
     */
    private static function handleSAMLRequest($user, $saml)
    {
        if (!$user->member) {
            Session::flash('flash_message', 'Only members can use the Proto SSO. You only have a user account.');
            return Redirect::route('becomeamember');
        }

        // SAML is transmitted base64 encoded and GZip deflated.
        $xml = gzinflate(base64_decode($saml));

        // LightSaml Magic. Taken from https://imbringingsyntaxback.com/implementing-a-saml-idp-with-laravel/
        $deserializationContext = new \LightSaml\Model\Context\DeserializationContext();
        $deserializationContext->getDocument()->loadXML($xml);

        $authnRequest = new \LightSaml\Model\Protocol\AuthnRequest();
        $authnRequest->deserialize($deserializationContext->getDocument()->firstChild, $deserializationContext);

        if (!array_key_exists(base64_encode($authnRequest->getAssertionConsumerServiceURL()), config('saml-idp.sp'))) {
            Session::flash('flash_message', 'You are using an unknown Service Provider. Please contact the System Administrators to get your Service Provider whitelisted for Proto SSO.');
            return Redirect::route('login::show');
        }

        $response = AuthController::buildSAMLResponse($user, $authnRequest);

        $bindingFactory = new \LightSaml\Binding\BindingFactory();
        $postBinding = $bindingFactory->create(\LightSaml\SamlConstants::BINDING_SAML2_HTTP_POST);
        $messageContext = new \LightSaml\Context\Profile\MessageContext();
        $messageContext->setMessage($response)->asResponse();

        $httpResponse = $postBinding->send($messageContext);

        return view('auth.saml.samlpostbind', ['response' => $httpResponse->getData()["SAMLResponse"], 'destination' => $httpResponse->getDestination()]);
    }

    /**
     * Another static helper function to build a SAML response based on a user and a request.
     *
     * @param $user The user to generate the SAML response for.
     * @param $authnRequest The request to generate a SAML response for.
     * @return \LightSaml\Model\Protocol\Response A LightSAML response.
     */
    private static function buildSAMLResponse($user, $authnRequest)
    {

        // LightSaml Magic. Taken from https://imbringingsyntaxback.com/implementing-a-saml-idp-with-laravel/
        $audience = config('saml-idp.sp')[base64_encode($authnRequest->getAssertionConsumerServiceURL())]['audience'];
        $destination = $authnRequest->getAssertionConsumerServiceURL();
        $issuer = config('saml-idp.idp.issuer');

        $certificate = \LightSaml\Credential\X509Certificate::fromFile(base_path() . config('saml-idp.idp.cert'));
        $privateKey = \LightSaml\Credential\KeyHelper::createPrivateKey(base_path() . config('saml-idp.idp.key'), '', true);

        $response = new \LightSaml\Model\Protocol\Response();
        $response
            ->addAssertion($assertion = new \LightSaml\Model\Assertion\Assertion())
            ->setID(\LightSaml\Helper::generateID())
            ->setIssueInstant(new \DateTime())
            ->setDestination($destination)
            ->setIssuer(new \LightSaml\Model\Assertion\Issuer($issuer))
            ->setStatus(new \LightSaml\Model\Protocol\Status(new \LightSaml\Model\Protocol\StatusCode('urn:oasis:names:tc:SAML:2.0:status:Success')))
            ->setSignature(new \LightSaml\Model\XmlDSig\SignatureWriter($certificate, $privateKey));

        $email = $user->email;

        $assertion
            ->setId(\LightSaml\Helper::generateID())
            ->setIssueInstant(new \DateTime())
            ->setIssuer(new \LightSaml\Model\Assertion\Issuer($issuer))
            ->setSubject(
                (new \LightSaml\Model\Assertion\Subject())
                    ->setNameID(new \LightSaml\Model\Assertion\NameID(
                        $email,
                        \LightSaml\SamlConstants::NAME_ID_FORMAT_EMAIL
                    ))
                    ->addSubjectConfirmation(
                        (new \LightSaml\Model\Assertion\SubjectConfirmation())
                            ->setMethod(\LightSaml\SamlConstants::CONFIRMATION_METHOD_BEARER)
                            ->setSubjectConfirmationData(
                                (new \LightSaml\Model\Assertion\SubjectConfirmationData())
                                    ->setInResponseTo($authnRequest->getId())
                                    ->setNotOnOrAfter(new \DateTime('+1 MINUTE'))
                                    ->setRecipient($authnRequest->getAssertionConsumerServiceURL())
                            )
                    )
            )
            ->setConditions(
                (new \LightSaml\Model\Assertion\Conditions())
                    ->setNotBefore(new \DateTime())
                    ->setNotOnOrAfter(new \DateTime('+1 MINUTE'))
                    ->addItem(
                        new \LightSaml\Model\Assertion\AudienceRestriction($audience)
                    )
            )
            ->addItem(
                (new \LightSaml\Model\Assertion\AttributeStatement())
                    ->addAttribute(new \LightSaml\Model\Assertion\Attribute(
                        'urn:mace:dir:attribute-def:mail',
                        $email
                    ))
                    ->addAttribute(new \LightSaml\Model\Assertion\Attribute(
                        'urn:mace:dir:attribute-def:displayName',
                        $user->name
                    ))
                    ->addAttribute(new \LightSaml\Model\Assertion\Attribute(
                        'urn:mace:dir:attribute-def:cn',
                        $user->name
                    ))
                    ->addAttribute(new \LightSaml\Model\Assertion\Attribute(
                        'urn:mace:dir:attribute-def:givenName',
                        $user->given_name
                    ))
                    ->addAttribute(new \LightSaml\Model\Assertion\Attribute(
                        'urn:mace:dir:attribute-def:uid',
                        $user->member->proto_username
                    ))
            )
            ->addItem(
                (new \LightSaml\Model\Assertion\AuthnStatement())
                    ->setAuthnInstant(new \DateTime('-10 MINUTE'))
                    ->setSessionIndex('_some_session_index')
                    ->setAuthnContext(
                        (new \LightSaml\Model\Assertion\AuthnContext())
                            ->setAuthnContextClassRef(\LightSaml\SamlConstants::AUTHN_CONTEXT_PASSWORD_PROTECTED_TRANSPORT)
                    )
            );

        return $response;

    }
}
