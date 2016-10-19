<?php

require_once('BaseTModels.class.php');

class Class_Model_TCourseList extends BaseTModels
{
	protected $_name   = 't_course_list';
	
	private function addWhereNendo($select)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$nendo = $db->select()
		->from(
				array('nendo'=>'m_nendo'),
				array('current_nendo')
		);
		$select->where("course_list.jyu_nendo IN (?)", $nendo);	// 実際には現年度一行のみ
		
		return $select;
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
				array('course_list' => $this->_name),
				array(new Zend_Db_Expr('0 as seq'), 'jwaricd', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
			)
			->join(
					array('course_roster' => 't_course_roster'),
					'course_roster.kougicd = course_list.jwaricd',
					''
			)
			
			->join(
					array('reserver' => 'm_members'),
					'reserver.student_id = course_roster.gaksekno',
					''
			)

			->where('reserver.student_id = ?', $id)
			->where('course_list.jkeitai != ?', '0');
			
		$select1 = $this->addWhereNendo($select1);
		
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
			->from(
					array('course_list' => $this->_name),
					array(new Zend_Db_Expr('1 as seq'), 'jwaricd', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
			)
			->join(
					array('course_roster' => 't_course_roster'),
					'course_roster.kougicd = course_list.jwaricd',
					''
			)
				
			->join(
					array('reserver' => 'm_members'),
					'reserver.student_id = course_roster.gaksekno',
					''
			)
			
			->where('reserver.student_id = ?', $id)
			->where('course_list.jkeitai = ?', '0');
		$select2 = $this->addWhereNendo($select2);
		
		$select = $this->select()
		->union(array($select1, $select2))
		->order(array('seq asc','jkeitai asc', 'yobi asc', 'jigen1 asc'));
		
		return $this->fetchAll($select);
	}
}
