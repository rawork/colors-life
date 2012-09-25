<?php

    // */1 * * * * /usr/local/bin/php /home/www/htdocs/lib/cron/min.php > /dev/null
    // 0 */1 * * * /usr/local/bin/php /home/www/htdocs/lib/cron/hour.php > /dev/null
    // 0 0 */1 * * /usr/local/bin/php /home/www/htdocs/lib/cron/day.php > /dev/null

    $PRJ_DIR = substr($PHP_SELF, 0, strpos($PHP_SELF, "/admin/lib/cron/"));
    require_once('/../../../config.php');
    $ai = new \AdminInterface\AdminInterface();
    $ai->cron("Min");

?>