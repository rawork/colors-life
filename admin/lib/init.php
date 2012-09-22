<?php
	
	function exception_handler($exception) {
		echo "<div style=\"padding:15px;color:#990000;font-size:14px;\">Неперехватываемое исключение: " , $exception->getMessage(), "</div>\n";
	}

	set_exception_handler('exception_handler');

	ob_start();
	$se_mask = "/(Yandex|Googlebot|StackRambler|Yahoo Slurp|WebAlta|msnbot)/";
	if (preg_match($se_mask,$_SERVER['HTTP_USER_AGENT']) > 0) {
		if (!empty($_GET[session_name()])) {
			header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
			exit();
		}
	} else {
		session_start();
	}
	$LIB_VERSION = '5.0.1';
	$LIB_DATE = '2012.09.10';
	
    function inc_lib($v) {
    global $LIB_DIR;
        if (file_exists($LIB_DIR.'/'.$v)) {
			include_once($LIB_DIR.'/'.$v);
			return true;
		} else {
			return false;
		}
    }
    function inc_prj($v) {
    global $PRJ_DIR;
		if (file_exists($PRJ_DIR.$v)) {
			include_once($PRJ_DIR.$v);
	        return true;
		} else {
			return false;
		}
    }
    function inc_u($v) {
        return inc_lib('components/'.ucfirst($v).'Unit.php');
    }
	
	// библиотека общих функций
	inc_lib('CUtils.php');
	
	
	// ID запрашиваемой страницы
	$GLOBALS['cur_page_id'] = preg_replace('/(\/|-|\.|:|\?|[|])/', '_', str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
	
	// Включаем кеш
//	inc_lib('tools/Cache.php');
//	$options = array(
//	    'cacheDir' => $PRJ_DIR.'/admin/lib/cache/',
//	    'lifeTime' => 24*60*14,
//	    'pearErrorMode' => CACHE_ERROR_DIE
//	);
//	$GLOBALS['cache'] = new Cache($options);
//	if ($data = $GLOBALS['cache']->get($GLOBALS['cur_page_id'])){
//		//echo $data;
//		//exit();
//	}
	
	// Соединение с базой и выполнение запросов
	$dbclass = strtolower(!empty($GLOBALS['DB_TYPE']) ? $GLOBALS['DB_TYPE'] : '').'Connector';
	if (!file_exists($GLOBALS['LIB_DIR'].'/db/DBConnector/'.$dbclass.'.php')) {
		throw new Exception('DB connection type error (DB_TYPE). Possible value: mysql,mysqli');
	}
	inc_lib('db/DBConnector/'.$dbclass.'.php');
	$db = new $dbclass($GLOBALS['DB_HOST'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS'], $GLOBALS['DB_BASE']);
	
	// инициализация переменных
	$db->execQuery('GLOBAL_VARS', 'SELECT * FROM config_variables');
	while ($var = $db->getNextArray('GLOBAL_VARS')) {
		$$var['name'] = $var['value'];
	}
	
	//  НАДо ОТКАЗАТЬСЯ ОТ СМАРТИ
	inc_lib('tools/smarty/Smarty.class.php');
	$GLOBALS['smarty'] = new Smarty;
	$GLOBALS['smarty']->template_dir = $PRJ_DIR.'/templates/';
	$GLOBALS['smarty']->compile_dir = $PRJ_DIR.'/admin/lib/templates_c/';
	$GLOBALS['smarty']->compile_check = true;
	$GLOBALS['smarty']->debugging = false;
	$GLOBALS['smarty']->assign('prj_name', $PRJ_NAME);
	$GLOBALS['smarty']->assign('prj_zone', $PRJ_ZONE);
	$GLOBALS['smarty']->assign('prj_dir', $PRJ_DIR);
	$GLOBALS['smarty']->assign('prj_ref', $PRJ_REF);
	$GLOBALS['smarty']->assign('lib_dir', $LIB_DIR);
	$GLOBALS['smarty']->assign('lib_ref', $LIB_REF);
	$GLOBALS['smarty']->assign('theme_dir', $THEME_DIR);
	$GLOBALS['smarty']->assign('theme_ref', $THEME_REF);
	
	inc_lib('AdminInterface/AdminProtect.php');
	inc_lib('db/Table.php');
	inc_lib('db/RTTI.php');
	
	$GLOBALS["rtti"] = new RTTI();
	$GLOBALS["rtti"]->register('connection', $db);
	
	if ($_SERVER['SCRIPT_NAME'] != '/restore.php') {
    	if (file_exists($PRJ_DIR.'/restore.php')) {
			throw new Exception('Удалите файл restore.php в корне сайта');
		}
		
		// Включаем парсер URL 
		inc_lib('Router.php');
		$router = new Router();
		$GLOBALS['urlprops'] = $router->getURLProps();
		
		// Инициализация текущего языка
		if (!isset($_SESSION['lang']))
			$_SESSION['lang'] = CUtils::_postVar('lang', false, 'ru');
		if (CUtils::_postVar('lang') && $_SESSION['lang'] != CUtils::_postVar('lang')) {
			$_SESSION['lang'] = CUtils::_postVar('lang');
			header('location: '.$GLOBALS['urlprops']['uri'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''));
		}
		
		$GLOBALS['smarty']->assign('slang', $GLOBALS['urlprops']['lang']);
		$GLOBALS['smarty']->assign('urlprops', $GLOBALS['urlprops']);
    	
		if (!stristr($_SERVER['REQUEST_URI'], '/admin')) {
			inc_u('tree');
			$GLOBALS['utree'] = new TreeUnit($GLOBALS['urlprops']);
			inc_u('auth');
			$GLOBALS['uauth'] = new AuthUnit($GLOBALS['urlprops']);
			if (isset($GLOBALS['urlprops']['node']['id'])) {
				$GLOBALS['smarty']->assign('dirs', $GLOBALS['utree']->tables['tree']->getPrev($GLOBALS['urlprops']['node']['id']));
			} 
			if (isset($GLOBALS['urlprops']['params'][0])) {
				$GLOBALS['smarty']->assign('cats_tree', $GLOBALS['rtti']->getPrev('catalog_categories', $GLOBALS['urlprops']['params'][0]));
			}
			$GLOBALS['smarty']->assign('mail_to', $ADMIN_EMAIL);
			$GLOBALS['smarty']->assign('utree', $GLOBALS['utree']);
			$GLOBALS['smarty']->assign('uauth', $GLOBALS['uauth']);
		}	
    }
