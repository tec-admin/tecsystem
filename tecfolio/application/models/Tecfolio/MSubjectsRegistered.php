<?php

require_once(dirname(__FILE__) . '/../BaseMModels.class.php');

class Class_Model_Tecfolio_MSubjectsRegistered extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME		= 'm_subjects_registered';
	protected $_name		= Class_Model_Tecfolio_MSubjectsRegistered::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_MSubjectsRegistered::TABLE_NAME;

		return array(
				'id' => 'id',
				'name' => new Zend_Db_Expr("yogen || '　' || class_subject"),
				
				'jyu_nendo' => 'jyu_nendo',
				'jyu_knr_no' => 'jyu_knr_no',
				'kyoincd' => 'kyoincd',
				'jkyoincd8' => 'jkyoincd8',
				
				'class_subject' => 'class_subject',
				'yogen' => 'yogen',
				'jwaricd' => 'jwaricd',
		);
	}
	
	public static function fieldArrayForInsert($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_MSubjectsRegistered::TABLE_NAME;
	
		return array(
				$prefix . '_id' => 'id',
				$prefix . '_jyu_nendo' => 'jyu_nendo',
				$prefix . '_jyu_knr_no' => 'jyu_knr_no',
				$prefix . '_jyu_knr_no_sub' => 'jyu_knr_no_sub',
				$prefix . '_kyoincd' => 'kyoincd',
				$prefix . '_jkyoincd8' => 'jkyoincd8',
				
				$prefix . '_grp4_key' => 'grp4_key',
				$prefix . '_gakka' => 'gakka',
				$prefix . '_kmkcd' => 'kmkcd',
				$prefix . '_kmkcd5' => 'kmkcd5',
				
				$prefix . '_class_subject' => 'class_subject',
				
				$prefix . '_setti_cd' => 'setti_cd',
				$prefix . '_jyu_kbn' => 'jyu_kbn',
				$prefix . '_jkeitai' => 'jkeitai',
				$prefix . '_tan_gakki' => 'tan_gakki',
				$prefix . '_class' => 'class',
				$prefix . '_yobi' => 'yobi',
				$prefix . '_jigen1' => 'jigen1',
				$prefix . '_yogen' => 'yogen',
				
				$prefix . '_jwaricd' => 'jwaricd',
	
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// 人事コード(8桁)から学事コード(5桁)を取得する
	public function selectKyoincdFromStaffno($staff_no)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$select = $db->select()
		->from(
				array('map' => 't_kyoin8_5_m'),
				array('ky_kyoincd')
		)
		->where('ky_jkyoincd8 = ?', $staff_no);
		
		return $db->fetchRow($select);
	}
	
	// 授業科目設定時、科目マスタ表のコピーを作成する
	public function insertFromMSubjects($kyoincd, $staff_no, $jyu_knr_no, $jwaricd)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$select = $db->select()->distinct()
		->from(
				array('subjects' => 'm_subjects'),
				array('id' => "('SUBJ_' || jyu_nendo || jyu_knr_no || kyoincd || jwaricd)", 'jyu_nendo', 'jyu_knr_no', 'jyu_knr_no_sub', 'kyoincd', 'jkyoincd8' => new Zend_Db_Expr("'" . $staff_no ."'"),
						'grp4_key', 'gakka', 'kmkcd', 'kmkcd5', 'class_subject', 'setti_cd', 'jyu_kbn',
						'jkeitai', 'tan_gakki', 'class', 'yobi', 'jigen1', 'yogen', 'jwaricd')
		)
		->where('kyoincd = ?', $kyoincd)
		;
		
		$str = "";
		foreach($jyu_knr_no as $k => $val)
		{
			$str .= "('" . $val . "','" . $jwaricd[$k] . "'),";
		}
		
		$select->where('(jyu_knr_no, jwaricd) IN ('. substr($str, 0, -1) . ')');
		
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		
		$this->insertSelect('m_subjects_registered', $select, $this::fieldArrayForInsert());
	}
	
	// 職員番号(8桁・メンバ表のもの)から選択
	public function selectFromStaffno($id)
	{
		$select1 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array(new Zend_Db_Expr('0 as seq'), 'id', 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->where('subjects.jkyoincd8 = ?', $id)
		->where('subjects.jkeitai != ?', '0')
		;
	
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array(new Zend_Db_Expr('1 as seq'), 'id', 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->where('subjects.jkyoincd8 = ?', $id)
		->where('subjects.jkeitai = ?', '0')
		;
	
		$select = $this->select()
		->union(array($select1, $select2))
		->order(array('seq asc','jkeitai asc', 'yobi asc', 'jigen1 asc'));
		
		return $this->fetchAll($select);
	}
	
	// 職員番号(8桁・メンバ表のもの)から選択
	public function selectFromStaffnoAndNendoAndGakki($id, $nendo, $gakki)
	{
		$select1 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array(new Zend_Db_Expr('0 as seq'), 'id', 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->where('subjects.jkyoincd8 = ?', $id)
		->where('subjects.jkeitai != ?', '0')
		->where('subjects.jyu_nendo = ?', $nendo)
		;
		if(!empty($gakki))
			$select1->where('subjects.tan_gakki = ?', $gakki);
	
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array(new Zend_Db_Expr('1 as seq'), 'id', 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->where('subjects.jkyoincd8 = ?', $id)
		->where('subjects.jkeitai = ?', '0')
		->where('subjects.jyu_nendo = ?', $nendo)
		;
		if(!empty($gakki))
			$select2->where('subjects.tan_gakki = ?', $gakki);
	
		$select = $this->select()
		->union(array($select1, $select2))
		->order(array('seq asc','jkeitai asc', 'yobi asc', 'jigen1 asc'));
	
		return $this->fetchAll($select);
	}
	
	// 履修データ表のコピーに存在する学籍番号から選択
	public function selectFromStudentId($id)
	{
		$select1 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array(new Zend_Db_Expr('0 as seq'), 'id', 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->joinLeft(
				array('course_roster' => 't_course_roster_registered'),
				'(subjects.jyu_nendo = course_roster.risyunen AND subjects.jwaricd = course_roster.kougicd)',
				''
		)
		->where('course_roster.gaksekno = ?', $id)
		->where('subjects.jkeitai != ?', '0')
		;
	
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array(new Zend_Db_Expr('1 as seq'), 'id', 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->joinLeft(
				array('course_roster' => 't_course_roster_registered'),
				'(subjects.jyu_nendo = course_roster.risyunen AND subjects.jwaricd = course_roster.kougicd)',
				''
		)
		->where('course_roster.gaksekno = ?', $id)
		->where('subjects.jkeitai = ?', '0')
		;
	
		$select = $this->select()
		->union(array($select1, $select2))
		->order(array('seq asc','jkeitai asc', 'yobi asc', 'jigen1 asc'));
	
		return $this->fetchAll($select);
	}
	
	// 履修データ表のコピーに存在する学籍番号から選択
	public function selectFromStudentIdAndNendoAndGakki($id, $nendo, $gakki)
	{
		$select1 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array(new Zend_Db_Expr('0 as seq'), 'id', 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->joinLeft(
				array('course_roster' => 't_course_roster_registered'),
				'(subjects.jyu_nendo = course_roster.risyunen AND subjects.jwaricd = course_roster.kougicd)',
				''
		)
		->where('course_roster.gaksekno = ?', $id)
		->where('subjects.jkeitai != ?', '0')
		->where('subjects.jyu_nendo = ?', $nendo)
		;
		if(!empty($gakki))
			$select1->where('subjects.tan_gakki = ?', $gakki);
	
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array(new Zend_Db_Expr('1 as seq'), 'id', 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->joinLeft(
				array('course_roster' => 't_course_roster_registered'),
				'(subjects.jyu_nendo = course_roster.risyunen AND subjects.jwaricd = course_roster.kougicd)',
				''
		)
		->where('course_roster.gaksekno = ?', $id)
		->where('subjects.jkeitai = ?', '0')
		->where('subjects.jyu_nendo = ?', $nendo)
		;
		if(!empty($gakki))
			$select2->where('subjects.tan_gakki = ?', $gakki);
	
		$select = $this->select()
		->union(array($select1, $select2))
		->order(array('seq asc','jkeitai asc', 'yobi asc', 'jigen1 asc'));
	
		return $this->fetchAll($select);
	}
	
	// IDから履修者情報を選択
	public function selectGakseFromId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array('id')
		)
		->joinLeft(
				array('course_roster' => 't_course_roster_registered'),
				'(subjects.jyu_nendo = course_roster.risyunen AND subjects.jwaricd = course_roster.kougicd)',
				array('m_member_id', 'student_id_jp', 'name_jp')
		)
		->where('subjects.id = ?', $id)
		->where('course_roster.name_jp IS NOT NULL')
		->order(array('course_roster.gaksekno asc'))
		;
		
		return $this->fetchAll($select);
	}
	
	// IDから履修者情報とポートフォリオを選択
	public function selectGaksePortfolioFromId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array('id')
		)
		->joinLeft(
				array('course_roster' => 't_course_roster_registered'),
				'(subjects.jyu_nendo = course_roster.risyunen AND subjects.jwaricd = course_roster.kougicd)',
				array('m_member_id', 'student_id_jp', 'name_jp')
		)
		->where('subjects.id = ?', $id)
		->where('course_roster.name_jp IS NOT NULL')
		->order(array('course_roster.gaksekno asc'))
		;
		
		return $this->fetchAll($select);
	}
	
	// IDと職員番号から選択
	public function selectFromIdAndStaffno($id, $staffno)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				Class_Model_Tecfolio_MSubjectsRegistered::fieldArray()
		)
		->where('subjects.id = ?', $id)
		->where('subjects.jkyoincd8 = ?', $staffno)
		;
	
		return $this->fetchRow($select);
	}
	
	// IDと学籍番号から選択
	public function selectFromIdAndStudentId($id, $student_id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				Class_Model_Tecfolio_MSubjectsRegistered::fieldArray()
		)
		->joinLeft(
				array('course_roster' => 't_course_roster_registered'),
				'(subjects.jyu_nendo = course_roster.risyunen AND subjects.jwaricd = course_roster.kougicd)',
				''
		)
		->where('subjects.id = ?', $id)
		->where('course_roster.gaksekno = ?', $student_id)
		;
	
		return $this->fetchRow($select);
	}
}