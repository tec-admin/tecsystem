<?php
define('CMDLINE', TRUE);
if (isset($_SERVER['REMOTE_ADDR'])) die('Permission denied.');
set_time_limit(0);

require_once dirname(__FILE__) . '/../public_html/' . APPLICATION_TYPE . '/index.php';
