<?php

/*
*  PHP5 implementation of Martin Porter's stemming algorithm for Russian language.
*  Written on a cold winter evening close to the end of 2005 by Dennis Kreminsky (etranger at etranger dot ru)
*  Use the code freely, but don't hold me responsible if it breaks whatever it might break.
*
*
*  Usage:
*  $stem=stem::russian($word);
*  All Russian characters are (originally) in UTF-8.
*
*/

/*
	—легонца варварски покоцано под win1251 и php4 , потому что надо было быстро чтоб работало ;) 
	serge@rogozhkin.ru 
*/


define ('CHAR_LENGTH', '1'); // all Russian characters take 2 bytes in UTF-8, so instead of using (not supported by default) mb_*
                             // string functions, we use the standard ones with a dirty char-length trick.
                             // Should you want to use WIN-1251 (or any other charset), convert this source file to that encoding
                             // and then change CHAR_LENGTH to the proper value, which is likely to be '1' then.

class PorterStem {

	private static $_abc = 'абвгдеЄжзийклмнопрстуфхцчшщъьэю€';
	private static $_ABC = 'јЅ¬√ƒ≈®∆«»… ЋћЌќѕ–—“”‘’÷„ЎўЏ№Ёёя';

	function rustolower($arg){
		for($i=0;$i<strlen(PorterStem::$_abc);$i++){
			$arg = str_replace(PorterStem::$_ABC{$i},PorterStem::$_abc{$i},$arg);
		}
		return $arg;
	}

	public static function stemming($word) {
    // RUSSIAN DIRTY LOWERCASE:
   		$word = PorterStem::rustolower($word);
   
    	$a=PorterStem::rv($word);
		$start=$a[0];
    	$rv=$a[1];
      	$rv=PorterStem::step1($rv);
      	$rv=PorterStem::step2($rv);
      	$rv=PorterStem::step3($rv);
      	$rv=PorterStem::step4($rv);
      	return $start.$rv;
   	}

 	private static function rv($word) {
      	$vowels=array('а','е','и','о','у','ы','э','ю','€');
      	$flag=0;
      	$rv='';
      	$start='';
      	for ($i=0; $i<strlen($word); $i+=CHAR_LENGTH) {
        	if ($flag==1)
               $rv.=substr($word, $i, CHAR_LENGTH);
            else
               	$start.=substr($word, $i, CHAR_LENGTH);
            if (array_search(substr($word,$i,CHAR_LENGTH), $vowels)!==FALSE)
               	$flag=1;
         	}
      	return array($start,$rv);
   	}

	private static function step1($word){
    	$perfective1=array('в', 'вши', 'вшись');
      	foreach ($perfective1 as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='а' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='€'))
            	return substr($word, 0, strlen($word)-strlen($suffix));

      	$perfective2=array('ив','ивши','ившись','ывши','ывшись');
      	foreach ($perfective2 as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix)
            	return substr($word, 0, strlen($word)-strlen($suffix));
      	$reflexive=array('с€', 'сь');
      	foreach ($reflexive as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix)
            	$word=substr($word, 0, strlen($word)-strlen($suffix));

      	$adjective=array('ее','ие','ые','ое','ими','ыми','ей','ий','ый','ой','ем','им','ым','ом','его','ого','ему','ому','их','ых','ую','юю','а€','€€','ою','ею');
      	$participle2=array('ем','нн','вш','ющ','щ');
      	$participle1=array('ивш','ывш','ующ');
      	foreach ($adjective as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix){
             	$word=substr($word, 0, strlen($word)-strlen($suffix));
             	foreach ($participle1 as $suffix)
                	if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='а' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='€'))
                  		$word=substr($word, 0, strlen($word)-strlen($suffix));
             	foreach ($participle2 as $suffix)
                	if (substr($word,-(strlen($suffix)))==$suffix)
                  		$word=substr($word, 0, strlen($word)-strlen($suffix));
             	return $word;
        	}

      	$verb1=array('ла','на','ете','йте','ли','й','л','ем','н','ло','но','ет','ют','ны','ть','ешь','нно');
      	foreach ($verb1 as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='а' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='€'))
            	return substr($word, 0, strlen($word)-strlen($suffix));
      	$verb2=array('ила','ыла','ена','ейте','уйте','ите','или','ыли','ей','уй','ил','ыл','им','ым','ен','ило','ыло','ено','€т','ует','уют','ит','ыт','ены','ить','ыть','ишь','ую','ю');
      	foreach ($verb2 as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix)
            	return substr($word, 0, strlen($word)-strlen($suffix));
      	$noun=array('а','ев','ов','ие','ье','е','и€ми','€ми','ами','еи','ии','и','ией','ей','ой','ий','й','и€м','€м','ием','ем','ам','ом','о','у','ах','и€х','€х','ы','ь','ию','ью','ю','и€','ь€','€');
      	foreach ($noun as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix)
            	return substr($word, 0, strlen($word)-strlen($suffix));
      	return $word;
   	}

 	private static function step2($word){
      	if (substr($word,-CHAR_LENGTH,CHAR_LENGTH)=='и')
            $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
      	return $word;
   	}

 	private static function step3($word) {
      	$vowels=array('а','е','и','о','у','ы','э','ю','€');
      	$flag=0;
      	$r1='';
      	$r2='';
      	for ($i=0; $i<strlen($word); $i+=CHAR_LENGTH) {
            if ($flag==2)
               	$r1.=substr($word, $i, CHAR_LENGTH);
            if (array_search(substr($word,$i,CHAR_LENGTH), $vowels)!==FALSE)
               	$flag=1;
            if ($flag=1 && array_search(substr($word,$i,CHAR_LENGTH), $vowels)===FALSE)
        		$flag=2;
        }
      	$flag=0;
		for ($i=0; $i<strlen($r1); $i+=CHAR_LENGTH){
            if ($flag==2)
               	$r2.=substr($r1, $i, CHAR_LENGTH);
            if (array_search(substr($r1,$i,CHAR_LENGTH), $vowels)!==FALSE)
               	$flag=1;
            if ($flag=1 && array_search(substr($r1,$i,CHAR_LENGTH), $vowels)===FALSE)
               	$flag=2;
        }
      	$derivational=array('ост', 'ость');
      	foreach ($derivational as $suffix)
          	if (substr($r2,-(strlen($suffix)))==$suffix)
        		$word=substr($word, 0, strlen($r2)-strlen($suffix));
    	return $word;
   	}

 	private static function step4($word) {
      	if (substr($word,-CHAR_LENGTH*2)=='нн')
        	$word=substr($word, 0, strlen($word)-CHAR_LENGTH);
      	else {
            $superlative=array('ейш', 'ейше');
            foreach ($superlative as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix)
                  	$word=substr($word, 0, strlen($word)-strlen($suffix));
            if (substr($word,-CHAR_LENGTH*2)=='нн')
                $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
		}
      	// should there be a guard flag? can't think of a russian word that ends with ейшь or ннь anyways, though the algorithm states this is an "otherwise" case
      	if (substr($word,-CHAR_LENGTH,CHAR_LENGTH)=='ь')
        	$word=substr($word, 0, strlen($word)-CHAR_LENGTH);
      	return $word;
   	}
}
?>