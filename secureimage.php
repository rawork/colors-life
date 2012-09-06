<?php

	include_once($_SERVER['DOCUMENT_ROOT'].'/admin/lib/tools/kcaptcha/kcaptcha.php');
	session_start();
	$captcha = new KCAPTCHA();
	$_SESSION['c_sec_code'] = md5($captcha->getKeyString().'FWK');
