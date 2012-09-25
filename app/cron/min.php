<?php

    // */1 * * * * /usr/local/bin/php /home/www/htdocs/app/cron/min.php > /dev/null
    // 0 */1 * * * /usr/local/bin/php /home/www/htdocs/app/cron/hour.php > /dev/null
    // 0 0 */1 * * /usr/local/bin/php /home/www/htdocs/app/cron/day.php > /dev/null

    $PRJ_DIR = substr($PHP_SELF, 0, strpos($PHP_SELF, "/app/cron/"));
    require_once('../init.php');
    $ai = new \AdminInterface\AdminInterface();
    $ai->cron("Min");

?>