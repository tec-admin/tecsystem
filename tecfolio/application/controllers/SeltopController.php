<?php
require_once('BaseController.class.php');

class SeltopController extends BaseController
{

	public function init()
	{
		$this->_helper->AclCheck('Common', 'View');
	}

	// トップ
	public function indexAction()
	{

	}
}

