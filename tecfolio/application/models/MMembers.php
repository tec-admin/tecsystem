<?php

require_once('BaseMModels.class.php');

// m_members テーブルクラス
class Class_Model_MMembers extends BaseMModels
{
	const TABLE_NAME = 'm_members';
	protected $_name = Class_Model_MMembers::TABLE_NAME;

	public static function fieldArray($prefix = "")
	{
		if ($prefix == '')
			$prefix = Class_Model_MMembers::TABLE_NAME;

		return array(
// 			$prefix . '_id' => 'id',
// 			$prefix . '_roles' => 'roles',
// 			$prefix . '_email' => 'email',
// 			$prefix . '_name' => 'name',
// 			$prefix . '_name_jp' => 'name_jp',
// 			$prefix . '_name_kana' => 'name_kana',
// 			$prefix . '_m_faculty_id' => 'm_faculty_id',
// 			$prefix . '_m_department_id' => 'm_department_id',
// 			$prefix . '_usr_kn' => 'usr_kn',
// 			$prefix . '_age' => 'age',
// 			$prefix . '_sex' => 'sex',
// 			$prefix . '_tenure_kbn' => 'tenure_kbn',
// 			$prefix . '_enrollment_kbn' => 'enrollment_kbn',
// 			$prefix . '_student_id' => 'student_id',
// 			$prefix . '_languages' => 'languages',
// 			$prefix . '_original_user_flg' => 'original_user_flg',
// 			$prefix . '_display_flg' => 'display_flg',
// 			$prefix . '_createdate' => 'createdate',
// 			$prefix . '_creator' => 'creator',
// 			$prefix . '_lastupdate' => 'lastupdate',
// 			$prefix . '_lastupdater' => 'lastupdater',
// 			$prefix . '_employee_id' => 'employee_id',
// 			$prefix . '_entrance_year' => 'entrance_year',
// 			$prefix . '_academic_year' => 'academic_year',
			
			$prefix . '_id' => 'id',
			$prefix . '_email' => 'email',
			$prefix . '_name' => 'name',
			$prefix . '_name_jp' => 'name_jp',
			$prefix . '_name_kana' => 'name_kana',
			$prefix . '_usr_kn' => 'usr_kn',
			$prefix . '_age' => 'age',
			$prefix . '_sex' => 'sex',
			$prefix . '_student_id' => 'student_id',
			$prefix . '_student_id_jp' => 'student_id_jp',
			$prefix . '_setti_cd' => 'setti_cd',
			$prefix . '_syozkcd1' => 'syozkcd1',
			$prefix . '_syozkcd2' => 'syozkcd2',
			$prefix . '_syzkcd_c' => 'syzkcd_c',
			$prefix . '_original_user_flg' => 'original_user_flg',
			$prefix . '_staff_no' => 'staff_no',
			$prefix . '_entrance_year' => 'entrance_year',
			$prefix . '_gaknenkn' => 'gaknenkn',
		);
	}

	public static function fieldArrayTwc($prefix = "")
	{
		if ($prefix == '')
			$prefix = Class_Model_MMembers::TABLE_NAME;

		return array(
				$prefix . '_id' => 'id',
				$prefix . '_roles' => 'roles',
				$prefix . '_email' => 'email',
				$prefix . '_name' => 'name',
				$prefix . '_name_jp' => 'name_jp',
				$prefix . '_name_kana' => 'name_kana',
				$prefix . '_m_faculty_id' => 'm_faculty_id',
				$prefix . '_m_department_id' => 'm_department_id',
				$prefix . '_usr_kbn' => 'usr_kbn',
				$prefix . '_age' => 'age',
				$prefix . '_sex' => 'sex',
				$prefix . '_tenure_kbn' => 'tenure_kbn',
				$prefix . '_enrollment_kbn' => 'enrollment_kbn',
				$prefix . '_student_id' => 'student_id',
				$prefix . '_languages' => 'languages',
				$prefix . '_original_user_flg' => 'original_user_flg',
				$prefix . '_display_flg' => 'display_flg',
				$prefix . '_createdate' => 'createdate',
				$prefix . '_creator' => 'creator',
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
				$prefix . '_shift_roles' => 'shift_roles',
				$prefix . '_employee_id' => 'employee_id',
				$prefix . '_entrance_year' => 'entrance_year',
				$prefix . '_academic_year' => 'academic_year',
		);
	}

