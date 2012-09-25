<?php
	
	require_once 'config/config.php';

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
	
	function exception_handler($exception) 
	{
		echo "<div style=\"padding:15px;color:#990000;font-size:16px;\">Ошибка: " , $exception->getMessage(), '<br><br>' , $exception->getTraceasString(), "</div>\n";
	}
	
	function autoloader($className)
	{
		if ($className == 'Smarty') {
			global $PRJ_DIR;
			require_once($PRJ_DIR.'/admin/lib/tools/smarty/Smarty.class.php');
		} else {
			global $PRJ_DIR;
			$basePath = $PRJ_DIR.'/admin/lib/';
			$className = ltrim($className, '\\');
			$fileName  = '';
			$namespace = '';
			if ($lastNsPos = strripos($className, '\\')) {
				$namespace = substr($className, 0, $lastNsPos);
				$className = substr($className, $lastNsPos + 1);
				$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
			}
			$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

			require $basePath.$fileName;
		}	
	}
	
	set_exception_handler('exception_handler');
	spl_autoload_register('autoloader');

	// ID запрашиваемой страницы
	$GLOBALS['cur_page_id'] = preg_replace('/(\/|-|\.|:|\?|[|])/', '_', str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
	
	// Включаем кеш
//	$options = array(
//	    'cacheDir' => $PRJ_DIR.'/app/cache/',
//	    'lifeTime' => 24*60*14,
//	    'pearErrorMode' => CACHE_ERROR_DIE
//	);
//	$GLOBALS['cache'] = new \Common\Cache($options);
//	if ($data = $GLOBALS['cache']->get($GLOBALS['cur_page_id'])){
//		//echo $data;
//		//exit();
//	}
	
	// Соединение с базой и выполнение запросов
	try {
		$className = '\\DB\\Connector\\'.ucfirst($GLOBALS['DB_TYPE']).'Connector';
		$connection = new $className($GLOBALS['DB_HOST'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS'], $GLOBALS['DB_BASE']);
	} catch (\Exception $e) {
		throw new \Exception('DB connection type error (DB_TYPE). Possible value: mysql,mysqli. Check DB connection params');
	}
	// инициализация переменных
	$connection->execQuery('GLOBAL_VARS', 'SELECT * FROM config_variables');
	while ($var = $connection->getNextArray('GLOBAL_VARS')) {
		$$var['name'] = $var['value'];
	}

	$container = new Common\Container();
	$container->register('util', new Common\Util());
	$container->register('connection', $connection);
	
	$container->register('smarty', new Smarty());
	$container->get('smarty')->template_dir = $PRJ_DIR.'/app/Resources/views/';
	$container->get('smarty')->compile_dir = $PRJ_DIR.'/app/cache/smarty/';
	$container->get('smarty')->compile_check = true;
	$container->get('smarty')->debugging = false;
	$container->get('smarty')->assign('prj_name', $PRJ_NAME);
	$container->get('smarty')->assign('prj_zone', $PRJ_ZONE);
	$container->get('smarty')->assign('prj_dir', $PRJ_DIR);
	$container->get('smarty')->assign('prj_ref', $PRJ_REF);
	$container->get('smarty')->assign('lib_dir', $LIB_DIR);
	$container->get('smarty')->assign('lib_ref', $LIB_REF);
	$container->get('smarty')->assign('theme_dir', $THEME_DIR);
	$container->get('smarty')->assign('theme_ref', $THEME_REF);
	
	
	// Включаем Роутер запросов к сайту 
	$container->register('router', new Common\Router());
	
	$security = new \Security\SecurityHandler();
	if (!$security->isAuthenticated() && $security->isSecuredArea()) {
		$controller = new \Security\SecurityController();
		echo $controller->loginAction();
		exit;
	} elseif (preg_match('/^\/admin\/(logout|forgot|password)/', $_SERVER['REQUEST_URI'], $matches)) {
		$controller = new \Security\SecurityController();
		$methodName = $matches[1].'Action';
		echo $controller->$methodName();
		exit;
	}
	$container->initialize();
	
	$container->get('router')->setLanguage();
	$container->get('router')->setParams();
	
	if ($_SERVER['SCRIPT_NAME'] != '/restore.php' && file_exists($PRJ_DIR.'/restore.php')) {
		throw new \Exception('Удалите файл restore.php в корне сайта');
	}
	
	if ($container->get('router')->isPublic($container->get('router')->getPath())) {

		$container->register('tree', new \Controller\TreeController());
		$container->register('auth', new \Controller\AuthController());
		$container->register('meta', new \Model\MetaManager());

		if ($container->get('router')->hasParam('nodeId')) {
			$container->get('smarty')->assign('dirs', $container->get('tree')->tables['tree']->getPrev($container->get('router')->getParam('nodeId')));
		}
		$params = $container->get('router')->getParam('params');
		if ($container->get('router')->getParam('params')) {
			$container->get('smarty')->assign('cats_tree', $container->getPrev('catalog_categories', $params[0]));
		}
		$container->get('smarty')->assign('mail_to', $ADMIN_EMAIL);
		$container->get('smarty')->assign('tree', $container->get('tree'));
		$container->get('smarty')->assign('auth', $container->get('auth'));
		
	}	
