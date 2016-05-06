<?php
/**
 * Outlook Connect strategy for Opauth
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright    Copyright © 2012 U-Zyn Chua (http://uzyn.com)
 * @link         http://opauth.org
 * @package      Opauth.OutlookStrategy
 * @license      MIT License
 */

/**
 * Outlook Connect strategy for Opauth
 *
 * @package			Opauth.Outlook
 */
class OutlookStrategy extends OpauthStrategy {
	/**
	 * Compulsory config keys, listed as unassociative arrays
	 * eg. array('app_id', 'app_secret');
	 */
	public $expects = array('client_id', 'client_secret');

	/**
	 * Optional config keys, without predefining any default values.
	 */
	public $optionals = array('redirect_uri', 'scope', 'state');

	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = array(
		'redirect_uri' => '{complete_url_to_strategy}oauth2callback',
	);

	/**
	 * Auth request
	 */
	public function request(){
		$url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';

		$params = array(
			'client_id' => $this->strategy['client_id'],
			'redirect_uri' => $this->strategy['redirect_uri'],
			'response_type' => 'code'
		);

		foreach ($this->optionals as $key) {
			if (!empty($this->strategy[$key])) $params[$key] = $this->strategy[$key];
		}

		// redirect to generated url
		$this->clientGet($url, $params);
	}

	/**
	 * Internal callback, after Outlook Connect's request
	 */
	public function oauth2callback(){
		$callbackTime = time();
		if (array_key_exists('code', $_GET) && !empty($_GET['code'])){
			$url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

			$params = array(
				'client_id' =>$this->strategy['client_id'],
				'client_secret' => $this->strategy['client_secret'],
				'redirect_uri'=> $this->strategy['redirect_uri'],
				'grant_type' => 'authorization_code',
				'code' => trim($_GET['code'])
			);
			if (!empty($this->strategy['state'])) $params['state'] = $this->strategy['state'];
			$response = $this->serverPost($url, $params, null, $headers);
			$results = json_decode($response);
			debug($results);

			if (!empty($results) && !empty($results->access_token)) {
				$me = $this->me($results->access_token);

				$this->auth = array(
					'uid' => $me['Id'],
					'info' => array(
						'email' => $me['EmailAddress'],
						'name' => $me['DisplayName'],
						'nickname' => $me['Alias'],
					),
					'credentials' => array(
						'token' => $results->access_token,
					),
					'raw' => $me
				);

				$this->callback();
			}
			else {
				$error = array(
					'code' => 'access_token_error',
					'message' => 'Failed when attempting to obtain access token',
					'raw' => array(
						'response' => $response,
						'headers' => $headers
					)
				);

				$this->errorCallback($error);
			}
		}
		else {
			$error = array(
				'code' => 'oauth2callback_error',
				'raw' => $_GET
			);

			$this->errorCallback($error);
		}
	}

	/**
	 * Queries Outlook Connect API for user info
	 *
	 * @param string $access_token
	 * @return array Parsed JSON results
	 */
	private function me($access_token) {


		$options['http']['header'] = "Authorization: Bearer ".$access_token;
		$options['http']['header'] .= "\r\nAccept: application/json";

		$me = $this->serverGet('https://outlook.office.com/api/beta/me', array('access_token' => $access_token), $options, $headers);

		if (!empty($me)) {
			return $this->recursiveGetObjectVars(json_decode($me));
		}
		else {
			$error = array(
				'code' => 'userinfo_error',
				'message' => 'Failed when attempting to query Outlook Connect API for user information',
				'raw' => array(
					'response' => $me,
					'headers' => $headers
				)
			);

			$this->errorCallback($error);
		}
	}
}