	public function GetSelectFromFuzzyKeyword($keyword)
	{
		$likekeyword = '%' . $keyword . '%';

		$select = $this->select();
		$select->where('student_id = ?', $keyword)
				->orWhere('name like ?', $likekeyword)
				->orWhere('name_jp like ?', $likekeyword)
				->orWhere('name_kana like ?', $likekeyword)
				->order(array('id'));
		return $select;
	}
	
	public function selectFromRoles($roles)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
			array('members' => 'm_members'),
			'*'
		)
		
		->join(
			array('attribute' => 't_member_attribute'),
			'attribute.id = members.id',
			array('roles' => 'roles')
		);
		$select->where('roles like ?', '%' . $roles . '%');
		
		return $this->fetchAll($select);
	}
	
	public function selectFromStudentId($studentid)
	{
		$select = $this->select();
		$select->where('student_id = ?', $studentid);
		return $this->fetchRow($select);
	}
	
	public function selectFromStudentIdJP($studentid)
	{
		$select = $this->select();
		$select->where('student_id = ?', $studentid);
		$select->orWhere('student_id_jp = ?', $studentid);
		return $this->fetchRow($select);
	}

	/********** 以下こいわ **********/

	public function selectFromUserId($id)
	{
		$select = $this->select();
		$select->where('id = ?', $id);
		return $this->fetchRow($select);
	}

	public function selectFromStudentEmployeeId($id)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('student_id = ?', $id);
