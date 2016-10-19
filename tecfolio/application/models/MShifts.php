<?php

require_once('BaseMModels.class.php');

/**
 * 大分類（マスター）モデルクラス
 *
 * 対応テーブル : m_shifts
 *
 * @author		satake
 * @version		0.0.1
*/
class Class_Model_MShifts extends BaseMModels
{
	/**
	 * @var string 対応テーブル名
	 */
	const TABLE_NAME = 'm_shifts';
	protected $_name   = Class_Model_MShifts::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MShifts::TABLE_NAME;

		return array(
				$prefix . '_id' => 'id',

				$prefix . '_m_term_id' => 'm_term_id',
				$prefix . '_m_dockind_id' => 'm_dockind_id',
				$prefix . '_m_place_id' => 'm_place_id',
				$prefix . '_dayno' => 'dayno',
				$prefix . '_starttime' => 'starttime',
				$prefix . '_endtime' => 'endtime',

				//$prefix . '_createdate' => 'createdate',
				//$prefix . '_creator' => 'creator',
				//$prefix . '_lastupdate' => 'lastupdate',
				//$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	public static function fieldArrayForInsert($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MShifts::TABLE_NAME;
	
		return array(
				$prefix . '_m_term_id' => 'm_term_id',
				$prefix . '_m_dockind_id' => 'm_dockind_id',
				$prefix . '_m_place_id' => 'm_place_id',
				$prefix . '_dayno' => 'dayno',
	
				$prefix . '_createdate' => 'createdate',
				$prefix . '_creator' => 'creator',
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
		);
	}

	/**
	 *	共通ユニークIDで検索(オーバーライド)
	 *
	 *	@param	integer	$id		 検索ID
	 *	@return	objecs	stdObj
	 */
	public function selectFromId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shifts' => $this->_name), "*")
				->joinLeft(
						array('timetables' => 'm_timetables'),
						'shifts.dayno = timetables.id',
						Class_Model_MTimetables::fieldArray()
				)
				->where('shifts.id = ?', $id);

