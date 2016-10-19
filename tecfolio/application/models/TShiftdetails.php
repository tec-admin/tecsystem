<?php

require_once('BaseTModels.class.php');

// t_reserves テーブルクラス
class Class_Model_TShiftdetails extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_shiftdetails';
	protected $_name   = Class_Model_TShiftdetails::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TShiftdetails::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_m_member_id' => 'm_member_id',
			$prefix . '_shiftdate' => 'shiftdate',
			$prefix . '_m_shift_id' => 'm_shift_id',
			$prefix . '_dow' => 'dow',
			$prefix . '_type' => 'type',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	private function setJoinField($select)
	{
		$select
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shiftdetails.m_shift_id = shifts.id',
				Class_Model_MShifts::fieldArray()
		)
	
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				Class_Model_MTimetables::fieldArray()
		)
	
		->joinLeft(
				array('members' => 'm_members'),
				'shiftdetails.m_member_id = members.id',
				Class_Model_MMembers::fieldArray()
		)
		;
	
		return $select;
	}
	
	private function setJoinFieldTwc($select)
	{
		$select
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shiftdetails.m_shift_id = shifts.id',
				Class_Model_MShifts::fieldArray()
		)
	
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				Class_Model_MTimetables::fieldArray()
		)
	
		->joinLeft(
				array('members' => 'm_members'),
				'shiftdetails.m_member_id = members.id',
				Class_Model_MMembers::fieldArrayTwc()
		)
		;
	
		return $select;
	}
	
	// 指定のメンバーID、キャンパスID, シフト時間帯、曜日に属するシフト詳細を選択
	public function selectFromDetails($termid, $campusid, $dayno, $m_member_id, $weektop, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('places'=>'m_places'),
				array('places.id'))
				->where('places.m_campus_id = ?', $campusid);
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.dayno = ?', $dayno)
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_place_id IN (?)', $subselect1)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftdetails' => $this->_name), "*")
		->where('shiftdetails.m_member_id = ?', $m_member_id)
		->where('shiftdetails.m_shift_id IN (?)', $subselect2)
		->where('shiftdetails.shiftdate = ?', new Zend_Db_Expr("(date '$weektop' + $dow - 1)"))
		;
	
		return $this->fetchAll($select);
	}
	
	// 指定のキャンパスID、 日付範囲に属するシフト詳細を選択
	public function selectFromRange($campusid, $startdate, $enddate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('places'=>'m_places'),
				array('places.id'))
				->where('places.m_campus_id = ?', $campusid);
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'));
		if($campusid != 0)
			$subselect2->where('shifts.m_place_id IN (?)', $subselect1);
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftdetails' => $this->_name), "*")
		->where('shiftdetails.m_shift_id IN (?)', $subselect2)
		->where('shiftdetails.shiftdate >= ?', $startdate)
		->where('shiftdetails.shiftdate <= ?', $enddate)
		;
		
		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 指定のシフト種別ID、日付範囲に属するシフト詳細を選択
	public function selectFromRangeForTwc($shiftclass, $startdate, $enddate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'))
				->where('dockinds.shiftclass = ?', $shiftclass);
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'));
		if($shiftclass != 0)
			$subselect2->where('shifts.m_dockind_id IN (?)', $subselect1);
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftdetails' => $this->_name), "*")
		->where('shiftdetails.m_shift_id IN (?)', $subselect2)
		->where('shiftdetails.shiftdate >= ?', $startdate)
		->where('shiftdetails.shiftdate <= ?', $enddate)
		;
	
		return $this->fetchAll($this->setJoinFieldTwc($select));
	}
	
	// 関大：指定のキャンパスに属する相談場所で指定の順番のシフトを列挙し、そのシフトを参照している指定曜日のシフトスタッフを削除する
	public function deleteFromDetails($termid, $campusid, $dayno, $m_member_id, $weektop, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('places'=>'m_places'),
				array('places.id'))
				->where('places.m_campus_id = ?', $campusid);
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.dayno = ?', $dayno)
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_place_id IN (?)', $subselect1)
				;
	
		$where = array(
				$db->quoteInto('m_member_id = ?', $m_member_id),
				$db->quoteInto('m_shift_id IN (?)', $subselect2),
				$db->quoteInto('shiftdate = ?', new Zend_Db_Expr("(date '$weektop' + $dow - 1)")),
		);
	
		$db->delete($this->_name, $where);
	}
	
	// 津田：指定のシフト種別に属する相談場所で指定の順番のシフトを列挙し、そのシフトを参照している指定曜日のシフトスタッフを削除する
	public function deleteFromDetailsForTwc($termid, $shiftclass, $dayno, $m_member_id, $weektop, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$shiftclasses = explode(",", $shiftclass);
		
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'))
				->where('dockinds.shiftclass = ?', $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$subselect1->orWhere("dockinds.shiftclass = ?", $shiftclasses[1]);
		}
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.dayno = ?', $dayno)
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				;
	
		$where = array(
				$db->quoteInto('m_member_id = ?', $m_member_id),
				$db->quoteInto('m_shift_id IN (?)', $subselect2),
				$db->quoteInto('shiftdate = ?', new Zend_Db_Expr("(date '$weektop' + $dow - 1)")),
		);
	
		$db->delete($this->_name, $where);
	}
}
