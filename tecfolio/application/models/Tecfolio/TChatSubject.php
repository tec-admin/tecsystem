<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TChatSubject extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_chat_subject';
	protected $_name   = Class_Model_Tecfolio_TChatSubject::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TChatSubject::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_m_subject_reg_id' 	=> 'm_subject_reg_id',
			$prefix . '_m_member_id' 		=> 'm_member_id',
			$prefix . '_m_member_name_jp' 	=> 'm_member_name_jp',

			$prefix . '_title' => 'title',
			$prefix . '_body' => 'body',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}

	// 登録授業科目IDより選択
	public function selectFromSubjectId($id, $m_members_id, $staff_no)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('chat_subject'=>'t_chat_subject'), '*'
		)
		->joinLeft(
				array('profiles' => 't_profiles'),
				'chat_subject.m_member_id = profiles.m_member_id',
				array('t_profiles_input_name' => 'input_name')
		)
		->joinLeft(
				array('members' => 'm_members'),	// membersのjoinは現状必要ないが、もしIDが存在しない際にその旨を表示する場合
				'chat_subject.m_member_id = members.id',
				array('m_members_name_jp' => 'name_jp')
		)
		->joinLeft(
				array('subj_map' => 't_chat_subject_contents'),
				'chat_subject.id = subj_map.t_chat_subject_id',
				''
		)
		->joinLeft(
				array('contents' => 't_contents'),
				'subj_map.t_content_id = contents.id',
				array('content_id' => 'id', 'ref_title', 'ref_url', 'ref_class', 
						'display' => new Zend_Db_Expr("CASE WHEN contents.publicity = 1 OR kyoin_map.ky_jkyoincd8 = '" . $staff_no ."' OR contents.creator = '" . $m_members_id . "' THEN '1' ELSE '0' END"))
		)
		->joinLeft(
				array('content_files' => 't_content_files'),
				'contents.t_content_file_id = content_files.id',
				array('content_files_id' => 'id', 'content_files_name' => 'name', 'content_files_type' => 'type')
		)
		->joinLeft(
				array('subjects' => 'm_subjects_registered'),
				'subjects.id = contents.m_mytheme_id',
				''
		)
		->joinLeft(
				array('kyoin_map' => 't_kyoin8_5_m'),
				'subjects.kyoincd = kyoin_map.ky_kyoincd',
				''
		)
		;

		$select
		->where('chat_subject.m_subject_reg_id = ?', $id)
		->where("(contents.delete_flag != '1' OR contents.delete_flag IS NULL)")
		;
		
		return $this->fetchAll($select);
	}
}

