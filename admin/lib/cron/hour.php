<?php

    $PRJ_DIR = substr($PHP_SELF, 0, strpos($PHP_SELF, "/admin/lib/cron/"));
    inc_prj("config.php");
    inc_lib("AdminInterface/AdminInterface.php");
    $ai = new AdminInterface();
    $ai->cron("Hour");

?>