<?php
	/* E-mail for adminProtect */
	define('__MAIN_EMAIL', 'rawork@yandex.ru');
	
	/* Path default settings */
	$PATH_MAINPAGE_TITLE = array('ru' => 'На главную', 'en' => 'Mainpage');
	define('__PATH_DELIMETER', '/');
	define('__PATH_MAINPAGE_VISIBLE', true);
	define('__PATH_VISIBLE', true);
		
	/* CMS core settings */
	define('__PROCESSOR_VISIBLE', false);
		
	/* Template Versions settings */
	define('__VERSION_QUANTITY', 10);
	
	/* CAPTCHA Settings */	
	define('__CAPTCHA_HASH', 'FWK');
	
	/* Developer user */	
	define('_DEV_USER', 'dev');
	define('_DEV_PASS', '27ee25711f69fa16bfe538f089f9fd95');
	
	/* Develope branch */
	define('__SHOW_NEW_DEV', false);

	define('TEMPLATE_EXTENSION',			'.tpl');
	define('NAVIGATION_TEMPLATE_LINK_MASK', '###');
	define('URL_PARAM_DELIMETER', '.');
?>