<?php
	
class CUtils {

	/*** cutting text */
	public static function cut_text($text, $len = 120) {
		if (strlen($text) > $len) {
			$text = substr(html_entity_decode($text, null, 'cp1251'), 0, $len);
			$text = substr($text, 0, strrpos($text, ' '));
			$text .= '...';
		}
		return $text;
	}

	/*** prepend zeroes */
	public static function prepend_zeroes($str, $length) {
		return str_pad($str, $length, '0', STR_PAD_LEFT);
	}

	/*** file size (by ref) */
	public static function getFileSize($ref, $precision = 2) {
		$ret = '';
		if (@file_exists($GLOBALS['PRJ_DIR'].$ref)) {
			$ret = CUtils::getSize(max(filesize($GLOBALS['PRJ_DIR'].$ref), 0), $precision);
		}
		return $ret;
	}

	/* file size formatting */
	public static function getSize($bytes, $precision = 2) {
		$units = array('б', 'кб', 'мб', 'гб', 'тб');
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . '&nbsp;' . $units[$pow];
	} 

	/*** Заливка одноименных файлов */
	public static function getNextFileName($name, $counter = 0) {
		preg_match('/([^\.]+).(.+)/', $name, $regs);
		$newName = $counter ? $regs[1].'_'.$counter.'.'.$regs[2] : $name;
		return @file_exists($GLOBALS['PRJ_DIR'].$newName) ? CUtils::getNextFileName($name, $counter + 1) : $newName;
	}

	/*** транслитерация строки (например, имя файла) */
	public static function translitStr($s) {
		// Сначала заменяем "односимвольные" фонемы.
		$s=strtr($s,"абвгдеёзийклмнопрстуфхъыэ_ ", "abvgdeeziyklmnoprstufh_iei_");
		$s=strtr($s,"АБВГДЕЁЗИЙКЛМНОПРСТУФХЪЫЭ_ ", "ABVGDEEZIYKLMNOPRSTUFH_IEI_");
		// Затем - "многосимвольные".
		$s=strtr($s, 
			array(
				"ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh", 
				"щ"=>"shch","ь"=>"", "ю"=>"yu", "я"=>"ya",
				"Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH", 
				"Щ"=>"SHCH","Ь"=>"", "Ю"=>"YU", "Я"=>"YA",
				"ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye"
			)
		);
		return $s;
	}

	public static function fdate($d, $format) {
		$dstr = date($format, mktime(substr($d,11,2),substr($d,14,2),substr($d,17,2),substr($d,5,2),substr($d,8,2),substr($d,0,4)));
		$lang = CUtils::_sessionVar('lang', false, 'ru');
		if ($lang != 'en') {
			$smonth = array(
				'ru' => array('Jan' => 'января', 'Feb' => 'февраля', 'Mar' => 'марта', 'Apr' => 'апреля', 'May' => 'мая', 'Jun' => 'июня', 
				'Jul' => 'июля', 'Aug' => 'августа', 'Sep' => 'сентября', 'Oct' => 'октября', 'Nov'  => 'ноября', 'Dec' => 'декабря'),
				'fr' => array(),
				'it' => array()
			);
			$month = array(
				'ru' => array('January' => 'января', 'February' => 'февраля', 'March' => 'марта', 'April' => 'апреля', 'May' => 'мая', 'June' => 'июня', 
				'July' => 'июля', 'August' => 'августа', 'September' => 'сентября', 'October' => 'октября', 'November'  => 'ноября', 'December' => 'декабря'),
				'fr' => array(),
				'it' => array()
			);
			$weekday = array(
				'ru' => array('Monday' => 'понедельник', 'Tuesday' => 'вторник', 'Wednesday' => 'среда', 'Thursday' => 'четверг',
				'Friday' => 'пятница', 'Saturday' => 'суббота', 'Sunday' => 'воскресенье'),
				'fr' => array(),
				'it' => array()
			);
			$sweekday = array(
				'ru' => array('Mon' => 'понедельник', 'Tue' => 'вторник', 'Wed' => 'среда', 'Thu' => 'четверг',
				'Fri' => 'пятница', 'Sat' => 'суббота', 'Sun' => 'воскресенье'),
				'fr' => array(),
				'it' => array()
			);
			$dstr = strtr($dstr, array_merge($smonth[$lang],$month[$lang],$sweekday[$lang],$weekday[$lang]));
		} 
		return $dstr;
	}

