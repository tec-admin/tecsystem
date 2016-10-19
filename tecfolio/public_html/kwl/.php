<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Tokyo');


// アプリケーション・ディレクトリへのパスを定義します
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application_kwl'));

// アプリケーション環境を定義します
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// libraryディレクトリーをinclude_pathに追加
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../../library'),	// Zend、Smarty
    realpath(APPLICATION_PATH . '/libs'),	// PEAR、自作
    get_include_path(),
)));


/** Zend_Application */
require_once 'Zend/Application.php';

//Zend_Session::start();

// アプリケーション及びブートストラップを作成して、実行します
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();


