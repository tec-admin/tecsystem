<?php

require_once('BaseMModels.class.php');

/**
 * 大分類（マスター）モデルクラス
 *
 * 対応テーブル : m_subjects
 *
 * @author		satake
 * @version		0.0.1
 */
class Class_Model_MSubjects extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME		= 'm_subjects';
	protected $_name		= Class_Model_MSubjects::TABLE_NAME;
	protected $_primary		= 'jyu_knr_no';

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MSubjects::TABLE_NAME;

		return array(
			$prefix . '_jyu_nendo' => 'jyu_nendo',
			$prefix . '_jyu_knr_no' => 'jyu_knr_no',
			//$prefix . '_kyoincd' => 'kyoincd',
			
			//$prefix . '_kmkmei_n' => 'kmkmei_n',
			$prefix . '_class_subject' => 'class_subject',
			$prefix . '_jwaricd' => 'jwaricd',

			//$prefix . '_createdate' => 'createdate',
			//$prefix . '_creator' => 'creator',
			//$prefix . '_lastupdate' => 'lastupdate',
			//$prefix . '_lastupdater' => 'lastupdater',
			//$prefix . '_display_flg' => 'display_flg',
		);
	}
	
	protected function joinField($select)
	{
		$select
		->joinLeft(
				array('course_roster' => 't_course_roster'),
				'course_roster.jyuknrno = subjects.jyu_knr_no',
				Class_Model_TCourseRoster::fieldArray()
		)
	
		->joinLeft(
				array('reserver' => 'm_members'),
				'reserver.student_id = course_roster.gaksekno',
				Class_Model_MMembers::fieldArray('reserver')
		)
		;
	
		return $select;
	}
	
	protected function addWhereNendo($select)
	{
// 		$frontController = Zend_Controller_Front::getInstance();
// 		$config = $frontController->getParam("bootstrap")->getOptions();
// 		$smonth = $config['nendo']['start']['month'];
// 		$sdate = $config['nendo']['start']['date'];
// 		$time = strtotime(Zend_Registry::get('nowdatetime'));
		
// 		if (date('m', $time) < $smonth || (date('m', $time) == $smonth && date('d', $time) < $sdate))
// 		{
// 			$select->where("subjects.jyu_nendo = '?'", date('Y', $time) - 1);
// 		}
// 		else
// 		{
// 			$select->where("subjects.jyu_nendo = '?'", date('Y', $time) );
// 		}
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$nendo = $db->select()
			->from(
				array('nendo'=>'m_nendo'),
				array('current_nendo')
			);
		$select->where("subjects.jyu_nendo = ?", $nendo);
		
		return $select;
	}
	
	// 時間割コードで選択（オーバーライド）
	public function selectFromId($id)
	{
		$select = $this->select()->distinct()
			->from(
				array('subjects' => $this->_name),
				Class_Model_MSubjects::fieldArray('subjects')
			);
		$select->where('subjects.jwaricd = ?', $id);
		
		return $this->fetchRow($this->addWhereNendo($select));
	}
	
	// 時間割コードと年度で選択
	public function selectFromJwaricdAndNendo($jwaricd, $jyu_nendo)
	{
		$select = $this->select();
		$select->where('jwaricd = ?', $jwaricd);
		$select->where('jyu_nendo = ?', $jyu_nendo);
		
		return $this->fetchRow($select);
	}
	
	// 学籍番号で取得
	/*
	 * ・学生から授業・科目を選択する画面にて、リストボックスから科目を選択する際に表示される授業科目順を以下の条件でソートして欲しい。
	 * 1．授業形態　：昇順
	 * 2．曜日：昇順
	 * 3．時限：昇順
	 * ※注意：授業形態が「0」の項目は一番最後に表示する
	 */
	public function selectFromStudentId($id)
	{
		$select1 = $this->select()->distinct()->setIntegrityCheck(false)
			->from(
				array('subjects' => $this->_name),
				array(new Zend_Db_Expr('0 as seq'), 'jwaricd', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
			)
			->join(
					array('course_roster' => 't_course_roster'),
					'course_roster.kougicd = subjects.jwaricd',
					''
			)
			
			->join(
					array('reserver' => 'm_members'),
					'reserver.student_id = course_roster.gaksekno',
					''
			)

			->where('reserver.student_id = ?', $id)
			->where('subjects.jkeitai != ?', '0');
			
		$select1 = $this->addWhereNendo($select1);
		
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
			->from(
					array('subjects' => $this->_name),
					array(new Zend_Db_Expr('1 as seq'), 'jwaricd', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
			)
			->join(
					array('course_roster' => 't_course_roster'),
					'course_roster.kougicd = subjects.jwaricd',
					''
			)
				
			->join(
					array('reserver' => 'm_members'),
					'reserver.student_id = course_roster.gaksekno',
					''
			)
			
			->where('reserver.student_id = ?', $id)
			->where('subjects.jkeitai = ?', '0');
		$select2 = $this->addWhereNendo($select2);
		
		$select = $this->select()
		->union(array($select1, $select2))
		->order(array('seq asc','jkeitai asc', 'yobi asc', 'jigen1 asc'));
		
		return $this->fetchAll($select);
	}
	
	// 学籍番号で取得
	public function selectFromStudentIdJP($id)
	{
		$select1 = $this->select()->distinct()->setIntegrityCheck(false)
			->from(
				array('subjects' => $this->_name),
				array(new Zend_Db_Expr('0 as seq'), 'jwaricd', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
			)
			->join(
					array('course_roster' => 't_course_roster'),
					'course_roster.kougicd = subjects.jwaricd',
					''
			)
			
			->join(
					array('reserver' => 'm_members'),
					'reserver.student_id = course_roster.gaksekno',
					''
			)

			->where('(reserver.student_id = ?', $id)
			->orWhere('reserver.student_id_jp = ?)', $id)
			->where('subjects.jkeitai != ?', '0');
			
		$select1 = $this->addWhereNendo($select1);
		
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
			->from(
					array('subjects' => $this->_name),
					array(new Zend_Db_Expr('1 as seq'), 'jwaricd', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
			)
			->join(
					array('course_roster' => 't_course_roster'),
					'course_roster.kougicd = subjects.jwaricd',
					''
			)
				
			->join(
					array('reserver' => 'm_members'),
					'reserver.student_id = course_roster.gaksekno',
					''
			)
			
			->where('(reserver.student_id = ?', $id)
			->orWhere('reserver.student_id_jp = ?)', $id)
			->where('subjects.jkeitai = ?', '0');
		$select2 = $this->addWhereNendo($select2);
		
		$select = $this->select()
		->union(array($select1, $select2))
		->order(array('seq asc','jkeitai asc', 'yogen asc', 'yobi asc', 'jigen1 asc'));
		
		return $this->fetchAll($select);
	}
	
	// 学籍番号で時間割取得
	public function selectTimetablesFromStudentId($id, $weektop, $weekend)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('subjects' => $this->_name),
				array('jwaricd', 'class_subject', 'yobi')
		)
		->join(
				array('course_roster' => 't_course_roster'),
				'course_roster.kougicd = subjects.jwaricd AND course_roster.risyunen = subjects.jyu_nendo',
				''
		)
		->join(
				array('jikanwari' => 't_jyugyo_jikanwari'),
				'jikanwari.jyu_knr_no = subjects.jyu_knr_no AND jikanwari.jyu_nendo = subjects.jyu_nendo',
				''
		)
		->join(
				array('reserver' => 'm_members'),
				'reserver.student_id = course_roster.gaksekno',
				''
		)
		->join(
				array('timetables' => 'm_jyugyo_timetables'),
				'jikanwari.jigen = CAST(timetables.id AS varchar)',
				array('starttime', 'endtime')
		)
	
		->where('reserver.student_id = ?', $id)
		->where(new Zend_Db_Expr('to_timestamp("jikanwari"."jyugyody",\'YYYYMMDD\')') . ' >= ?', $weektop)
		->where(new Zend_Db_Expr('to_timestamp("jikanwari"."jyugyody",\'YYYYMMDD\')') . ' <= ?', $weekend)
		->order(array('jwaricd asc', 'class_subject asc'));
		
		return $this->fetchAll($this->addWhereNendo($select));
	}

}

