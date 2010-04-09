<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Søren Thing Andersen, Net Image A/S <sta@netimage.dk>
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extmgm::extPath('fbconnect', 'class.tx_fbconnect.php'));
require_once(t3lib_extmgm::extPath('fbconnect', 'lib/facebook.php'));

/**
 * Plugin 'Facebook Connect' for the 'fbconnect' extension.
 *
 * @author	Søren Thing Andersen, Net Image A/S <sta@netimage.dk>
 * @package	TYPO3
 * @subpackage	tx_fbconnect
 */
class tx_fbconnect_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_fbconnect_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_fbconnect_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'fbconnect';	// The extension key.
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		global $TSFE; /* @var $TSFE tslib_fe */
		
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
	
		// Get main template
		$templateCode = $this->cObj->fileResource($this->conf['templateFile']);
		if ($templateCode == '') {
			return $this->pi_wrapInBaseClass("Template missing.");
		}
		
		// Any Frontend user logged in?
		$user = $TSFE->fe_user->user; // array or false
		
		// Do we have a connected Facebook user?
		$apiKey	= $this->conf['apiKey'];
		$secret	= $this->conf['secret'];
		$fb = new Facebook($apiKey, $secret);
		$fb_user = $fb->get_loggedin_user();
		
		// Now build our output in an array of strings
		$content = array();
		
		// We need different parts of the template:
		if ($fb_user) { // A connected Facebook user
			$profileTemplate = $this->cObj->getSubpart($templateCode, '###PROFILE###');
			$profileHtml = $this->cObj->substituteMarker($profileTemplate, '###UID###', $fb_user);
		}
		else { // No connected Facebook user
			$connectButtonTemplate = $this->cObj->getSubpart($templateCode, '###CONNECT_BUTTON###');
		}
		
		if ($user) { // A frontend user is logged in
			// Get linked Facebook user ID - possibly 0
			$linked_user_id = $user['tx_fbconnect_user'];

			// Update link status?
			if ($linked_user_id) { // The user is presently linked to a Facebook account.
				// Shall we unlink?
				if (($post_pi_vars = t3lib_div::_POST($this->prefixId))
						&& $post_pi_vars['unlink'] == 1) {	// Unlink request received.
					// Update the Frontend user record
					if (tx_fbconnect::setFeUserFacebookId($user['uid'], '')) {
						// Frontend user updated in database, now update memory values.
						$linked_user_id = $user['tx_fbconnect_user'] = ''; 
					}
				}
			}
			else { // The user is NOT linked to a Facebook account.				
				// Shall we link?
				if ($fb_user								// Connected Facebook user present
						&& ($post_pi_vars = t3lib_div::_POST($this->prefixId))
						&& $post_pi_vars['link'] == 1) {	// And link request received.
					// Update the Frontend user record
					if (tx_fbconnect::setFeUserFacebookId($user['uid'], $fb_user)) {
						// Frontend user updated in database, now update memory values.
						$linked_user_id = $user['tx_fbconnect_user'] = $fb_user; 
					}
				}
			}
			
			// Show options 
			if ($linked_user_id) { // The user is presently linked to a Facebook account.
				if ($fb_user) { // A connected Facebook user
					// Present option to unlink accounts
					// Choose the right template: Do the Facebook IDs match?
					$template_name = ($linked_user_id == $fb_user) ? 'LINKED_AND_CONNECTED' : 'LINKED_AND_CONNECTED_MISMATCH';
					$template = $this->cObj->getSubpart($templateCode, "###$template_name###");
					$markerArray = array(	'profile'	=> $profileHtml,
											'prefixId'	=> $this->prefixId,
											'action'	=> $this->pi_getPageLink($TSFE->id));
					$content[] = $this->cObj->substituteMarkerArray($template, $markerArray, '###|###', true);
				}
				else { // No connected Facebook user
					// Show info that the user is linked but not currently connected.
					$connectButtonHtml = $this->cObj->substituteMarker($connectButtonTemplate, '###ONLOGIN###', 'reload_page();');
					$LinkedNotConnectedTemplate = $this->cObj->getSubpart($templateCode, '###LINKED_NOT_CONNECTED###');
					$content[] = $this->cObj->substituteMarker($noLinkNotConnectedTemplate, '###CONNECT_BUTTON###', $connectButtonHtml);
					$markerArray = array(	'connect_button'	=> $connectButtonHtml,
											'prefixId'	=> $this->prefixId,
											'action'	=> $this->pi_getPageLink($TSFE->id));
					$content[] = $this->cObj->substituteMarkerArray($LinkedNotConnectedTemplate, $markerArray, '###|###', true);
				}
			}
			else { // The user is NOT linked to a Facebook account.				
				if ($fb_user) { // A connected Facebook user
					// Present option to link accounts
					$noLinkButConnectedTemplate = $this->cObj->getSubpart($templateCode, '###NO_LINK_BUT_CONNECTED###');
					$markerArray = array(	'profile'	=> $profileHtml,
											'prefixId'	=> $this->prefixId,
											'action'	=> $this->pi_getPageLink($TSFE->id));
					$content[] = $this->cObj->substituteMarkerArray($noLinkButConnectedTemplate, $markerArray, '###|###', true);
				}
				else { // No connected Facebook user
					// Show info that the user can connect.
					$connectButtonHtml = $this->cObj->substituteMarker($connectButtonTemplate, '###ONLOGIN###', 'reload_page();');
					$noLinkNotConnectedTemplate = $this->cObj->getSubpart($templateCode, '###NO_LINK_NOT_CONNECTED###');
					$content[] = $this->cObj->substituteMarker($noLinkNotConnectedTemplate, '###CONNECT_BUTTON###', $connectButtonHtml);
				}
			}
		}
		else { // No frontend user logged in
			if ($fb_user) { // A connected Facebook user
				// If the Facebook user was previously linked to a Frontend user, log in as that FE user
				if (tx_fbconnect::loginAsLinkedFeUser($conf['usersPid'], $fb_user)) {
					// A linked FE user is now logged in.
					// Reload this page
					$link = t3lib_div::locationHeaderUrl($this->pi_getPageLink($TSFE->id));
					header("Location: $link");
					exit();
				}
				else { // No linked FE user was found
					// Show info including profile.
					$autoLoginFailTemplate = $this->cObj->getSubpart($templateCode, '###AUTOLOGIN_FAIL###');
					$markerArray = array(	'profile'	=> $profileHtml);
					$content[] = $this->cObj->substituteMarkerArray($autoLoginFailTemplate, $markerArray, '###|###', true);
				}
			}
			else { // No connected Facebook user
				// Show the Facebook Connect button, callback to loginAsLinkedFeUser() on login
				$content[] = $this->cObj->substituteMarker($connectButtonTemplate, '###ONLOGIN###', 'reload_page();');
			}
		}
		
		// We allways need the Javascript initialization for Facebook
		$initTemplate = $this->cObj->getSubpart($templateCode, '###INIT###');
		$markerArray = array(	'apiKey'		=> htmlentities($apiKey, ENT_QUOTES, 'UTF-8'),
								'siteRelPath'	=> t3lib_extMgm::siteRelPath($this->extKey));
		$content[] = $this->cObj->substituteMarkerArray($initTemplate, $markerArray, '###|###', true);
		
		return $this->pi_wrapInBaseClass(implode('', $content));
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fbconnect/pi1/class.tx_fbconnect_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fbconnect/pi1/class.tx_fbconnect_pi1.php']);
}

?>