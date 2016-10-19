<?php
// 統合認証
class UnityAuthAdapter implements Zend_Auth_Adapter_Interface
{

	private $_uid;
	private $_member;

	public function __construct($uid)
	{
		$this->_uid		= $uid;
	}

	public function authenticate()
	{
		// DBからメンバ情報を取得
		$tMembers = new Class_Model_MMembers();
		$select = $tMembers->select();
		$select->where('account = ?', $this->_uid);
		$row = $tMembers->fetchRow($select);
		if ($row != NULL)
		{
			// パスワード以外のメンバ情報を保存
			$this->_member = new stdClass;
			$this->_member->id					= $row->id;
			$this->_member->account				= $this->_uid;
			$this->_member->roles				= $row->roles;
			$this->_member->email				= $row->email;
			$this->_member->type				= "unity";	// 統合認証
			$this->_member->name				= $row->name;
			$this->_member->name_jp				= $row->name_jp;
			$this->_member->name_kana			= $row->name_kana;
			$this->_member->undergraduate_id	= $row->undergraduate_id;
			$this->_member->department_id		= $row->department_id;
			$this->_member->usr_kbn				= $row->usr_kbn;
			$this->_member->age					= $row->age;
			$this->_member->sex					= $row->sex;
			$this->_member->tenure_kbn			= $row->tenure_kbn;
			$this->_member->enrollment_kbn		= $row->enrollment_kbn;
			$this->_member->student_id			= $row->student_id;
			$this->_member->languages			= $row->languages;
			$this->_member->original_user_flg	= $row->original_user_flg;
			$this->_member->displayflg			= $row->displayflg;
			$this->_member->createdate			= $row->createdate;
			$this->_member->creator				= $row->creator;
			$this->_member->lastupdate			= $row->lastupdate;
			$this->_member->lastupdater			= $row->lastupdater;

			return new Zend_Auth_Result(
				Zend_Auth_Result::SUCCESS,
				$this->_uid,
				array('Success.')
			);
		}
		else
		{
			return new Zend_Auth_Result(
				Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
				$this->_uid,
				array('User is not existed.')
			);
		}
	}

	public function getMemberInfo()
	{
		return $this->_member;
	}
}
