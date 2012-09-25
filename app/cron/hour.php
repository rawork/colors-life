<?php

    $PRJ_DIR = substr($PHP_SELF, 0, strpos($PHP_SELF, "/admin/lib/cron/"));
    require_once('/../../../config.php');
    $ai = new \AdminInterface\AdminInterface();
    $ai->cron("Hour");

?>