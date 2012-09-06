<?php
   
include_once('../config.php');
    inc_lib('AdminInterface/AdminInterface.php');
    $ai = new AdminInterface();
    $ai->show();
?>