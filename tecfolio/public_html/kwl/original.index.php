<?php

// �A�v���P�[�V�����E�f�B���N�g���ւ̃p�X���`���܂�
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// �A�v���P�[�V���������`���܂�
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// library�f�B���N�g���[��include_path�ɒǉ�
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

//Zend_Session::start();

// �A�v���P�[�V�����y�уu�[�g�X�g���b�v���쐬���āA���s���܂�
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();

