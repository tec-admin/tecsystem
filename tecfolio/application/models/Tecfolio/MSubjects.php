<?php

require_once(dirname(__FILE__) . '/../MSubjects.php');

// m_members テーブルクラスの拡張
class Class_Model_Tecfolio_MSubjects extends Class_Model_MSubjects
{
	// 職員番号(8桁)から選択
	// ※既に選択済みの授業科目は除く
	public function selectAvailableFromStaffno($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subselect = $db->select()
		->from(
				array('registered' => 'm_subjects_registered'),
				array('jyu_nendo', 'jyu_knr_no', 'kyoincd', 'jwaricd')
		)
		->where('jkyoincd8 = ?', $id);
		
		$select1 = $this->select()->distinct()->setIntegrityCheck(false)
			->from(
					array('subjects' => $this->_name),
					array(new Zend_Db_Expr('0 as seq'), 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
			)
			->join(
					array('tmap' => 't_kyoin8_5_m'),
					'subjects.kyoincd = tmap.ky_kyoincd',
					''
			)
			->join(
					array('prof' => 'm_members'),
					'prof.staff_no = tmap.ky_jkyoincd8',
					''
			)
			
			->where('prof.staff_no = ?', $id)
			->where('subjects.jkeitai != ?', '0')
			->where('(jyu_nendo, jyu_knr_no, kyoincd, jwaricd) NOT IN (?)', $subselect)
			;
			
		$select1 = $this->addWhereNendo($select1);
		
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
			->from(
					array('subjects' => $this->_name),
					array(new Zend_Db_Expr('1 as seq'), 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
			)
			->join(
					array('tmap' => 't_kyoin8_5_m'),
					'subjects.kyoincd = tmap.ky_kyoincd',
					''
			)
			->join(
					array('prof' => 'm_members'),
					'prof.staff_no = tmap.ky_jkyoincd8',
					''
			)
			
			->where('prof.staff_no = ?', $id)
			->where('subjects.jkeitai = ?', '0')
			->where('(jyu_nendo, jyu_knr_no, kyoincd, jwaricd) NOT IN (?)', $subselect)
			;
			
		$select2 = $this->addWhereNendo($select2);
		
		$select = $this->select()
		->union(array($select1, $select2))
		->order(array('seq asc','jkeitai asc', 'yobi asc', 'jigen1 asc'));
		
		return $this->fetchAll($select);
	}
	
	// 職員番号(8桁)から登録済みの授業科目をすべて選択
	public function selectSelectedAllFromStaffno($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$select1 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => 'm_subjects_registered'),
				array(new Zend_Db_Expr('0 as seq'), 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->where('jkyoincd8 = ?', $id)
		->where('jkeitai != ?', '0')
		;
	
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => 'm_subjects_registered'),
				array(new Zend_Db_Expr('0 as seq'), 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->where('jkyoincd8 = ?', $id)
		->where('jkeitai = ?', '0')
		;
	
		$select = $this->select()
		->union(array($select1, $select2))
		->order(array('seq asc','jkeitai asc', 'yobi asc', 'jigen1 asc'));
		
		return $this->fetchAll($select);
	}
	
	// 職員番号(8桁)から登録済みの授業科目で本年度のものを選択
	public function selectSelectedThisYearFromStaffno($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$select1 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => 'm_subjects_registered'),
				array(new Zend_Db_Expr('0 as seq'), 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->where('jkyoincd8 = ?', $id)
		->where('jkeitai != ?', '0')
		;
			
		$select1 = $this->addWhereNendo($select1);
	
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('subjects' => 'm_subjects_registered'),
				array(new Zend_Db_Expr('0 as seq'), 'jyu_nendo', 'jwaricd', 'jyu_knr_no', 'class_subject', 'yogen', 'jkeitai', 'yobi', 'jigen1')
		)
		->where('jkyoincd8 = ?', $id)
		->where('jkeitai = ?', '0')
		;
			
		$select2 = $this->addWhereNendo($select2);
	
		$select = $this->select()
		->union(array($select1, $select2))
		->order(array('seq asc','jkeitai asc', 'yobi asc', 'jigen1 asc'));
	
		return $this->fetchAll($select);
	}

}

