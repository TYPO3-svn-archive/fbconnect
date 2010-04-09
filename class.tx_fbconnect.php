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
 * Utility functions for 'fbconnect' extension.
 *
 * @author	Søren Thing Andersen, Net Image A/S <sta@netimage.dk>
 * @package	TYPO3
 * @subpackage	tx_fbconnect
 */
class tx_fbconnect {
	
	/**
	 * Tries to find a Frontend user linked withe the specified Facebook user ID.
	 * if found, the Frontend user is logged in.
	 *
	 * @param integer $pid			Storage page ID for FE users.
	 * @param string $fb_user_uid	Facebook user ID
	 * @return boolean				True if a user was found and logged in, false otherwise.
	 */
	public static function loginAsLinkedFeUser($pid, $fb_user_uid) {
		$db = $GLOBALS["TYPO3_DB"]; /* @var $db t3lib_DB */
		$pid			= intval($pid);
		$fb_user_uid	= $db->quoteStr($fb_user_uid, 'fe_users');
		$where			= "pid = $pid AND tx_fbconnect_user = '$fb_user_uid'";
		$result			= $db->exec_SELECTquery('*', 'fe_users', $where, '', 'uid', 1);
		if ($result && ($aUser = $db->sql_fetch_assoc($result))) {
			$fe_user = $GLOBALS['TSFE']->fe_user; /* @var $fe_user tslib_feUserAuth */
			unset($fe_user->user);
			$fe_user->createUserSession($aUser);
			$fe_user->loginSessionStarted = TRUE;
			$fe_user->user = $fe_user->fetchUserSession();
			$GLOBALS["TSFE"]->loginUser = 1;
			return true;
		}
		// No linked user found
		return false;
	} // End loginAsLinkedFeUser()

	/**
	 * Updates Frontend user with uid $fe_user_uid, setting tx_fbconnect_user to $fb_user_uid.
	 *
	 * @param integer $fe_user_uid	The Frontend user UID
	 * @param string $fb_user_uid	The Facebook user ID
	 * @return boolean				True on success
	 */
	public static function setFeUserFacebookId($fe_user_uid, $fb_user_uid) {
		$db = $GLOBALS["TYPO3_DB"]; /* @var $db t3lib_DB */
		$fe_user_uid	= intval($fe_user_uid);
		$where			= "uid = $fe_user_uid";
		$result = $db->exec_UPDATEquery('fe_users', $where, array('tx_fbconnect_user' => $fb_user_uid));
		return (boolean) $result;
	} // End setFeUserFacebookId()
}

?>