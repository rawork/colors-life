<?php
	
	class CLog {

		public static function write($sMessage) {
			global $db;
			$sIPAddress	= empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
			$sUserName		= CUtils::_sessionVar('user');
			$sQuery = "
				INSERT INTO
					system_log(credate, ip_address, user_name, description) 
				VALUES
					(NOW(), '$sIPAddress','$sUserName', '$sMessage')
			";
			$db->execQuery('log', $sQuery);
		}

	}