	/* Валидация email */
	public static function valid_email($email) {
		return preg_match('/^[a-z0-9]+([-_\.]?[a-z0-9])*@[a-z0-9]+([-_\.]?[a-z0-9])+\.[a-z]{2,4}$/i', $email);
	}

	/* get http content */
	public static function get_http_content($host, $url = '/') {
		$retry = 0;
		while (!($f = @fsockopen($host, 80)) && $retry++ < 10) {
			sleep(1);
		}
		if ($f) {
			$request = 'GET '.$url." HTTP/1.0\r\n".
				'Host: '.$host."\r\n".
				'Referer: http://'.$host."/\r\n". 
				"Accept: text/html\r\n". 
				"Accept-Language: ru\r\n". 
				"User-Agent: Mozilla/4.0 (compatible; MSIE 5.5; Windows NT 5.0)\r\n\r\n";
				if (fputs($f, $request)) {
				$ret = '';
				while (!feof($f)) {
					$ret .= fgets($f, 1024);
				}
				fclose($f);
				return $ret;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	// Сумма прописью - доработал - Alexander Selifonov
	// last updated: 15.01.2007
	// echo SumProp(2004.30, 'руб.', 'коп.');
	// SumProp(nnnn,'USD'|'RUR'|'EUR')-полный вывод со спряжением "долларов"-"центов"
	public static function sumProp($srcsumm,$val_rub='', $val_kop=''){
		$cifir= Array('од','дв','три','четыр','пят','шест','сем','восем','девят');
		$sotN = Array('сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот');
		$milion= Array('триллион','миллиард','миллион','тысяч');
		$anDan = Array('','','','сорок','','','','','девяносто');
		$scet=4;
		$cifR='';
		$cfR='';
		$oboR= Array();
		//==========================
		$splt = explode('.', $srcsumm);
		if(sizeof($splt)<2) $splt = explode(',', $srcsumm);
		$xx = $splt[0];
		$xx1 = (empty($splt[1])? '00': $splt[1]);
		$xx1 = str_pad($xx1, 2, '0', STR_PAD_RIGHT); // 2345.1 -> 10 копеек
		//  $xx1 = round(($srcsumm-floor($srcsumm))*100);
		if ($xx>999999999999999) { $cfR=$srcsumm; return $cfR; }
		while($xx/1000>0){
			$yy=floor($xx/1000);
			$delen= round(($xx/1000-$yy)*1000);

			$sot= floor($delen/100)*100;
			$des=(floor($delen-$sot)>9? floor(($delen-$sot)/10)*10:0);
			$ed= floor($delen-$sot)-floor(($delen-$sot)/10)*10;

			$forDes=($des/10==2?'а':'');
			$forEd= ($ed==1 ? 'ин': ($ed==2?'е':'') );
			if ( floor($yy/1000)>=1000 ) { // делаю "единицы" для тысяч, миллионов...
				$ffD=($ed>4?'ь': ($ed==1 || $scet<3? ($ed<2?'ин': ($scet==3?'на': ($scet<4? ($ed==2?'а':( $ed==4?'е':'')) :'на') ) ) : ($ed==2 || $ed==4?'е':'') ) );
			} else { // единицы для "единиц
				$ffD=($ed>4?'ь': ($ed==1 || $scet<3? ($scet<3 && $ed<2?'ин': ($scet==3?'на': ($scet<4? ($ed==2?'а':( $ed==4?'е':'')) :'ин') ) ) : ( $ed==4?'е':($ed==2?'а':'')) ) );
			}
			if($ed==2) $ffD = ($scet==3)?'е':'а'; // два рубля-миллиона-миллиарда, но две тысячи

			$forTys=($des/10==1? ($scet<3?'ов':'') : ($scet<3? ($ed==1?'': ($ed>1 && $ed<5?'а':'ов') ) : ($ed==1? 'а': ($ed>1 && $ed<5?'и':'') )) );
			$nnn = floor($sot/100)-1;
			$oprSot=(!empty($sotN[$nnn]) ? $sotN[$nnn]:'');
			$nnn = floor($des/10);
			$oprDes=(!empty($cifir[$nnn-1])? ($nnn==1?'': ($nnn==4 || $nnn==9? $anDan[$nnn-1]:($nnn==2 || $nnn==3?$cifir[$nnn-1].$forDes.'дцать':$cifir[$nnn-1].'ьдесят') ) ) :'');

			$oprEd=(!empty($cifir[$ed-1])? $cifir[$ed-1].(floor($des/10)==1?$forEd.'надцать' : $ffD ) : ($des==10?'десять':'') );
			$oprTys=(!empty($milion[$scet]) && $delen>0) ? $milion[$scet].$forTys : '';

			$cifR= (strlen($oprSot) ? ' '.$oprSot:'').
				(strlen($oprDes)>1 ? ' '.$oprDes:'').
				(strlen($oprEd)>1  ? ' '.$oprEd:'').
				(strlen($oprTys)>1 ? ' '.$oprTys:'');
			$oboR[]=$cifR;
			$xx=floor($xx/1000);
			$scet--;
			if (floor($xx)<1 ) break;
		}
		$oboR = array_reverse($oboR);
		for ($i=0; $i<sizeof($oboR); $i++){
			$probel = strlen($cfR)>0 ? ' ':'';
			$cfR .= (($oboR[$i]!='' && $cfR!='') ? $probel:'') . $oboR[$i];
		}
		if (strlen($cfR)<3) $cfR='ноль';

		$intsrc = $splt[0];
		$kopeiki = $xx1;
		$kop2 =str_pad($xx1, 2, '0', STR_PAD_RIGHT);

		$sum2 = str_pad($intsrc, 2, '0', STR_PAD_LEFT);
		$sum2 = substr($sum2, strlen($sum2)-2); // 676571-> '71'
		$sum21 = substr($sum2, strlen($sum2)-2,1); // 676571-> '7'
		$sum22 = substr($sum2, strlen($sum2)-1,1); // 676571-> '1'
		$kop1  = substr($kop2,0,1);
		$kop2  = substr($kop2,1,1);
		$ar234 = array('2','3','4'); // доллар-А, рубл-Я...
		// делаю спряжения у слова рубл-ей|я|ь / доллар-ов... / евро
		if($val_rub=='RUR') {
			$val1 = 'рубл';
			$val2 = 'копейка';
			if($sum22=='1' && $sum21!='1') $val1 .= 'ь'; // 01,21...91 рубль
			elseif(in_array($sum22, $ar234) && ($sum21!='1')) $val1 .= 'я';
			else $val1 .= 'ей';
			if(in_array($kop2, $ar234) && ($kop1!='1')) $val2 = 'копейки';
			elseif($kop2=='1' && $kop1!='1') $val2 = 'копейка'; // 01,21...91 копейка
			else $val2 = 'копеек';
			$cfR .= ' '.$val1.' '.$kopeiki.' '.$val2;
		} elseif($val_rub=='USD') {
			$val1 = 'доллар';
			$val2 = 'цент';
			if($sum22=='1' && $sum21!='1') $val1 .= ''; // 01,21...91 доллар
			elseif(in_array($sum22, $ar234) && ($sum21!='1')) $val1 .= 'a';
			else $val1 .= 'ов';
			if($kop2=='1' && $kop1!='1') $val2 .= ''; // 01,21...91 цент
			elseif(in_array($kop2, $ar234) && ($kop1!='1')) $val2 .= 'a';
			else $val2 .= 'ов';
			$val1 .= ' США';
			$cfR .= ' '.$val1.' '.$kopeiki.' '.$val2;
		} elseif($val_rub=='EUR') {
			$val1 = 'евро';
			$val2 = 'цент';
			if($kop2=='1' && $kop1!='1') $val2 .= ''; // 01,21...91 цент
			elseif(in_array($kop2, $ar234) && ($kop1!='1')) $val2 .= 'a';
			else $val2 .= 'ов';
			$cfR .= ' '.$val1.' '.$kopeiki.' '.$val2;
		} else {
			$cfR .= ' '.$val_rub;
			if($val_kop!='') $cfR .= ' '.$kopeiki.' '.$val_kop;
		}
		return $cfR;
	} // SumProp() end


	/* Обработка глобальных переменных  $_POST, $_GET, $_REQUEST, $_SESSION, $COOKIE */
	public static function _postVar($key, $is_num = false, $default = ''){
		if (isset($_POST[$key])) {
			return $is_num ? intval($_POST[$key]) : (is_array($_POST[$key]) ? $_POST[$key] : addslashes($_POST[$key]));
		} else {
			return $default;
		}
	}

	public static function _getVar($key, $is_num = false, $default = ''){
		if (isset($_GET[$key])) {
			return $is_num ? intval($_GET[$key]) : (is_array($_GET[$key]) ? $_GET[$key] : addslashes($_GET[$key]));
		} else {
			return $default;
		}
	}

	public static function _requestVar($key, $is_num = false, $default = ''){
		if (isset($_REQUEST[$key])) {
			return $is_num ? intval($_REQUEST[$key]) : (is_array($_REQUEST[$key]) ? $_REQUEST[$key] : addslashes($_REQUEST[$key]));
		} else {
			return $default;
		}
	}

	public static function _sessionVar($key, $is_num = false, $default = ''){
		if (isset($_SESSION[$key])) {
			return $is_num ? intval($_SESSION[$key]) : (is_array($_SESSION[$key]) ? $_SESSION[$key] : addslashes($_SESSION[$key]));
		} else {
			return $default;
		}
	}

	public static function _cookieVar($key, $is_num = false, $default = ''){
		if (isset($_COOKIE[$key])) {
			return $is_num ? intval($_COOKIE[$key]) : (is_array($_COOKIE[$key]) ? $_COOKIE[$key] : addslashes($_COOKIE[$key]));
		} else {
			return $default;
		}
	}

	/* Генерация Кеш-ключа */
	public static function genKey($n = 8) {
		$pwd = ''; 
		$k = 0; 
		$pass = array(); 
		$w = rand(30,80); 
		for ($r = 0; $r < $w; $r++) { 
			$res = rand(65,90); 
			$pass[$k] = chr($res); 
			$k++; 
			$res = rand(97,122); 
			$pass[$k] = chr($res); 
			$k++; 
			$res = rand(48,57); 
			$pass[$k] = chr($res); 
			$k++; 
		} 
		for ($i = 0; $i < $n; $i++) { 
			$s = rand(1,$k-1); 
			$pwd .= $pass[$s]; 
		} 
		return $pwd;
	}

	public static function guid(){
		if (function_exists('com_create_guid')){
			return com_create_guid();
		} else {
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = chr(123)// "{"
				.substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12)
				.chr(125);// "}"
			return $uuid;
		}
	}

	public static function fetch_user_salt($length = 3){
		$salt = '';
		for ($i = 0; $i < $length; $i++)
			$salt .= chr(rand(32, 126));
		return $salt;
	}

	public static function showMsg($message) {
		if (!empty($message['type']) && !empty($message['text'])) {
			return '
			<div class="message">
			<table cellpadding="0" cellspacing="0" border="0" class="message message-'.$message['type'].'">
			<tr>
			<td>
				<table cellpadding="0" cellspacing="0" border="0" class="content">
					<tr>
						<td valign="top"><div class="icon-'.$message['type'].'"></div></td>
						<td>
							<span class="message-title">'.($message['type'] == 'error' ? 'Ошибка' : 'Информация').'</span><br>
							<div class="empty" style="height:5px;"></div>'.$message['text'].'<br />
						</td>
					</tr>
				</table>
			</td>
			</tr>
			</table>
			</div>';
		} else {
			return '';
		}
	}

	public static function showError($sMessage) {
		echo <<<EOD
<div style="padding:15px;color:#990000;font-size:14px;"><b>Ошибка</b>:&nbsp;$sMessage</div>
EOD;
	}

}