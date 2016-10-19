<?php

  defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
  defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));


   set_include_path(implode(PATH_SEPARATOR, array(
       realpath(APPLICATION_PATH . '/../library'), // Zend<81>ASmarty
       realpath(APPLICATION_PATH . '/libs'),       // PEAR<81>A<8e>c<8d>i
       get_include_path(),
  )));

  require_once 'Zend/Version.php';
  echo Zend_Version::VERSION;

?>
