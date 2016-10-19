<?php

require_once(dirname(__FILE__) . '/../BaseMModels.class.php');

class Class_Model_Tecfolio_TCourseRosterRegistered extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME		= 't_course_roster_registered';
	protected $_name		= Class_Model_Tecfolio_TCourseRosterRegistered::TABLE_NAME;
	protected $_primary		= 'gakse_id';

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TCourseRosterRegistered::TABLE_NAME;

		return array(
			$prefix . '_m_member_id' 	=> 'm_member_id',
			$prefix . '_gakse_id' 		=> 'gakse_id',
			$prefix . '_gaksekno' 		=> 'gaksekno',
			
			$prefix . '_student_id_jp' 	=> 'student_id_jp',
			$prefix . '_name_jp' 		=> 'name_jp',
			$prefix . '_name_kana' 		=> 'name_kana',
			$prefix . '_syzkcd_c' 		=> 'syzkcd_c',
			
			$prefix . '_risyunen' 	=> 'risyunen',
			$prefix . '_semekikn' 	=> 'semekikn',
			$prefix . '_kougicd' 	=> 'kougicd',
			$prefix . '_jyuknrno' 	=> 'jyuknrno',
			$prefix . '_risystkn' 	=> 'risystkn',
			
			$prefix . '_lastupdate' 	=> 'lastupdate',
			$prefix . '_lastupdater' 	=> 'lastupdater',
		);
	}
	
	// 授業科目設定時、同時に履修科目表の該当項目をコピーする
	public function insertFromTCourseRoster($jyu_knr_no, $jwaricd)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$select = $db->select()
		->from(
				array('course_roster' => 't_course_roster'),
				array('m_member_id' => 'members.id', 'gakse_id', 'gaksekno', 
						'student_id_jp' => 'members.student_id_jp', 'name_jp' => 'members.name_jp', 'name_kana' => 'members.name_kana', 'syzkcd_c' => 'members.syzkcd_c',
						'risyunen', 'semekikn', 'kougicd', 'jyuknrno', 'risystkn')
		)
		->joinLeft(
				array('members' => 'm_members'),
				'members.student_id = course_roster.gaksekno',
				''
		)
		;
		
		$str = "";
		foreach($jyu_knr_no as $k => $val)
		{
			$str .= "('" . $val . "','" . $jwaricd[$k] . "'),";
		}
		
		$select->where('(jyuknrno, kougicd) IN ('. substr($str, 0, -1) . ')');
	
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		
		$this->insertSelect('t_course_roster_registered', $select, $this::fieldArray());
	}
	
	// その科目の履修者で未登録のデータを取得する
	public function selectUnregisteredUsers($nendo, $jwaricd)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subselect = $db->select()
		->from(
				array('registered' => 't_course_roster_registered'),
				array('gakse_id', 'risyunen', 'semekikn', 'kougicd')
		)
		;
		
		$select = $db->select()
		->from(
				array('course_roster' => 't_course_roster'),
				array('gakse_id', 'name_jp' => 'members.name_jp')
		)
		->joinLeft(
				array('members' => 'm_members'),
				'members.student_id = course_roster.gaksekno',
				''
		)
		->where('risyunen = ?', $nendo)
		->where('kougicd = ?', $jwaricd)
		->where('(gakse_id, risyunen, semekikn, kougicd) NOT IN (?)', $subselect)
		;
		
		return $db->fetchAll($select);
	}
	
	// その科目の履修者で登録済みのデータを取得する
	public function selectRegisteredUsers($nendo, $jwaricd)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subselect = $db->select()
		->from(
				array('registered' => 't_course_roster_registered'),
				array('gakse_id', 'risyunen', 'semekikn', 'kougicd')
		)
		;
		
		$select = $db->select()
		->from(
				array('course_roster' => 't_course_roster'),
				array('gakse_id', 'name_jp' => 'members.name_jp')
		)
		->joinLeft(
				array('members' => 'm_members'),
				'members.student_id = course_roster.gaksekno',
				''
		)
		->where('risyunen = ?', $nendo)
		->where('kougicd = ?', $jwaricd)
		->where('(gakse_id, risyunen, semekikn, kougicd) IN (?)', $subselect)
		;
		
		return $db->fetchAll($select);
	}
	
	// 選択された科目の情報と、ユーザの学籍番号から、履修データのコピーを作成する
	public function insertSelectedUsers($gakse_id, $nendo, $jwaricd)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$select = $db->select()
		->from(
				array('course_roster' => 't_course_roster'),
				array('m_member_id' => 'members.id', 'gakse_id', 'gaksekno', 
						'student_id_jp' => 'members.student_id_jp', 'name_jp' => 'members.name_jp', 'name_kana' => 'members.name_kana', 'syzkcd_c' => 'members.syzkcd_c',
						'risyunen', 'semekikn', 'kougicd', 'jyuknrno', 'risystkn')
		)
		->joinLeft(
				array('members' => 'm_members'),
				'members.student_id = course_roster.gaksekno',
				''
		)
		;
	
		$str = "";
		foreach($gakse_id as $k => $val)
		{
			$str .= "'" . $val . "',";
		}
		
		$select
			->where('risyunen = ?', $nendo)
			->where('kougicd = ?', $jwaricd)
			->where('gakse_id IN ('. substr($str, 0, -1) . ')');
	
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
	
		$this->insertSelect('t_course_roster_registered', $select, $this::fieldArray());
	}
	
	// 選択された科目の情報と、ユーザの学籍番号から、履修データのコピーを削除する
	public function deleteSelectedUsers($gakse_id, $nendo, $jwaricd)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$str = "";
		foreach($gakse_id as $k => $val)
		{
			$str .= "'" . $val . "',";
		}
		
		$where = array(
				$db->quoteInto('risyunen = ?', $nendo),
				$db->quoteInto('kougicd = ? AND gakse_id IN ('. substr($str, 0, -1) . ')', $jwaricd),
		);
	
		return $db->delete($this->_name, $where);
	}
}