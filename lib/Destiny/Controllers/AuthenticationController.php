<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Transactional;
use Destiny\Google\GoogleAuthHandler;
use Destiny\Common\ViewModel;
use Destiny\Twitter\TwitterAuthHandler;
use Destiny\Twitch\TwitchAuthHandler;
use Destiny\Api\ApiAuthHandler;
use Destiny\Common\HttpEntity;
use Destiny\Common\Utils\Http;

/**
 * @Controller
 */
class AuthenticationController {
	
	/**
	 * @Route ("/auth/api")
	 * @Transactional
	 *
	 * @param array $params   
	 * @param ViewModel $model         	
	 * @throws Exception
	 */
	public function authApi(array $params, ViewModel $model) {
		try {
			$authHandler = new ApiAuthHandler ();
			return $authHandler->execute ( $params, $model );
		} catch ( \Exception $e ) {
			$response = new HttpEntity ( Http::STATUS_ERROR, $e->getMessage () );
			return $response;
		}
	}
	
	/**
	 * @Route ("/auth/twitch")
	 * @Transactional
	 *
	 * @param array $params  
	 * @param ViewModel $model          	
	 * @throws Exception
	 */
	public function authTwitch(array $params, ViewModel $model) {
		try {
			$authHandler = new TwitchAuthHandler ();
			return $authHandler->execute ( $params, $model );
		} catch ( \Exception $e ) {
			$model->title = 'Login error';
			$model->error = $e;
			return 'login';
		}
	}
	
	/**
	 * @Route ("/auth/twitter")
	 * @Transactional
	 *
	 * @param array $params  	
	 * @param ViewModel $model           	
	 * @throws Exception
	 */
	public function authTwitter(array $params, ViewModel $model) {
		try {
			$authHandler = new TwitterAuthHandler ();
			return $authHandler->execute ( $params, $model );
		} catch ( \Exception $e ) {
			$model->title = 'Login error';
			$model->error = $e;
			return 'login';
		}
	}
	
	/**
	 * @Route ("/auth/google")
	 * @Transactional
	 *
	 * @param array $params        	
	 * @param ViewModel $model        	
	 * @throws Exception
	 */
	public function authGoogle(array $params, ViewModel $model) {
		try {
			$authHandler = new GoogleAuthHandler ();
			return $authHandler->execute ( $params, $model );
		} catch ( \Exception $e ) {
			$model->title = 'Login error';
			$model->error = $e;
			return 'login';
		}
	}
}