;
		$select2->where('employee_id = ?', $id);

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromRoleStudentEmployeeId($id, $role)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('student_id = ?', $id);
		$select1->where('roles  like ?', '%' . $role . '%');

		$select2->where('employee_id = ?', $id);
		$select2->where('roles  like ?', '%' . $role . '%');

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromAccount($account)
	{
		$select = $this->select();
		$select->where('account LIKE  ?', '%' . $account . '%');
		return $this->fetchRow($select);
	}
	public function selectFromName_jp($name_jp)
	{
		$select = $this->select();
		$select->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		return $this->fetchAll($select);
	}
	public function selectFromRoleName_jp($name_jp,$role)
	{
		$select = $this->select();
		$select->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		$select->where('roles  like ?', '%' . $role . '%');
		return $this->fetchAll($select);
	}
	public function selectFromFacultyId($subjectid)
	{
		$select = $this->select();
		$select->where('m_faculty_id = ?', $subjectid);
		return $this->fetchAll($select);
	}
	public function selectFromRoleFacultyId($subjectid,$role)
	{
		$select = $this->select();
		$select->where('m_faculty_id = ?', $subjectid);
		$select->where('roles  like ?', '%' . $role . '%');

		return $this->fetchAll($select);
	}
	public function selectFromDepartmentId($subjectid)
	{
		$select = $this->select();
		$select->where('m_department_id = ?', $subjectid);

		return $this->fetchAll($select);
	}
	public function selectFromRoleDepartmentId($subjectid,$role)
	{
		$select = $this->select();
		$select->where('m_department_id = ?', $subjectid);
		$select->where('roles  like ?', '%' . $role . '%');

		return $this->fetchAll($select);
	}

	public function selectFromNameFacultyId($subjectid,$name_jp)
	{
		$select = $this->select();
		$select->where('m_faculty_id = ?', $subjectid);
		$select->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		return $this->fetchAll($select);
	}
	public function selectFromRoleNameFacultyId($subjectid,$name_jp,$role)
	{
		$select = $this->select();
		$select->where('m_faculty_id = ?', $subjectid);
		$select->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		$select->where('roles  like ?', '%' . $role . '%');
		return $this->fetchAll($select);
	}
	public function selectFromNameDepartmentId($subjectid,$name_jp)
	{
		$select = $this->select();
		$select->where('m_department_id = ?', $subjectid);
		$select->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		return $this->fetchAll($select);
	}
	public function selectFromRoleNameDepartmentId($subjectid,$name_jp,$role)
	{
		$select = $this->select();
		$select->where('m_department_id = ?', $subjectid);
		$select->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		$select->where('roles  like ?', '%' . $role . '%');
		return $this->fetchAll($select);
	}

	public function selectFromStudentEmployeeIdFacultyId($subjectid,$id)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('m_faculty_id = ?', $subjectid);
		$select1->where('student_id = ?', $id);

		$select2->where('m_faculty_id = ?', $subjectid);
		$select2->where('employee_id = ?', $id);

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromRoleStudentEmployeeIdFacultyId($subjectid,$id,$role)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('m_faculty_id = ?', $subjectid);
		$select1->where('student_id = ?', $id);
		$select1->where('roles  like ?', '%' . $role . '%');

		$select2->where('m_faculty_id = ?', $subjectid);
		$select2->where('employee_id = ?', $id);
		$select2->where('roles  like ?', '%' . $role . '%');

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromStudentEmployeeIdDepartmentId($subjectid,$id)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('m_faculty_id = ?', $subjectid);
		$select1->where('student_id = ?', $id);

		$select2->where('m_faculty_id = ?', $subjectid);
		$select2->where('employee_id = ?', $id);

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromRoleStudentEmployeeIdDepartmentId($subjectid,$id,$role)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('m_faculty_id = ?', $subjectid);
		$select1->where('student_id = ?', $id);
		$select1->where('roles  like ?', '%' . $role . '%');

		$select2->where('m_faculty_id = ?', $subjectid);
		$select2->where('employee_id = ?', $id);
		$select2->where('roles  like ?', '%' . $role . '%');

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromIdName($name_jp,$id)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('student_id = ?', $id);
		$select1->where('name_jp LIKE  ?', '%' . $name_jp . '%');

		$select2->where('employee_id = ?', $id);
		$select2->where('name_jp LIKE  ?', '%' . $name_jp . '%');

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromRoleIdName($name_jp,$id,$role)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('student_id = ?', $id);
		$select1->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		$select1->where('roles  like ?', '%' . $role . '%');

		$select2->where('employee_id = ?', $id);
		$select2->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		$select2->where('roles  like ?', '%' . $role . '%');

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromIdNameFacultyId($subjectid,$name_jp,$id)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('student_id = ?', $id);
		$select1->where('m_faculty_id = ?', $subjectid);
		$select1->where('name_jp LIKE  ?', '%' . $name_jp . '%');

		$select2->where('employee_id = ?', $id);
		$select2->where('m_faculty_id = ?', $subjectid);
		$select2->where('name_jp LIKE  ?', '%' . $name_jp . '%');

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromRoleIdNameFacultyId($subjectid,$name_jp,$id,$role)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('student_id = ?', $id);
		$select1->where('m_faculty_id = ?', $subjectid);
		$select1->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		$select1->where('name_jp LIKE  ?', '%' . $name_jp . '%');

		$select2->where('employee_id = ?', $id);
		$select2->where('m_faculty_id = ?', $subjectid);
		$select2->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		$select2->where('roles  like ?', '%' . $role . '%');

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromIdNameDepartmentId($subjectid,$name_jp,$id)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('student_id = ?', $id);
		$select1->where('m_department_id = ?', $subjectid);
		$select1->where('name_jp LIKE  ?', '%' . $name_jp . '%');

		$select2->where('employee_id = ?', $id);
		$select2->where('m_department_id = ?', $subjectid);
		$select2->where('name_jp LIKE  ?', '%' . $name_jp . '%');

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function selectFromRoleIdNameDepartmentId($subjectid,$name_jp,$id,$role)
	{
		$select = $this->select();
		$select1 = $this->select();
		$select2 = $this->select();

		$select1->where('student_id = ?', $id);
		$select1->where('m_department_id = ?', $subjectid);
		$select1->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		$select1->where('name_jp LIKE  ?', '%' . $name_jp . '%');

		$select2->where('employee_id = ?', $id);
		$select2->where('m_department_id = ?', $subjectid);
		$select2->where('name_jp LIKE  ?', '%' . $name_jp . '%');
		$select2->where('name_jp LIKE  ?', '%' . $name_jp . '%');

		$select->union(array($select1,$select2));

		return $this->fetchAll($select);
	}
	public function insertcsv($csv)
	{
		$select = $this->select();

		if (!is_array($csv))
			$csv = array();

		return parent::insert($csv);
	}
	/********** 以上こいわ **********/

	public function getDistinctEntranceYear()
	{
		$select = $this->select()->distinct();
		$select->where('entrance_year is not null');
		$select->order('entrance_year asc');
		return $this->fetchAll($select);
	}
	
	// ユーザ検索
	public function getSearchedUser($roles, $userid, $name, $gakubu, $gakka, $page=1, $limit=20)
	{
		$name = mb_convert_encoding($name, "UTF-8");
		
		$select = $this->select()->setIntegrityCheck(false);
		
		$select
			->from( 
				array('members' => $this->_name), 
				Class_Model_MMembers::fieldArray('members')
			)
			->join(
				array('attribute' => 't_member_attribute'),
				'attribute.id = members.id',
				array('roles' => 'roles')
			)
		;

		$select->joinLeft(
				array('syozoku1' => 't_syozoku1'),
				'syozoku1.setti_cd = members.setti_cd AND syozoku1.syozkcd1 = members.syozkcd1',
				Class_Model_TSyozoku1::fieldArray()
		)
		->joinLeft(
				array('syozoku2' => 't_syozoku2'),
				'syozoku2.setti_cd = members.setti_cd AND syozoku2.syozkcd1 = members.syozkcd1 AND syozoku2.syozkcd2 = members.syozkcd2',
				Class_Model_TSyozoku2::fieldArray()
		)
		;
		
		// 複数ロールのORにより選択
		//if(!empty($roles))
		//	BaseModels::connectOrWhereWithLIKE($select, 'members.roles', $roles);
		
		// 一時的にシステム管理者を除外
		//$select->where('roles NOT LIKE ?', '%System%');
		
		// 複数ロールのANDにより選択
		if(!empty($roles))
		{
			foreach($roles as $role)
				$select->where('roles LIKE ?', '%' . $role . '%');
		}
		
		if(!empty($userid))
		{
			$select->where('(members.student_id LIKE ?', '%' . $userid . '%');
			$select->orWhere('members.student_id_jp LIKE ?', '%' . $userid . '%');
			$select->orWhere('members.staff_no LIKE ?)', '%' . $userid . '%');
		}
		if(!empty($name))
		{
			$select->where('(members.name_jp LIKE ?', '%' . $name . '%');
			$select->orWhere('members.name_kana LIKE ?', '%' . $name . '%');
			$select->orWhere('members.name_kana LIKE ?)', '%' . mb_convert_kana($name, "KVC") . '%');
		}
		if(!empty($gakubu))
			$select->where('syozoku1.szknam_c LIKE ?', '%' . $gakubu . '%');
		if(!empty($gakka))
			$select->where('syozoku2.szknam_c LIKE ?', '%' . $gakka . '%');
		
		$select->order(array('syozoku1.z008szsrt_no asc', 'syozoku2.z008szsrt_no asc', new Zend_Db_Expr("LPAD(members.student_id,'10','0') asc")));
		
		$select->limitPage($page, $limit);
		
		return $this->fetchAll($select);
	}
	
	// ユーザ検索
	public function getSearchedUserCount($roles, $userid, $name, $gakubu, $gakka)
	{
		$name = mb_convert_encoding($name, "UTF-8");
	
		$select = $this->select()->setIntegrityCheck(false);
	
		$select
		->from(
				array('members' => $this->_name),
				new Zend_Db_Expr('count(*) as count')
		)
		->join(
				array('attribute' => 't_member_attribute'),
				'attribute.id = members.id',
				''
		);
	
		$select->joinLeft(
				array('syozoku1' => 't_syozoku1'),
				'syozoku1.setti_cd = members.setti_cd AND syozoku1.syozkcd1 = members.syozkcd1',
				''
		)
		->joinLeft(
				array('syozoku2' => 't_syozoku2'),
				'syozoku2.setti_cd = members.setti_cd AND syozoku2.syozkcd1 = members.syozkcd1 AND syozoku2.syozkcd2 = members.syozkcd2',
				''
		)
		;
	
		// 複数ロールのANDにより選択
		if(!empty($roles))
		{
			foreach($roles as $role)
				$select->where('roles LIKE ?', '%' . $role . '%');
		}
	
		if(!empty($userid))
		{
			$select->where('(members.student_id LIKE ?', '%' . $userid . '%');
			$select->orWhere('members.student_id_jp LIKE ?', '%' . $userid . '%');
			$select->orWhere('members.staff_no LIKE ?)', '%' . $userid . '%');
		}
		if(!empty($name))
		{
			$select->where('(members.name_jp LIKE ?', '%' . $name . '%');
			$select->orWhere('members.name_kana LIKE ?', '%' . $name . '%');
			$select->orWhere('members.name_kana LIKE ?)', '%' . mb_convert_kana($name, "KVC") . '%');
		}
		if(!empty($gakubu))
			$select->where('syozoku1.szknam_c LIKE ?', '%' . $gakubu . '%');
		if(!empty($gakka))
			$select->where('syozoku2.szknam_c LIKE ?', '%' . $gakka . '%');
	
		return $this->fetchAll($select);
	}
	
	// ユーザ検索
	public function getSearchedUserTwc($roles, $userid, $name, $faculty, $department, $shiftclass)
	{
		$name = mb_convert_encoding($name, "UTF-8");
	
		$select = $this->select()->setIntegrityCheck(false);

		$select->from( array('members' => $this->_name), Class_Model_MMembers::fieldArrayTwc() );
		$select->joinLeft(
				array('l_shiftclasses' => 'm_l_shiftclasses'),
				'l_shiftclasses.m_shiftclass_id = members.shift_roles',
				Class_Model_MLShiftclasses::fieldArray()
		);
		
		$select->joinLeft(
				array('faculties' => 'm_faculties'),
				'faculties.id = members.m_faculty_id',
				Class_Model_MFaculties::fieldArray()
		)
		->joinLeft(
				array('departments' => 'm_departments'),
				'departments.id = members.m_department_id',
				Class_Model_MDepartments::fieldArray()
		)
		;
	
		// 複数ロールのORにより選択
		//if(!empty($roles))
		//	BaseModels::connectOrWhereWithLIKE($select, 'members.roles', $roles);
	
		// 一時的にシステム管理者を除外
		$select->where('members.roles NOT LIKE ?', '%System%');
	
		// 複数ロールのANDにより選択
		if(!empty($roles))
		{
			foreach($roles as $role)
				$select->where('members.roles LIKE ?', '%' . $role . '%');
		}
	
		if(!empty($userid))
			$select->where('members.student_id LIKE ?', '%' . $userid . '%');
		if(!empty($name))
		{
			$select->where('(members.name_jp LIKE ?', '%' . $name . '%');
			$select->orWhere('members.name_kana LIKE ?', '%' . $name . '%');
			$select->orWhere('members.name_kana LIKE ?)', '%' . mb_convert_kana($name, "KVC") . '%');
		}
		if(!empty($faculty))
			$select->where('faculties.name LIKE ?', '%' . $faculty . '%');
		if(!empty($department))
			$select->where('departments.name LIKE ?', '%' . $department . '%');
		
		if(!empty($shiftclass))
			$select->where('l_shiftclasses.l_class_name LIKE ?', '%' . $shiftclass . '%');
	
		$select->order(new Zend_Db_Expr("TRANSLATE(members.student_id, '0123456789', ''), TO_NUMBER( SUBSTRING(members.student_id FROM '[0-9].*$'), '99999999' )"));
	
		return $this->fetchAll($select);
	}

	public function selectAllUser()
	{
		$select = $this->select();
		$select->order('id asc');
		return $this->fetchAll($select);
	}
}

