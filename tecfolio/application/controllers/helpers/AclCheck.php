<?php
// ACLアクションヘルパー
class Class_Action_Helper_AclCheck extends Zend_Controller_Action_Helper_Abstract
{
	private $_acl;

	/*
	* 権限の設定
	*/
	public function __construct()
	{
		$this->_acl = new Zend_Acl();
		
		// リソースを設定
		$this->_acl->add(new Zend_Acl_Resource('Common'));
		$this->_acl->add(new Zend_Acl_Resource('Reserve'));
		$this->_acl->add(new Zend_Acl_Resource('Management'));
		$this->_acl->add(new Zend_Acl_Resource('Database'));
		$this->_acl->add(new Zend_Acl_Resource('Class'));


		// ロールの設定
		// メモ	：addRoleの第二引数に与えたロールを継承できる
		// →例	：$this->_acl->addRole(new Zend_Acl_Role('Administrator'),'Staff');
		$this->_acl->addRole(new Zend_Acl_Role('Guest'));						// ゲスト
		$this->_acl->addRole(new Zend_Acl_Role('Student'));						// 生徒
		$this->_acl->addRole(new Zend_Acl_Role('Staff'));						// スタッフ
		$this->_acl->addRole(new Zend_Acl_Role('Administrator'));				// 管理者
		$this->_acl->addRole(new Zend_Acl_Role('Professor'));					// 教員

		// ロールにリソースを設定
		//View   （閲覧する権限）
		//Edit   （編集する権限）
		
		// コモン権限
		$this->_acl->allow('Guest', 'Common', 'View');
		$this->_acl->allow('Student', 'Common', array('View', 'Edit'));
		$this->_acl->allow('Staff', 'Common', array('View', 'Edit'));
		$this->_acl->allow('Administrator', 'Common', array('View', 'Edit'));
		$this->_acl->allow('Professor', 'Common', array('View', 'Edit'));
		
		// リザーブ権限(学生)
		$this->_acl->allow('Student', 'Reserve', array('View', 'Edit'));

		// マネージメント権限(スタッフ)
 		$this->_acl->allow('Staff', 'Management', array('View', 'Edit'));
		
		// データベース権限(運営管理者)
		$this->_acl->allow('Administrator', 'Database', array('View', 'Edit'));
		
		// クラス権限(教員)
		$this->_acl->allow('Professor', 'Class', array('View', 'Edit'));
	}

	/*
	* 権限のチェック
	*
	* $resource	チェックするページのリソース(Common/Management)
	* $priv		可能かどうかチェックする操作(Read/Update/Create/Delete)
	*/
	public function check($resource, $priv)
	{
		$auth = Zend_Auth::getInstance();

		// Zend_Authインスタンスより、roles(カンマ区切り)を取得
		$roles = explode(',', $auth->getIdentity()->roles);

		$flag = FALSE;
		if(!empty($auth->getIdentity()->roles))
		{
			for ($i = 0; $i < count($roles); $i++)
			{
				$role = trim($roles[$i]);
				if($this->_acl->isAllowed($role, $resource, $priv))
				{
					$flag =TRUE;
				}
			}
		}

		if (!$flag)
		{	// ロールに対する権限がない
			//Zend_Auth::getInstance()->clearIdentity();
			$res = $this->getResponse();
			$res->setHttpResponseCode(403);
			//$res->setHeader('Refresh', '5; URL='.$_SERVER['SERVER_NAME']);
			$res->setBody('アクセスが拒否されました');
			$res->sendResponse();
			exit();
		}
		return;
	}

	/*
	* デフォルトメソッド
	*/
	public function direct($resource, $priv)
	{
		return $this->check($resource, $priv);
	}
}