		return $this->fetchRow($select);
	}

	public function selectJoinedRows($t_reserves_id)
	{
		$select = $this->select()->setIntegrityCheck(false)

		->from(
				array('shifts' => $this->_name), "*")
				
				->joinLeft(
						array('timetables' => 'm_timetables'),
						'shifts.dayno = timetables.id',
						Class_Model_MTimetables::fieldArray()
				)
				
				->joinLeft(
						array('dockinds' => 'm_dockinds'),
						'dockinds.id = shifts.m_dockind_id',
						Class_Model_MDockinds::fieldArray()
				)
				->joinLeft(
						array('places' => 'm_places'),
						'places.id = shifts.m_place_id',
						Class_Model_MPlaces::fieldArray()
				)

				->joinLeft(
						array('reserves' => 't_reserves'),
						'reserves.m_shift_id = shifts.id',
						Class_Model_TReserves::fieldArray()
				)

				->joinLeft(
						array('subjects' => 'm_subjects'),
						'subjects.jwaricd = reserves.jwaricd',
						Class_Model_MSubjects::fieldArray()
				)

				->where('reserves.id = ?', $t_reserves_id)
				;

		/*
		$frontController = Zend_Controller_Front::getInstance();
		$config = $frontController->getParam("bootstrap")->getOptions();
		$this->logpath = $config['trace']['log']['path'];
		$this->logname = $config['trace']['log']['name'];
		$logFile = $this->logpath . '/' . strtr($this->logname, array('#DT#' => strftime('%d')));
		$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
		$logger->log($select->__toString(), Zend_Log::DEBUG);
		*/

		return $this->fetchRow($select);
	}
	
	// 文書更新時にシフトを更新
	public function insertShiftFromDockind($dockindid)
	{
		$select = $this->select()->distinct()
		->from(
				array('shifts' => $this->_name),
				array('shifts.m_term_id', 'dockindid' => '(' . $dockindid .')', 'shifts.m_place_id', 'shifts.dayno')
		)
		->order(new Zend_Db_Expr("1,2,3,4"));
	
		$select->columns(array('createdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
	
		$this->insertSelect('m_shifts', $select, $this::fieldArrayForInsert());
	}
	
	// 場所更新時にシフトを更新
	public function insertShiftFromPlace($placeid)
	{
		$select = $this->select()->distinct()
			->from(
					array('shifts' => $this->_name), 
					array('shifts.m_term_id', 'shifts.m_dockind_id', 'placeid' => '(' . $placeid .')', 'shifts.dayno')
			)
			->order(new Zend_Db_Expr("1,2,3,4"));
		
		$select->columns(array('createdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		
		$this->insertSelect('m_shifts', $select, $this::fieldArrayForInsert());
	}
	
	// 学期更新時にシフトを更新
	public function insertShiftFromTerm($termid)
	{
		$select = $this->select()->distinct()
		->from(
				array('shifts' => $this->_name),
				array('termid' => '(' . $termid .')', 'shifts.m_dockind_id', 'shifts.m_place_id', 'shifts.dayno')
		)
		->order(new Zend_Db_Expr("1,2,3,4"));
	
		$select->columns(array('createdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
	
		$this->insertSelect('m_shifts', $select, $this::fieldArrayForInsert());
	}
	
	// 文書IDで削除
	public function deleteFromDockindId($dockindid)
	{
		$where = $this->getAdapter()->quoteInto('m_dockind_id = ?', $dockindid);
	
		return $this->delete($where);
	}
	
	// 場所IDで削除
	public function deleteFromPlaceId($placeid)
	{
		$where = $this->getAdapter()->quoteInto('m_place_id = ?', $placeid);
	
		return $this->delete($where);
	}
	
	// 学期IDで削除
	public function deleteFromTermId($termid)
	{
		$where = $this->getAdapter()->quoteInto('m_term_id = ?', $termid);
	
		return $this->delete($where);
	}

	// シフトの1グループ（m_term_id, m_dockind_id, m_place_id でユニークなもの）取得
	public function selectShiftGroup($m_term_id, $m_dockind_id, $m_place_id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shifts' => $this->_name), "*")
				->joinLeft(
						array('timetables' => 'm_timetables'),
						'shifts.dayno = timetables.id',
						Class_Model_MTimetables::fieldArray()
				);
		$select->where('m_term_id = ?', $m_term_id);
		$select->where('m_dockind_id = ?', $m_dockind_id);
		$select->where('m_place_id = ?', $m_place_id);
		$select->order(array('dayno'));
		
		return $this->fetchAll($select);
	}

	// 【津大用】シフトの1グループ（m_term_id, $m_dockind_shiftclassでユニークなもの）取得
	public function selectShiftGroupForTwc($m_term_id, $shiftclass, $m_place_id=1)
	{
		$shiftclasses = explode(",", $shiftclass);
		$db = Zend_Db_Table::getDefaultAdapter();
		// $m_dockind_idからshift_classを取得
		$subselect = $db->select()
		->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		if($shiftclasses[0] != 9)
			$subselect->where("dockinds.shiftclass = ?", $shiftclasses[0]);
		$subselect->limit(1);

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shifts' => $this->_name), "*")
				->joinLeft(
						array('timetables' => 'm_timetables'),
						'shifts.dayno = timetables.id',
						Class_Model_MTimetables::fieldArray()
				);
		$select->where('m_term_id = ?', $m_term_id);
		$select->where('m_dockind_id = ?', $subselect);
		$select->where('m_place_id = ?', $m_place_id);
		$select->order(array('dayno'));
		
		return $this->fetchAll($select);
	}

	// 指定の日付が属する期間のシフトを取得
	public function getShiftFromDate($date = '')
	{
		if (empty($date))
			$date = Zend_Registry::get('nowdate');

		$db = Zend_Db_Table::getDefaultAdapter();
		$termids = $db->select()->from(	// 指定の日時が範囲内にある期間ID
				array('terms'=>'m_terms'),
				array('terms.id'))
				->where("terms.startdate <= ? AND terms.enddate >= ?", $date, $date);

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shift' => $this->_name), "*")

				->where('shift.m_term_id IN (?)', $termids)

				->order(array('shift.id'));

		return $this->fetchAll($select);
	}

	// 指定キャンパス、通し番号のシフトを取得
	public function selectFromTermIdAndCampusIdAndDayno($termid, $campusid, $dayno)
	{
		if (empty($date))
			$date = Zend_Registry::get('nowdate');

		$db = Zend_Db_Table::getDefaultAdapter();
		$placeids = $db->select()->from(	// 指定の日時が範囲内にある期間ID
				array('places'=>'m_places'),
				array('places.id'))
				->where("places.m_campus_id = ?", $campusid);

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shift' => $this->_name), "*")

				->where('shift.m_term_id = ?', $termid)
				->where('shift.m_place_id IN (?)', $placeids)
				->where('shift.dayno = ?', $dayno)

				->order(array('shift.id'));

		return $this->fetchAll($select);
	}
	
	// 何れかの学期、指定キャンパス、通し番号のシフトを取得
	public function selectFromMultiTermIdAndCampusIdAndDayno(array $termids, $campusid, $dayno)
	{
		if (empty($date))
			$date = Zend_Registry::get('nowdate');
	
		$db = Zend_Db_Table::getDefaultAdapter();
		$placeids = $db->select()->from(	// 指定の日時が範囲内にある期間ID
				array('places'=>'m_places'),
				array('places.id'))
				->where("places.m_campus_id = ?", $campusid);
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shift' => $this->_name), "*")

				->where('shift.m_place_id IN (?)', $placeids)
				->where('shift.dayno = ?', $dayno);
		
		//配列の最初にポインタを移動
		reset($termids);
		//このときのキーを取得
		$array_first_info = each($termids);
		$array_first_key = $array_first_info["key"];
		
		//配列の最後にポインタを移動
		end($termids);
		//このときのキーを取得
		$array_last_info = each($termids);
		$array_last_key = $array_last_info["key"];
		
		foreach($termids as $key => $value)
		{
			if($key === $array_first_key)
				$select->where('(shift.m_term_id = ?', $value);
			elseif($key === $array_last_key)
				$select->orWhere('shift.m_term_id = ?)', $value);
			else
				$select->orWhere('shift.m_term_id = ?', $value);
		}
	
		$select->order(array('shift.id'));
	
		return $this->fetchAll($select);
	}

	// 指定シフト種別、通し番号のシフトを取得
	public function selectFromTermIdAndShiftclassAndDayno($termid, $shiftclass, $dayno)
	{
		$shiftclasses = explode(",", $shiftclass);

		if (empty($date))
			$date = Zend_Registry::get('nowdate');

		$db = Zend_Db_Table::getDefaultAdapter();
		$dockindids = $db->select()->from(	// 指定の日時が範囲内にある期間ID
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'))
				->where("dockinds.shiftclass = ?", $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$dockindids->orWhere("dockinds.shiftclass = ?", $shiftclasses[1]);
		}

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shift' => $this->_name), "*")

				->where('shift.m_term_id = ?', $termid)
				->where('shift.m_dockind_id IN (?)', $dockindids)
				->where('shift.dayno = ?', $dayno)

				->order(array('shift.id'));

		return $this->fetchAll($select);
	}
	
	// 何れかの学期、指定キャンパス、通し番号のシフトを取得
	public function selectFromMultiTermIdAndShiftclassAndDayno(array $termids, $shiftclass, $dayno)
	{
		$shiftclasses = explode(",", $shiftclass);
		
		if (empty($date))
			$date = Zend_Registry::get('nowdate');
	
		$db = Zend_Db_Table::getDefaultAdapter();
		$dockindids = $db->select()->from(	// 指定の日時が範囲内にある期間ID
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'))
				->where("dockinds.shiftclass = ?", $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$dockindids->orWhere("dockinds.shiftclass = ?", $shiftclasses[1]);
		}
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shift' => $this->_name), "*")
	
				->where('shift.m_dockind_id IN (?)', $dockindids)
				->where('shift.dayno = ?', $dayno);
	
		//配列の最初にポインタを移動
		reset($termids);
		//このときのキーを取得
		$array_first_info = each($termids);
		$array_first_key = $array_first_info["key"];
	
		//配列の最後にポインタを移動
		end($termids);
		//このときのキーを取得
		$array_last_info = each($termids);
		$array_last_key = $array_last_info["key"];
	
		foreach($termids as $key => $value)
		{
			if($key === $array_first_key)
				$select->where('(shift.m_term_id = ?', $value);
			elseif($key === $array_last_key)
				$select->orWhere('shift.m_term_id = ?)', $value);
			else
				$select->orWhere('shift.m_term_id = ?', $value);
		}
	
		$select->order(array('shift.id'));
	
		return $this->fetchAll($select);
	}


	// シフトを取得（m_term_id, m_dockind_id, m_place_id、daynoでユニークなもの）取得
	public function selectShift($m_term_id, $m_dockind_id, $m_place_id, $dayno)
	{
		$select = $this->select();
		$select->where('m_term_id = ?', $m_term_id);
		$select->where('m_dockind_id = ?', $m_dockind_id);
		$select->where('m_place_id = ?', $m_place_id);
		$select->where('dayno = ?', $dayno);
		return $this->fetchRow($select);
	}

	// daynoの件数取得
	public function countDayno($termid)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shift' => $this->_name),
				array('shift.dayno'))
				->where('shift.m_term_id = ?', $termid)
				->group('shift.dayno');

		return count($this->fetchAll($select));
	}
}

