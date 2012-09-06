<?php
    include_once('config.php');
    inc_lib('CPage.php');
	$page = new Page($GLOBALS['utree']);
   	$page->show();
