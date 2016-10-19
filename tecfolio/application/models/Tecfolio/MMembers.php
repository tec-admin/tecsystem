<?php

require_once(dirname(__FILE__) . '/../MMembers.php');

// m_members テーブルクラス
class Class_Model_Tecfolio_MMembers extends Class_Model_MMembers
{
	public function selectFromNameJp($name_jp)
	{
		$select = $this->select()->setIntegrityCheck(false);
		
		$select
		->from(
				array('members' => $this->_name),
				array('id', 'name_jp', 'syzkcd_c')
		)
		->joinLeft(
				array('profile' => 't_profiles'),
				'members.id = profile.m_member_id',
				'mentor_flag'
		);
		
		$select
			->where('members.name_jp LIKE  ?', '%' . $name_jp . '%')
			->where('profile.mentor_flag = \'1\'');
		
		return $this->fetchAll($select);
	}
	
	public function selectFromNameJpExceptMe($name_jp, $m_member_id)
	{
		$select = $this->select()->setIntegrityCheck(false);
	
		$select
		->from(
				array('members' => $this->_name),
				array('id', 'name_jp', 'syzkcd_c')
		)
		->joinLeft(
				array('profile' => 't_profiles'),
				'members.id = profile.m_member_id',
				array('mentor_flag', 'input_name')
		);
	
		$select
		->where('members.name_jp LIKE  ?', '%' . $name_jp . '%')
		->where('profile.mentor_flag = \'1\'')
		->where('members.id != ?', $m_member_id);
	
		return $this->fetchAll($select);
	}
}