<?php

    $PRJ_DIR = substr($PHP_SELF, 0, strpos($PHP_SELF, "/app/cron/"));
    require_once('../init.php');
    $ai = new \AdminInterface\AdminInterface();
    $ai->cron("Hour");

?>