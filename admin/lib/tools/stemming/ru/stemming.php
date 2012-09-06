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
	�������� ��������� �������� ��� win1251 � php4 , ������ ��� ���� ���� ������ ���� �������� ;) 
	serge@rogozhkin.ru 
*/


define ('CHAR_LENGTH', '1'); // all Russian characters take 2 bytes in UTF-8, so instead of using (not supported by default) mb_*
                             // string functions, we use the standard ones with a dirty char-length trick.
                             // Should you want to use WIN-1251 (or any other charset), convert this source file to that encoding
                             // and then change CHAR_LENGTH to the proper value, which is likely to be '1' then.

class PorterStem {

	private static $_abc = '�������������������������������';
	private static $_ABC = '�����Ũ�������������������������';

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
      	$vowels=array('�','�','�','�','�','�','�','�','�');
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
    	$perfective1=array('�', '���', '�����');
      	foreach ($perfective1 as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�'))
            	return substr($word, 0, strlen($word)-strlen($suffix));

      	$perfective2=array('��','����','������','����','������');
      	foreach ($perfective2 as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix)
            	return substr($word, 0, strlen($word)-strlen($suffix));
      	$reflexive=array('��', '��');
      	foreach ($reflexive as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix)
            	$word=substr($word, 0, strlen($word)-strlen($suffix));

      	$adjective=array('��','��','��','��','���','���','��','��','��','��','��','��','��','��','���','���','���','���','��','��','��','��','��','��','��','��');
      	$participle2=array('��','��','��','��','�');
      	$participle1=array('���','���','���');
      	foreach ($adjective as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix){
             	$word=substr($word, 0, strlen($word)-strlen($suffix));
             	foreach ($participle1 as $suffix)
                	if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�'))
                  		$word=substr($word, 0, strlen($word)-strlen($suffix));
             	foreach ($participle2 as $suffix)
                	if (substr($word,-(strlen($suffix)))==$suffix)
                  		$word=substr($word, 0, strlen($word)-strlen($suffix));
             	return $word;
        	}

      	$verb1=array('��','��','���','���','��','�','�','��','�','��','��','��','��','��','��','���','���');
      	foreach ($verb1 as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�'))
            	return substr($word, 0, strlen($word)-strlen($suffix));
      	$verb2=array('���','���','���','����','����','���','���','���','��','��','��','��','��','��','��','���','���','���','��','���','���','��','��','���','���','���','���','��','�');
      	foreach ($verb2 as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix)
            	return substr($word, 0, strlen($word)-strlen($suffix));
      	$noun=array('�','��','��','��','��','�','����','���','���','��','��','�','���','��','��','��','�','���','��','���','��','��','��','�','�','��','���','��','�','�','��','��','�','��','��','�');
      	foreach ($noun as $suffix)
          	if (substr($word,-(strlen($suffix)))==$suffix)
            	return substr($word, 0, strlen($word)-strlen($suffix));
      	return $word;
   	}

 	private static function step2($word){
      	if (substr($word,-CHAR_LENGTH,CHAR_LENGTH)=='�')
            $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
      	return $word;
   	}

 	private static function step3($word) {
      	$vowels=array('�','�','�','�','�','�','�','�','�');
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
      	$derivational=array('���', '����');
      	foreach ($derivational as $suffix)
          	if (substr($r2,-(strlen($suffix)))==$suffix)
        		$word=substr($word, 0, strlen($r2)-strlen($suffix));
    	return $word;
   	}

 	private static function step4($word) {
      	if (substr($word,-CHAR_LENGTH*2)=='��')
        	$word=substr($word, 0, strlen($word)-CHAR_LENGTH);
      	else {
            $superlative=array('���', '����');
            foreach ($superlative as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix)
                  	$word=substr($word, 0, strlen($word)-strlen($suffix));
            if (substr($word,-CHAR_LENGTH*2)=='��')
                $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
		}
      	// should there be a guard flag? can't think of a russian word that ends with ���� or ��� anyways, though the algorithm states this is an "otherwise" case
      	if (substr($word,-CHAR_LENGTH,CHAR_LENGTH)=='�')
        	$word=substr($word, 0, strlen($word)-CHAR_LENGTH);
      	return $word;
   	}
}
?>