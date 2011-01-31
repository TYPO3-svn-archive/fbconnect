<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Nils Blattner <nb@cabag.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extmgm::extPath('fbconnect', 'lib/facebook_graph.php'));

/**
 * Singleton class that allows access to the facebook api.
 *
 * @author	Nils Blattner <nb@cabag.ch>
 */
class tx_fbconnect_api implements t3lib_singleton {
	/**
	 * @var Facebook Facebook API
	 */
	protected $fbApi = null;
	
	/**
	 * @var array The extension configuration
	 */
	protected $extConf;
	
	/**
	 * @var string The extension prefix.
	 */
	protected $prefix = 'tx_fbconnect';
	
	/**
	 * @var tslib_cObj A cObject to render typoscript.
	 */
	protected $cObject = null;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['fbconnect']);
		
		$this->cObject = t3lib_div::makeInstance('tslib_cObj');
		
		$appKey = $this->extConf['appKey'];
		$appId = $this->extConf['appId'];
		$secret = $this->extConf['secret'];
		$useCookie = (bool)$this->extConf['useCookie'];
		$cookieDomain = $this->extConf['cookieDomain'];
		
		$fb_conf = array(
			'appId' => $appId,
			'secret' => $secret,
			'cookie' => $useCookie
		);
		
		if ($useCookie && !empty($cookieDomain)) {
			$fb_conf['domain'] = $cookieDomain;
		}
		
		$this->fbApi = new Facebook($fb_conf);
		
		$this->setHeaderJavaScript();
	}
	
	/**
	 * Writes the javascript needed for Facebook into the header.
	 *
	 * @return void
	 */
	private function setHeaderJavaScript() {
		$javascript = '			<script src="http://connect.facebook.net/en_US/all.js"></script>
			<script>
			/*<![CDATA[*/
			<!--
		
			  document.observe(\'dom:loaded\', function (e) {
				FB.init({appId: \'' . $this->getAppId() . '\', status: true,
					   cookie: true, xfbml: true});
				FB.Event.subscribe(\'auth.login\', function(response) {
					window.location.reload();
					/* $$(\'body\').each(function (n) {
						n.insert({
							bottom: \'<div style="display:none;"><form id="hiddenFBConnect" method="POST" action="\' + window.location.href + \'"><input type="hidden" name="' . $this->prefix . '[connect]" value="1" /></form></div>\'
						});
					});
					$(\'hiddenFBConnect\').submit(); */
				});
			  });
			// -->
			/*]]>*/
			</script>';
		
			if (!empty($this->extConf['headerJS'])) {
				$javascript = $this->extConf['headerJS'];
			}
		
		// facebook connect javascript
		// on click it sends a post to get connected
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefix] .= $javascript;
	}
	
	/**
	 * Returns the connected facebook user-id.
	 *
	 * @return int The connected facebook user-id.
	 */
	public function getUserId() {
		return intval($this->fbApi->getUser());
	}
	
	/**
	 * Returns the userdata of the connected facebook user.
	 *
	 * @return int The userdata.
	 */
	public function getUserData() {
		return $this->api('/me');
	}
	
	/**
	 * Returns the application key.
	 *
	 * @return string The application key.
	 */
	public function getAppKey() {
		return $this->extConf['appKey'];
	}
	
	/**
	 * Returns the application id.
	 *
	 * @return string The application id.
	 */
	public function getAppId() {
		return $this->extConf['appId'];
	}
	
	/**
	 * Allows access to the api functions.
	 *
	 * Polymorphic parameters
	 * @return mixed The result of the api functions.
	 */
	public function api() {
		$args = func_get_args();
		try {
			return call_user_func_array(array($this->fbApi, 'api'), $args);
		} catch (Exception $e) {
			return array();
		}
	}
	
	/**
	 * Creates an array, ready to be inserted into fe_users.
	 * 
	 * @param array $conf TypoScript array mapping facebook user values to different fields. Each field has stdWrap and the fields are the facebook user values.
	 * @return array Mapped array.
	 */
	public function getPreparedUserFromFacebook($conf) {
		$newUser = array();
		$data = $this->getUserData();
		$now = new DateTime();
		
		foreach ($data as $key => &$value) {
			if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', trim($value))) {
				$time = strtotime($value);
				//print_r(array($value, $time, $now->getOffset()));
				$value = $time === FALSE ? $value : ($time + $now->getOffset());
			}
		}
		
		$this->cObject->data = $data;
		
		foreach ($conf as $tKey => &$value) {
			if (substr($tKey, -1) == '.') {
				// typoscript sub array (username {...})
				$key = substr($tKey, 0, -1);
				$type = 'TEXT';
				
				if (isset($conf[$key]) && !empty($conf[$key])) {
					$type = $conf[$key];
				}
				
				$newUser[$key] = $this->cObject->cObjGetSingle($type, $conf[$tKey]);
			} else {
				// typoscript cObject type (username = TEXT)
				if (empty($conf[$tKey . '.'])) {
					// theres only the type (username = *)
					// -> assume the type is supposed to be a constant
					$newUser[$tKey] = $conf[$tKey];
				}
			}
		}
		
		return $newUser;
	}
	
	/**
	 * Inject new config into the facebook api.
	 *
	 * @param string $appId The application ID.
	 * @param string $secret The application secret.
	 * @param boolean $cookie Whether or not facebook connect should set a cookie.
	 * @param string $domain The cookie domain.
	 * @return void
	 */
	public function injectConfig($appId, $secret, $cookie = null, $domain = null) {
		$this->fbApi->setAppId($appId);
		$this->fbApi->setApiSecret($secret);
		if ($cookie !== null) {
			$this->fbApi->setCookieSupport($cookie);
		}
		if ($domain !== null) {
			$this->fbApi->setBaseDomain($domain);
		}
	}
}


?>
