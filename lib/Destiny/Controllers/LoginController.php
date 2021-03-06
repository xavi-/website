<?php
namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\Config;
use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\OAuthClient;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserRole;
use Destiny\Common\Authentication\AuthenticationService;

/**
 * @Controller
 */
class LoginController {

	/**
	 * @Route ("/logout")
	 *
	 * @param array $params
	 */
	public function logout(array $params) {
		AuthenticationService::instance ()->logout ();
		return 'redirect: /';
	}

	/**
	 * @Route ("/login")
	 * @HttpMethod ({"GET"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function login(array $params, ViewModel $model) {
		Session::set ( 'accountMerge' );
		$model->title = 'Login';
		$model->follow = (isset($params['follow'])) ? $params['follow'] : '';
		return 'login';
	}

	/**
	 * @Route ("/login")
	 * @HttpMethod ({"POST"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function loginPost(array $params, ViewModel $model) {
		$userService = UserService::instance ();
		
		$authProvider = (isset ( $params ['authProvider'] ) && ! empty ( $params ['authProvider'] )) ? $params ['authProvider'] : '';
		$rememberme = (isset ( $params ['rememberme'] ) && ! empty ( $params ['rememberme'] )) ? true : false;
		
		if (empty ( $authProvider )) {
			$model->title = 'Login error';
			$model->rememberme = $rememberme;
			$model->error = new Exception ( 'Please select a authentication provider' );
			return 'login';
		}
		
		// This is and rememberme are the only places that will create a new session cookie for a user
		Session::start ( Session::START_NOCOOKIE );
		
		if ($rememberme) {
			Session::set ( 'rememberme', 1 );
		}
		
		// @TODO this logic feels dirty and out of place
		// If this user is already logged in and the account merge param is set - probably trying to merge
		if (Session::hasRole ( UserRole::USER ) && isset ( $params ['accountMerge'] ) && $params ['accountMerge'] === '1') {
			// check if the auth provider you are trying to login with is not the same as the current
			$currentAuthProvider = Session::getCredentials ()->getAuthProvider ();
			if (strcasecmp ( $currentAuthProvider, $authProvider ) === 0) {
				throw new Exception ( 'You are already logged in and authenticated using this provider.');
			}
			// Set a session var that is picked up in the AuthenticationService
			// in the GET method, this variable is unset
			Session::set ( 'accountMerge', '1' );
		}
		
		$callback = sprintf ( Config::$a ['oauth'] ['callback'], strtolower ( $authProvider ) );
		if (empty ( $callback )) {
			throw new Exception ( 'Invalid callback format' );
		}
		
		if (isset ( $params ['follow'] ) && ! empty ( $params ['follow'] )) {
			Session::set ( 'follow', $params ['follow'] );
		}
		
		switch (strtoupper ( $authProvider )) {
			case 'TWITCH' :
				$authClient = new oAuthClient ( Config::$a ['oauth'] ['providers'] ['twitch'] );
				$authClient->setHeaderTokenName ( 'OAuth' );
				return 'redirect: '. $authClient->getAuthorisationEndPoint ( 'https://api.twitch.tv/kraken/oauth2/authorize', $callback, 'user_read' );
			
			case 'GOOGLE' :
				$authClient = new OAuthClient ( Config::$a ['oauth'] ['providers'] ['google'] );
				$authClient->setHeaderTokenName ( 'Bearer' );
				return 'redirect: '. $authClient->getAuthorisationEndPoint ( 'https://accounts.google.com/o/oauth2/auth', $callback, 'openid+email', array ('state' => 'security_token=' . Session::getSessionId ()	) );
			
			case 'TWITTER' :
				$twitterOAuthConf = Config::$a ['oauth'] ['providers'] ['twitter'];
				$tmhOAuth = new \tmhOAuth ( array (
					'consumer_key' => $twitterOAuthConf ['clientId'],
					'consumer_secret' => $twitterOAuthConf ['clientSecret'],
					'token' => $twitterOAuthConf ['token'],
					'secret' => $twitterOAuthConf ['secret'],
					'curl_connecttimeout' => Config::$a ['curl'] ['connecttimeout'],
					'curl_timeout' => Config::$a ['curl'] ['timeout'],
					'curl_ssl_verifypeer' => Config::$a ['curl'] ['verifypeer'] 
				) );
				$code = $tmhOAuth->apponly_request ( array (
					'without_bearer' => true,
					'method' => 'POST',
					'url' => $tmhOAuth->url ( 'oauth/request_token', '' ),
					'params' => array (
						'oauth_callback' => $callback 
					) 
				) );
				if ($code != 200) {
					throw new Exception ( 'There was an error communicating with Twitter.' );
				}
				$response = $tmhOAuth->extract_params ( $tmhOAuth->response ['response'] );
				if ($response ['oauth_callback_confirmed'] !== 'true') {
					throw new Exception ( 'The callback was not confirmed by Twitter so we cannot continue.' );
				}
				Session::set ( 'oauth', $response );
				return 'redirect: ' . $tmhOAuth->url ( 'oauth/authorize', '' ) . "?oauth_token={$response['oauth_token']}";
			
			default :
				$model->title = 'Login error';
				$model->rememberme = $rememberme;
				$model->error = new Exception ( 'Authentication type not supported' );
				return 'login';
		}
	}

}
