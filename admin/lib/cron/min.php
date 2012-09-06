<?php

    // */1 * * * * /usr/local/bin/php /home/www/htdocs/lib/cron/min.php > /dev/null
    // 0 */1 * * * /usr/local/bin/php /home/www/htdocs/lib/cron/hour.php > /dev/null
    // 0 0 */1 * * /usr/local/bin/php /home/www/htdocs/lib/cron/day.php > /dev/null

    $PRJ_DIR = substr($PHP_SELF, 0, strpos($PHP_SELF, "/admin/lib/cron/"));
    inc_prj("config.php");
    inc_lib("AdminInterface/AdminInterface.php");
    $ai = new AdminInterface();
    $ai->cron("Min");

?>