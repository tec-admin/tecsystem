<?php
// 独自認証
class OrgAuthAdapter implements Zend_Auth_Adapter_Interface
{

	private $_uid;
	private $_password;
	private $_member;

	public function __construct($uid, $password)
	{
		$this->_uid			= $uid;
		$this->_password	= $password;
	}

	public function authenticate()
	{
		// DBからメンバ情報を取得
		$mMembers = new Class_Model_MMembers();
		$select = $mMembers->select()->setIntegrityCheck(false)
			
			->from(
				array('members' => 'm_members'),
				Class_Model_MMembers::fieldArray('members')
			)
			
			->join(
				array('attribute' => 't_member_attribute'),
				'attribute.id = members.id',
				array('attribute_roles' => 'roles')
			)
			->joinLeft(
				array('profile' => 't_profiles'),
				'profile.m_member_id = members.id',
				array('profile_languages' => 'languages')
			);
		
		$select->where('members.id = ?', $this->_uid);
		$select->where('password = md5(?)', $this->_password);
		
		$row = $mMembers->fetchRow($select);
		if ($row != NULL)
		{
			// パスワード以外のメンバ情報を保存
			$this->_member = new stdClass;
			$this->_member->id					= $this->_uid;
			$this->_member->roles				= $row->attribute_roles;
			$this->_member->email				= $row->members_email;
			$this->_member->type				= "org";	// 独自認証
			$this->_member->name				= $row->members_name;
			$this->_member->name_jp				= $row->members_name_jp;
			$this->_member->name_kana			= $row->members_name_kana;
			$this->_member->usr_kn				= $row->members_usr_kn;
			$this->_member->age					= $row->members_age;
			$this->_member->sex					= $row->members_sex;
			$this->_member->student_id			= $row->members_student_id;
			if(empty($this->_member->student_id))
				$this->_member->student_id		= '';
			$this->_member->staff_no			= $row->members_staff_no;
			if(empty($this->_member->staff_no))
				$this->_member->staff_no		= '';
			$this->_member->original_user_flg	= $row->members_original_user_flg;
			$this->_member->setti_cd			= $row->members_setti_cd;
			$this->_member->syzkcd_c			= $row->members_syzkcd_c;
			$this->_member->syozkcd1			= $row->members_syozkcd1;
			$this->_member->syozkcd2			= $row->members_syozkcd2;
			$this->_member->entrance_year		= $row->members_entrance_year;
			$this->_member->gaknenkn			= $row->members_gaknenkn;
			
			$this->_member->languages			= $row->profile_languages;
			
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
