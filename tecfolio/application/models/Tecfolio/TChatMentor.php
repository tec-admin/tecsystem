<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TChatMentor extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_chat_mentor';
	protected $_name   = Class_Model_Tecfolio_TChatMentor::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TChatMentor::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			
			$prefix . '_m_mytheme_id' => 'm_mytheme_id',
			$prefix . '_t_mentor_id' => 't_mentor_id',
			$prefix . '_m_member_id' => 'm_member_id',
			$prefix . '_tgt_member_id' => 'tgt_member_id',
			$prefix . '_title' => 'title',
			$prefix . '_body' => 'body',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// メンターIDから選択
	public function selectFromMentorId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('chat_mentor'=>'t_chat_mentor'), '*'
		)
		->joinLeft(
				array('profiles' => 't_profiles'),
				'chat_mentor.m_member_id = profiles.m_member_id',
				array('t_profiles_input_name' => 'input_name')
		)
		->joinLeft(
				array('members' => 'm_members'),
				'chat_mentor.m_member_id = members.id',
				array('m_members_name_jp' => 'name_jp')
		)
		->joinLeft(
				array('mentors' => 't_mentors'),
				'chat_mentor.t_mentor_id = mentors.id',
				array('t_mentors_m_member_id' => 'm_member_id')
		)
		;
		
		$select
			->where('chat_mentor.t_mentor_id = ?', $id)
			->order(array('lastupdate desc'))
		;
		
		return $this->fetchAll($select);
	}
	
	// メンターIDと対象のメンバーIDから選択
	public function selectFromMentorIdAndTargetId($id, $target)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('chat_mentor'=>'t_chat_mentor'), '*'
		)
		->joinLeft(
				array('profiles' => 't_profiles'),
				'chat_mentor.m_member_id = profiles.m_member_id',
				array('t_profiles_input_name' => 'input_name')
		)
		->joinLeft(
				array('members' => 'm_members'),
				'chat_mentor.m_member_id = members.id',
				array('m_members_name_jp' => 'name_jp')
		)
		->joinLeft(
				array('mentors' => 't_mentors'),
				'chat_mentor.t_mentor_id = mentors.id',
				array('t_mentors_m_member_id' => 'm_member_id')
		)
		;
	
		$select
		->where('chat_mentor.t_mentor_id = ?', $id)
		->where('(chat_mentor.m_member_id = ?', $target)
		->orWhere('chat_mentor.tgt_member_id = ?)', $target)
		->order(array('lastupdate desc'))
		;
	
		return $this->fetchAll($select);
	}
}

