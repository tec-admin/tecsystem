<?php

require_once('BaseTModels.class.php');

class Class_Model_TShiftlimits extends BaseTModels
{
	/**
	 * @var string 対応テーブル名
	 */
	const TABLE_NAME = 't_shiftlimits';
	protected $_name   = Class_Model_TShiftlimits::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TShiftlimits::TABLE_NAME;

		return array(
				$prefix . '_reservationdate' => 'reservationdate',
				$prefix . '_m_shift_id' => 'm_shift_id',
				$prefix . '_reservelimit' => 'reservelimit',
				$prefix . '_limitname' => 'limitname',

				//$prefix . '_createdate' => 'createdate',
				//$prefix . '_creator' => 'creator',
				//$prefix . '_lastupdate' => 'lastupdate',
				//$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	public static function fieldArrayForInsert($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TShiftlimits::TABLE_NAME;
	
		return array(
				$prefix . '_reservationdate' => 'reservationdate',
				$prefix . '_m_shift_id' => 'm_shift_id',
				$prefix . '_reservelimit' => 'reservelimit',
				$prefix . '_limitname' => 'limitname',
	
				$prefix . '_createdate' => 'createdate',
				$prefix . '_creator' => 'creator',
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// 学期挿入時にシフトを更新
	public function insertShiftlimitsFromTerm($termid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		// 現在の受入数データのm_shift_idのみを挿入された学期IDに変更して取得
		$subselect = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name), array('shiftlimits.reservationdate', 'shiftlimits.reservelimit'))
		->join(
				array('shifts' => 'm_shifts'),
				'shiftlimits.m_shift_id = shifts.id',
				array('termid' => '(' . $termid . ')')
		)
		->join(
				array('original' => 'm_shifts'),
				new Zend_Db_Expr("$termid = original.m_term_id AND shifts.m_dockind_id = original.m_dockind_id AND shifts.m_place_id = original.m_place_id AND shifts.dayno = original.dayno"),
				array('original.id')
		)
		;
	
		// 挿入するデータを整形する
		$select = $db->select()->from(
				array('sub'=> $subselect),
				array(
						'reservationdate'	=> 'sub.reservationdate',
						'm_shift_id'		=> 'sub.id',
						'reservelimit'		=> 'sub.reservelimit',
				)
		)
		->group(new Zend_Db_Expr("1,2,3,4,5,6"))
		;
	
		$select->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
		$select->columns(array('createdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
	
		$this->insertSelect('t_shiftlimits', $select, Class_Model_TShiftlimits::fieldArrayForInsert());
	}
	
	// 文書挿入時にシフトを更新
	public function insertShiftlimitsFromDockind($dockindid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		// 現在の受入数データのm_shift_idのみを挿入された文書IDに変更して取得
		$subselect = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name), array('shiftlimits.reservationdate', 'shiftlimits.reservelimit'))
		->join(
				array('shifts' => 'm_shifts'),
				'shiftlimits.m_shift_id = shifts.id',
				array('dockindid' => '(' . $dockindid . ')')
		)
		->join(
				array('original' => 'm_shifts'),
				new Zend_Db_Expr("shifts.m_term_id = original.m_term_id AND $dockindid = original.m_dockind_id AND shifts.m_place_id = original.m_place_id AND shifts.dayno = original.dayno"),
				array('original.id')
		)
		;
	
		// 挿入するデータを整形する
		$select = $db->select()->from(
				array('sub'=> $subselect),
				array(
						'reservationdate'	=> 'sub.reservationdate',
						'm_shift_id'		=> 'sub.id',
						'reservelimit'		=> 'sub.reservelimit',
				)
		)
		->group(new Zend_Db_Expr("1,2,3,4,5,6"))
		;
	
		$select->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
		$select->columns(array('createdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
	
		$this->insertSelect('t_shiftlimits', $select, Class_Model_TShiftlimits::fieldArrayForInsert());
	}
	
	// 場所挿入時にシフトを更新
	public function insertShiftlimitsFromPlace($placeid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		// 現在の受入数データのm_shift_idのみを挿入された場所IDに変更して取得
		$subselect = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name), array('shiftlimits.reservationdate', 'shiftlimits.reservelimit'))
		->join(
				array('shifts' => 'm_shifts'),
				'shiftlimits.m_shift_id = shifts.id',
				array('placeid' => '(' . $placeid . ')')
		)
		->join(
				array('original' => 'm_shifts'),
				new Zend_Db_Expr("shifts.m_term_id = original.m_term_id AND shifts.m_dockind_id = original.m_dockind_id AND $placeid = original.m_place_id AND shifts.dayno = original.dayno"),
				array('original.id')
		)
		;
	
		// 挿入するデータを整形する
		$select = $db->select()->from(
				array('sub'=> $subselect),
				array(
						'reservationdate'	=> 'sub.reservationdate',
						'm_shift_id'		=> 'sub.id',
						'reservelimit'		=> 'sub.reservelimit',
				)
		)
		->group(new Zend_Db_Expr("1,2,3,4,5,6"))
		;
	
		$select->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
		$select->columns(array('createdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		
		$this->insertSelect('t_shiftlimits', $select, Class_Model_TShiftlimits::fieldArrayForInsert());
	}
	
	// 学期IDで削除
	public function deleteFromTermId($termid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect = $db->select()->from(
				array('shifts' => 'm_shifts'),
				array('shifts.id')
		)
		->where('shifts.m_term_id = ?', $termid)
		;
	
		$where = $this->getAdapter()->quoteInto('m_shift_id IN (?)', $subselect);
	
		return $this->delete($where);
	}
	
	// 文書IDで削除
	public function deleteFromDockindId($dockindid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect = $db->select()->from(
				array('shifts' => 'm_shifts'),
				array('shifts.id')
		)
		->where('shifts.m_dockind_id = ?', $dockindid)
		;
	
		$where = $this->getAdapter()->quoteInto('m_shift_id IN (?)', $subselect);
	
		return $this->delete($where);
	}
	
	// 場所IDで削除
	public function deleteFromPlaceId($placeid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subselect = $db->select()->from(
				array('shifts' => 'm_shifts'),
				array('shifts.id')
		)
		->where('shifts.m_place_id = ?', $placeid)
		;
		
		$where = $this->getAdapter()->quoteInto('m_shift_id IN (?)', $subselect);
		
		return $this->delete($where);
	}

	public function selectShift($m_shift_id, $reservationdate)
	{
		$select = $this->select();
		$select->where('m_shift_id = ?', $m_shift_id);
		$select->where('reservationdate = ?', $reservationdate);
		
		return $this->fetchAll($select);
	}
	
	public function selectShiftForUpdate($m_shift_id, $reservationdate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.m_term_id', 'shifts.m_place_id', 'shifts.dayno'))
				->where('shifts.id = ?', $m_shift_id)
				;
		
		$subShift = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('(shifts.m_term_id, shifts.m_place_id, shifts.dayno) IN (?)', $subselect)
				;
		
		$select = $this->select()->forUpdate();
		$select->where('m_shift_id IN (?)', $subShift);
		$select->where('reservationdate = ?', $reservationdate);
		
		return $this->fetchAll($select);
	}
	
	// 関大：場所・シフト連番・日付で取得
	public function selectShiftKwl($m_place_id, $dayno, $reservationdate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subTerm = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.id'))
				->where('terms.startdate <= ?', $reservationdate)
				->where('terms.enddate >= ?', $reservationdate)
				;
		
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_term_id IN (?)', $subTerm)
				->where('shifts.m_place_id = ?', $m_place_id)
				->where('shifts.dayno = ?', $dayno)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shiftlimits' => $this->_name), "*")
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect)
				->where('shiftlimits.reservationdate = ?', $reservationdate)
				;
	
		return $this->fetchAll($select);
	}
	
	// 津大：シフト種別・シフト連番・日付で取得
	public function selectShiftTwc($m_dockind_id, $dayno, $reservationdate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// id降順だと、アカデミック(shiftclass=1)の場合'1,2'ではなく'1'が返る
		// 予約数は'1,2'、受入数・担当者数は'1'で取得する必要がある
		// ※シフト種別が変更される予定はなし
		$shiftclass = Class_Model_MLShiftclasses::getShiftclassFromDockindId($m_dockind_id, 'id desc');
		
		$shiftclasses = explode(",", $shiftclass['m_shiftclass_id']);
		
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'))
				->where('dockinds.shiftclass = ?', $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$subselect1->orWhere('dockinds.shiftclass = ?', $shiftclasses[1]);
		}
		
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_dockind_id IN ?', $subselect1)
				->where('shifts.dayno = ?', $dayno)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shiftlimits' => $this->_name), "*")
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
				->where('shiftlimits.reservationdate = ?', $reservationdate)
				;
	
		return $this->fetchAll($select);
	}

	// シフトマスタIDで取得
	public function selectFromShiftId($m_shift_id)
	{
		$select = $this->select();
		$select->where('m_shift_id = ?', $m_shift_id);
		return $this->fetchAll($select);
	}

	// 指定の期間内をすべて取得
	public function selectFromTerm($startdate, $enddate)
	{
		$select = $this->select();

		$select->where("'" . $startdate . "' <= reservationdate AND " . "'" . $enddate . "' >= reservationdate");

		return $this->fetchAll($select);
	}

	// 指定の期間、キャンパス、シフト内連番、曜日に属する受入数を取得
	public function selectLimitFromToday($termid, $campusid, $dayno, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subselect1 = $db->select()->from(
				array('places'=>'m_places'),
				array('places.id'))
				->where('places.m_campus_id = ?', $campusid);
		
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))

				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_place_id IN (?)', $subselect1)
				->where('shifts.m_dockind_id = 1')
				->where('shifts.dayno = ?', $dayno)
				;

		$subselectTerm1 = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.startdate'))
				->where('terms.id = ?', $termid)
				;

		$subselectTerm2 = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.enddate'))
				->where('terms.id = ?', $termid)
				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit'))

				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) = ?', $dow)
				->where('reservationdate >= ?', $subselectTerm1)
				->where('reservationdate <= ?', $subselectTerm2)

				->where('shiftlimits.m_shift_id IN (?)', $subselect2)

				->group('shiftlimits.reservelimit')

				//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchRow($select);
	}
	
	// 指定の期間、場所、シフト内連番、曜日に属する受入数を取得
	public function selectLimitFromTodayAndPlaceId($termid, $placeid, $dayno, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_place_id = ?', $placeid)
				->where('shifts.m_dockind_id = 1')
				->where('shifts.dayno = ?', $dayno)
				;
	
		$subselectTerm1 = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.startdate'))
				->where('terms.id = ?', $termid)
				;
	
		$subselectTerm2 = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.enddate'))
				->where('terms.id = ?', $termid)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit'))
	
				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) = ?', $dow)
				->where('reservationdate >= ?', $subselectTerm1)
				->where('reservationdate <= ?', $subselectTerm2)
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
	
				->group('shiftlimits.reservelimit')
	
				//->order(array('staffshifts.createdate DESC'))
		;
	
		return $this->fetchRow($select);
	}
	
	// 津田用、指定の期間、シフト種別、シフト内連番、曜日に属する受入数を取得
	public function selectLimitFromTodayForTwc($termid, $shiftclass, $dayno, $dow)
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
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				->where('shifts.dayno = ?', $dayno)
	
				;
		
		$subselectTerm1 = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.startdate'))
				->where('terms.id = ?', $termid)
				;
		
		$subselectTerm2 = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.enddate'))
				->where('terms.id = ?', $termid)
				;
		
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit'))
	
				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) = ?', $dow)
				->where('reservationdate >= ?', $subselectTerm1)
				->where('reservationdate <= ?', $subselectTerm2)
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
	
				->group('shiftlimits.reservelimit')
	
				//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchRow($select);
	}

	// 関大(旧仕様)：指定の期間、キャンパス、シフト内連番、曜日に属する受入数を取得
// 	public function selectLimitFromTermIdAndCampusId($termid, $campusid)
// 	{
// 		$db = Zend_Db_Table::getDefaultAdapter();

// 		$subselect1 = $db->select()->from(
// 				array('places'=>'m_places'),
// 				array('places.id'))
// 				->where('places.m_campus_id = ?', $campusid);

// 		$subselect2 = $db->select()->from(
// 				array('shifts'=>'m_shifts'),
// 				array('shifts.id'))

// 				->where('shifts.m_term_id = ?', $termid)
// 				->where('shifts.m_place_id IN (?)', $subselect1)
// 				->where('shifts.m_dockind_id = 1')

// 				;

// 		$subselectTerm1 = $db->select()->from(
// 				array('terms'=>'m_terms'),
// 				array('terms.startdate'))
// 				->where('terms.id = ?', $termid)
// 				;

// 		$subselectTerm2 = $db->select()->from(
// 				array('terms'=>'m_terms'),
// 				array('terms.enddate'))
// 				->where('terms.id = ?', $termid)
// 				;

// 		$select = $this->select()->setIntegrityCheck(false)
// 		->from(array('shiftlimits' => $this->_name),
// 				array('shiftlimits.reservelimit', 'EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) AS dow' ))

// 				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
// 				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) >= 1')
// 				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) <= 5')
// 				->where('reservationdate >= ?', $subselectTerm1)
// 				->where('reservationdate <= ?', $subselectTerm2)

// 				->group(array('shiftlimits.reservelimit', 'shiftlimits.reservationdate', 'shifts.dayno'))

// 				->order(array('dow ASC','shifts.dayno ASC'))

// 				->joinLeft(
// 						array('shifts' => 'm_shifts'),
// 						'shiftlimits.m_shift_id = shifts.id',
// 						array('shifts.dayno')
// 				)
// 				;

// 		return $this->fetchAll($select);
// 	}
	
	// 関大(新仕様)：指定の期間、場所、シフト内連番、曜日に属する受入数を取得
	public function selectLimitFromTermIdAndPlaceId($termid, $placeid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_place_id = ?', $placeid)
				->where('shifts.m_dockind_id = 1')
	
				;
	
		$subselectTerm1 = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.startdate'))
				->where('terms.id = ?', $termid)
				;
	
		$subselectTerm2 = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.enddate'))
				->where('terms.id = ?', $termid)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit', 'EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) AS dow' ))
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) >= 1')
				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) <= 5')
				->where('reservationdate >= ?', $subselectTerm1)
				->where('reservationdate <= ?', $subselectTerm2)
	
				->group(array('shiftlimits.reservelimit', 'shiftlimits.reservationdate', 'shifts.dayno'))
	
				->order(array('dow ASC','shifts.dayno ASC'))
	
				->joinLeft(
						array('shifts' => 'm_shifts'),
						'shiftlimits.m_shift_id = shifts.id',
						array('shifts.dayno')
				)
				;
	
		return $this->fetchAll($select);
	}
	
	// 津田：指定の期間、キャンパス、シフト内連番、曜日に属する受入数を取得
	public function selectLimitFromTermIdAndShiftclass($termid, $shiftclass)
	{
		$shiftclasses = explode(",", $shiftclass);
		
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		
		if($shiftclasses[0] != 9)
			$subselect1->where("dockinds.shiftclass = ?", $shiftclasses[0]);
		
		if(count($shiftclasses) > 1)
		{
			$subselect1->orWhere("dockinds.shiftclass = ?", $shiftclasses[1]);
		}
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				;
	
		$subselectTerm1 = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.startdate'))
				->where('terms.id = ?', $termid)
				;
	
		$subselectTerm2 = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.enddate'))
				->where('terms.id = ?', $termid)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit', 'EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) AS dow' ))
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) >= 1')
				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) <= 5')
				->where('reservationdate >= ?', $subselectTerm1)
				->where('reservationdate <= ?', $subselectTerm2)
	
				->group(array('shiftlimits.reservelimit', 'shiftlimits.reservationdate', 'shifts.dayno'))
	
				->order(array('dow ASC', 'shifts.dayno ASC', 'shiftlimits.reservelimit DESC'))
	
				->joinLeft(
						array('shifts' => 'm_shifts'),
						'shiftlimits.m_shift_id = shifts.id',
						array('shifts.dayno')
				)
				;
		
		return $this->fetchAll($select);
	}
	
	// 指定の期間、場所、シフト内連番、曜日に属する受入数を取得
	public function selectLimitFromPlaceIdAndWeektop($termid, $placeid, $weektop)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
		// termidを条件に入れると、学期の境目のデータは表示されなくなる
		->where('shifts.m_term_id = ?', $termid)
		->where('shifts.m_place_id = ?', $placeid)
		->where('shifts.m_dockind_id = 1')
	
		;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit', 'EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) AS dow' ))
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) >= 1')
				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) <= 5')
				->where('reservationdate >= ?', $weektop)
				->where('reservationdate <= (date ? + 4)', $weektop)
	
				->group(array('shiftlimits.reservelimit', 'shiftlimits.reservationdate', 'shifts.dayno'))
	
				->order(array('dow ASC','shifts.dayno ASC'))
	
				->joinLeft(
						array('shifts' => 'm_shifts'),
						'shiftlimits.m_shift_id = shifts.id',
						array('shifts.dayno')
				)
				;
	
		return $this->fetchAll($select);
	}
	
	// 津田：指定の期間、キャンパス、シフト内連番、曜日に属する受入数を取得
	public function selectLimitFromShiftclassAndWeektop($termid, $shiftclass, $weektop)
	{
		$shiftclasses = explode(",", $shiftclass);
		
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		
		if($shiftclasses[0] != 9)
			$subselect1->where("dockinds.shiftclass = ?", $shiftclasses[0]);
		
		if(count($shiftclasses) > 1)
		{
			$subselect1->orWhere("dockinds.shiftclass = ?", $shiftclasses[1]);
		}
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
		// termidを条件に入れると、学期の境目のデータは表示されなくなる
		->where('shifts.m_term_id = ?', $termid)
		->where('shifts.m_dockind_id IN (?)', $subselect1)
	
		;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit', 'EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) AS dow' ))
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) >= 1')
				->where('EXTRACT(DOW FROM to_timestamp(CAST(shiftlimits.reservationdate AS TEXT),\'yyyy/mm/dd\')) <= 5')
				->where('reservationdate >= ?', $weektop)
				->where('reservationdate <= (date ? + 4)', $weektop)
	
				->group(array('shiftlimits.reservelimit', 'shiftlimits.reservationdate', 'shifts.dayno'))
	
				->order(array('dow ASC','shifts.dayno ASC', 'shiftlimits.reservelimit DESC'))
	
				->joinLeft(
						array('shifts' => 'm_shifts'),
						'shiftlimits.m_shift_id = shifts.id',
						array('shifts.dayno')
				)
				;
		
		return $this->fetchAll($select);
	}

	// 関大：指定の期間、場所、シフト内連番、日付に属する受入数を取得
	public function selectLimitFromReservationdate($termid, $placeid, $dayno, $reservationdate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))

				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_place_id = ?', $placeid)
				->where('shifts.m_dockind_id = 1')
				->where('shifts.dayno = ?', $dayno)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit'))

				->where('shiftlimits.reservationdate = ?', $reservationdate)

				->where('shiftlimits.m_shift_id IN (?)', $subselect2)

				->group('shiftlimits.reservelimit')

				//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchRow($select);
	}
	
	// 津田：指定の期間、シフト種別、シフト内連番、日付に属する受入数を取得
	public function selectLimitFromReservationdateForTwc($termid, $shiftclass, $dayno, $reservationdate)
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
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				->where('shifts.dayno = ?', $dayno)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit'))
	
				->where('shiftlimits.reservationdate = ?', $reservationdate)
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
	
				->group('shiftlimits.reservelimit')
	
				//->order(array('staffshifts.createdate DESC'))
		;
	
		return $this->fetchRow($select);
	}
	
	// 関大：指定の期間、場所、シフト内連番、日付に属する受入数を取得
	public function selectLimitFromWeektopAndDow($termid, $placeid, $dayno, $weektop, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_place_id = ?', $placeid)
				->where('shifts.m_dockind_id = 1')
				->where('shifts.dayno = ?', $dayno)
	
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit'))
	
				->where('shiftlimits.reservationdate = ?',  new Zend_Db_Expr("(date '$weektop' + $dow - 1)"))
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
	
				->group('shiftlimits.reservelimit')
	
				//->order(array('staffshifts.createdate DESC'))
		;
	
		return $this->fetchRow($select);
	}
	
	// 津田：指定の期間、キャンパス、シフト内連番、日付に属する受入数を取得
	public function selectLimitFromWeektopAndDowForTwc($termid, $shiftclass, $dayno, $weektop, $dow)
	{
		$shiftclasses = explode(",", $shiftclass);
		
		$db = Zend_Db_Table::getDefaultAdapter();
	
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
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				->where('shifts.dayno = ?', $dayno)
	
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('shiftlimits' => $this->_name),
				array('shiftlimits.reservelimit'))
	
				->where('shiftlimits.reservationdate = ?',  new Zend_Db_Expr("(date '$weektop' + $dow - 1)"))
	
				->where('shiftlimits.m_shift_id IN (?)', $subselect2)
	
				->group('shiftlimits.reservelimit')
	
				//->order(array('staffshifts.createdate DESC'))
		;
	
		return $this->fetchRow($select);
	}
	
	// 関大用(旧仕様：スタッフ側では使用)：学期単位での受入数増減
	public function updateFromTermIdAndCampusIdAndDaynoAndDow($termid, $campusid, $dayno, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		if (!is_array($params))
			$params = array();

		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

		$subSelectPlaces = $db->select()
		->from('m_places', array('id' => 'id'))
		->where('m_campus_id = ?', $campusid);

		$subSelectShiftId = $db->select()
		->from('m_shifts', array('id' => 'id'))
		->where('m_term_id = ?', $termid)
		->where('m_place_id IN (?)', $subSelectPlaces)
		->where('dayno = ?', $dayno);

		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto("m_shift_id IN (?)", $subSelectShiftId) . $this->getAdapter()->quoteInto("AND EXTRACT(DOW FROM to_timestamp(CAST(reservationdate AS TEXT),'yyyy/mm/dd')) = ?", $dow)
		);
	}
	
	// 関大用(新仕様)：学期単位での受入数増減
	public function updateFromTermIdAndPlaceIdAndDaynoAndDow($termid, $placeid, $dayno, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectShiftId = $db->select()
		->from('m_shifts', array('id' => 'id'))
		->where('m_term_id = ?', $termid)
		->where('m_place_id = ?', $placeid)
		->where('dayno = ?', $dayno);
		
		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto("m_shift_id IN (?)", $subSelectShiftId) . $this->getAdapter()->quoteInto("AND EXTRACT(DOW FROM to_timestamp(CAST(reservationdate AS TEXT),'yyyy/mm/dd')) = ?", $dow)
		);
	}

	// 津田用：学期単位での受入数増減
	public function updateFromTermIdAndShiftclassAndDaynoAndDow($termid, $shiftclass, $dayno, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$shiftclasses = explode(",", $shiftclass);

		if (!is_array($params))
			$params = array();

		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

		$subSelectDockinds = $db->select()
		->from('m_dockinds', array('id' => 'id'))
		->where('m_dockinds.shiftclass = ?', $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$subSelectDockinds->orWhere("m_dockinds.shiftclass = ?", $shiftclasses[1]);
		}

		$subSelectShiftId = $db->select()
		->from('m_shifts', array('id' => 'id'))
		->where('m_term_id = ?', $termid)
		->where('m_dockind_id IN (?)', $subSelectDockinds)
		->where('dayno = ?', $dayno);

		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto("m_shift_id IN (?)", $subSelectShiftId) . $this->getAdapter()->quoteInto("AND EXTRACT(DOW FROM to_timestamp(CAST(reservationdate AS TEXT),'yyyy/mm/dd')) = ?", $dow)
		);
	}

	// 関大用(旧仕様)：日付単位での受入数データを更新
// 	public function updateFromTermIdAndCampusIdAndDaynoAndReservationdate($termid, $campusid, $dayno, $reservationdate, $params)
// 	{
// 		$db = Zend_Db_Table::getDefaultAdapter();

// 		if (!is_array($params))
// 			$params = array();

// 		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
// 		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
// 			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

// 		$subSelectPlaces = $db->select()
// 		->from('m_places', array('id' => 'id'))
// 		->where('m_campus_id = ?', $campusid);

// 		$subSelectShiftId = $db->select()
// 		->from('m_shifts', array('id' => 'id'))
// 		->where('m_term_id = ?', $termid)
// 		->where('m_place_id IN (?)', $subSelectPlaces)
// 		->where('dayno = ?', $dayno);

// 		$count = $this->update(
// 				$params,
// 				$this->getAdapter()->quoteInto("m_shift_id IN (?)", $subSelectShiftId) . $this->getAdapter()->quoteInto("AND reservationdate = ?", $reservationdate)
// 		);
// 	}
	
	// 関大用(新仕様)：日付単位での受入数データを更新
	public function updateFromTermIdAndPlaceIdAndDaynoAndReservationdate($termid, $placeid, $dayno, $reservationdate, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectShiftId = $db->select()
		->from('m_shifts', array('id' => 'id'))
		->where('m_term_id = ?', $termid)
		->where('m_place_id = ?', $placeid)
		->where('dayno = ?', $dayno);
	
		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto("m_shift_id IN (?)", $subSelectShiftId) . $this->getAdapter()->quoteInto("AND reservationdate = ?", $reservationdate)
		);
	}
	
	// 津田：日付単位での受入数データを更新
	public function updateFromTermIdAndShiftclassAndDaynoAndReservationdate($termid, $shiftclass, $dayno, $reservationdate, $params)
	{
		$shiftclasses = explode(",", $shiftclass);
		
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectDockinds = $db->select()
		->from('m_dockinds', array('id' => 'id'))
		->where('shiftclass = ?', $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$subSelectDockinds->orWhere("shiftclass = ?", $shiftclasses[1]);
		}
	
		$subSelectShiftId = $db->select()
		->from('m_shifts', array('id' => 'id'))
		->where('m_term_id = ?', $termid)
		->where('m_dockind_id IN (?)', $subSelectDockinds)
		->where('dayno = ?', $dayno);
	
		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto("m_shift_id IN (?)", $subSelectShiftId) . $this->getAdapter()->quoteInto("AND reservationdate = ?", $reservationdate)
		);
	}
	
	// 関大用(旧仕様)：日付単位での受入数データを更新
// 	public function updateFromTermIdAndCampusIdAndDaynoAndWeektopAndDow($termid, $campusid, $dayno, $weektop, $dow, $params)
// 	{
// 		$db = Zend_Db_Table::getDefaultAdapter();
	
// 		if (!is_array($params))
// 			$params = array();
	
// 		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
// 		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
// 			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
// 		$subSelectPlaces = $db->select()
// 		->from('m_places', array('id' => 'id'))
// 		->where('m_campus_id = ?', $campusid);
	
// 		$subSelectShiftId = $db->select()
// 		->from('m_shifts', array('id' => 'id'))
// 		->where('m_term_id = ?', $termid)
// 		->where('m_place_id IN (?)', $subSelectPlaces)
// 		->where('dayno = ?', $dayno);
	
// 		$count = $this->update(
// 				$params,
// 				$this->getAdapter()->quoteInto("m_shift_id IN (?)", $subSelectShiftId) . $this->getAdapter()->quoteInto("AND reservationdate = ?", new Zend_Db_Expr("(date '$weektop' + $dow - 1)"))
// 		);
// 	}
	
	// 関大用(新仕様)：日付単位での受入数データを更新
	public function updateFromTermIdAndPlaceIdAndDaynoAndWeektopAndDow($termid, $placeid, $dayno, $weektop, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

		$subSelectShiftId = $db->select()
		->from('m_shifts', array('id' => 'id'))
		->where('m_term_id = ?', $termid)
		->where('m_place_id = ?', $placeid)
		->where('dayno = ?', $dayno);
	
		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto("m_shift_id IN (?)", $subSelectShiftId) . $this->getAdapter()->quoteInto("AND reservationdate = ?", new Zend_Db_Expr("(date '$weektop' + $dow - 1)"))
		);
	}
	
	// 津田：日付単位での受入数データを更新
	public function updateFromTermIdAndShiftclassAndDaynoAndWeektopAndDow($termid, $shiftclass, $dayno, $weektop, $dow, $params)
	{
		$shiftclasses = explode(",", $shiftclass);
		
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectDockinds = $db->select()
		->from('m_dockinds', array('id' => 'id'))
		->where('shiftclass = ?', $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$subSelectDockinds->orWhere("shiftclass = ?", $shiftclasses[1]);
		}
	
		$subSelectShiftId = $db->select()
		->from('m_shifts', array('id' => 'id'))
		->where('m_term_id = ?', $termid)
		->where('m_dockind_id IN (?)', $subSelectDockinds)
		->where('dayno = ?', $dayno);
	
		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto("m_shift_id IN (?)", $subSelectShiftId) . $this->getAdapter()->quoteInto("AND reservationdate = ?", new Zend_Db_Expr("(date '$weektop' + $dow - 1)"))
		);
	}

	// 関大用(旧仕様)：学期単位での受入数データを新規挿入
// 	public function insertFromTermIdAndCampusIdAndDaynoAndDow($termid, $campusid, $dayno, $dow, $params)
// 	{
// 		$db = Zend_Db_Table::getDefaultAdapter();

// 		if (!is_array($params))
// 			$params = array();

// 		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
// 		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

// 		$subSelectTermsStart = $db->select()
// 		->from('m_terms', array('startdate' => 'startdate'))
// 		->where('id = ?', $termid);

// 		$subSelectTermsEnd = $db->select()
// 		->from('m_terms', array('enddate' => 'enddate'))
// 		->where('id = ?', $termid);

// 		$subSelectPlaces = $db->select()
// 		->from('m_places', array('id' => 'id'))
// 		->where('m_campus_id = ?', $campusid);

// 		$subSelectShiftId = $db->select()
// 		->from('m_shifts', array('m_shift_id' => 'id'))
// 		->join( array('insert_date' => new Zend_Db_Expr("(SELECT (" . $subSelectTermsStart . ") + i as d from generate_series(0,190) as i)"))
// 				, '1=1'
// 				, array('reservationdate' => 'insert_date.d'))
// 				->where('insert_date.d <= ?',  $subSelectTermsEnd)
// 				->where('EXTRACT(DOW FROM to_timestamp(CAST(insert_date.d AS TEXT),\'yyyy/mm/dd\')) = ?', $dow)
// 				->where('m_term_id = ?', $termid)
// 				->where('m_place_id IN (?)', $subSelectPlaces)
// 				->where('dayno = ?', $dayno);

// 		$subSelectShiftId->columns(array('reservelimit' => new Zend_Db_Expr("'" . $params["reservelimit"] . "'")));
// 		$subSelectShiftId->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
// 		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
// 		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
// 		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
// 		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));


// 		$this->insertSelect('t_shiftlimits', $subSelectShiftId, $this::fieldArrayForInsert());
// 	}
	
	// 関大用(新仕様)：学期単位での受入数データを新規挿入
	public function insertFromTermIdAndCampusIdAndDaynoAndDow($termid, $campusid, $dayno, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectTermsStart = $db->select()
		->from('m_terms', array('startdate' => 'startdate'))
		->where('id = ?', $termid);
	
		$subSelectTermsEnd = $db->select()
		->from('m_terms', array('enddate' => 'enddate'))
		->where('id = ?', $termid);
		
		$subSelectPlaceId = $db->select()
		->from('m_places', array('id' => 'id'))
		->where('m_campus_id = ?', $campusid);
	
		$subSelectShiftId = $db->select()
		->from('m_shifts', '')
		->join( array('insert_date' => new Zend_Db_Expr("(SELECT (" . $subSelectTermsStart . ") + i as d from generate_series(0,200) as i)"))
				, '1=1'
				, array('reservationdate' => 'insert_date.d'))
				->where('insert_date.d <= ?',  $subSelectTermsEnd)
				->where('EXTRACT(DOW FROM to_timestamp(CAST(insert_date.d AS TEXT),\'yyyy/mm/dd\')) = ?', $dow)
				->where('m_term_id = ?', $termid)
				->where('m_place_id IN (?)', $subSelectPlaceId)
				->where('dayno = ?', $dayno);
		
		$subSelectShiftId->columns(array('m_shift_id' => 'id'));
		$subSelectShiftId->columns(array('reservelimit' => new Zend_Db_Expr("'" . $params["reservelimit"] . "'")));
		$subSelectShiftId->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));
	
	
		$this->insertSelect('t_shiftlimits', $subSelectShiftId, $this::fieldArrayForInsert());
	}
	
	public function deleteFromTermIdAndPlaceIdAndDaynoAndDow($termid, $placeid, $dayno, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subSelectShiftId = $db->select()
			->from('m_shifts', array('id'))
				->where('m_term_id = ?', $termid)
				->where('m_place_id = ?', $placeid)
				->where('dayno = ?', $dayno)
				;
		
		$where = $this->getAdapter()->quoteInto('m_shift_id IN (?)', $subSelectShiftId)
					. $this->getAdapter()->quoteInto(' AND EXTRACT(DOW FROM to_timestamp(CAST(reservationdate AS TEXT),\'yyyy/mm/dd\')) = ?', $dow);
	
		return $this->delete($where);
	}
	
	// 関大用(新仕様)：学期単位での受入数データを新規挿入
	public function insertFromTermIdAndPlaceIdAndDaynoAndDow($termid, $placeid, $dayno, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectTermsStart = $db->select()
		->from('m_terms', array('startdate' => 'startdate'))
		->where('id = ?', $termid);
	
		$subSelectTermsEnd = $db->select()
		->from('m_terms', array('enddate' => 'enddate'))
		->where('id = ?', $termid);
	
		$subSelectShiftId = $db->select()
		->from('m_shifts', '')
		->join( array('insert_date' => new Zend_Db_Expr("(SELECT (" . $subSelectTermsStart . ") + i as d from generate_series(0,190) as i)"))
				, '1=1'
				, array('reservationdate' => 'insert_date.d'))
				->where('insert_date.d <= ?',  $subSelectTermsEnd)
				->where('EXTRACT(DOW FROM to_timestamp(CAST(insert_date.d AS TEXT),\'yyyy/mm/dd\')) = ?', $dow)
				->where('m_term_id = ?', $termid)
				->where('m_place_id = ?', $placeid)
				->where('dayno = ?', $dayno);
		
		$subSelectShiftId->columns(array('m_shift_id' => 'id'));
		$subSelectShiftId->columns(array('reservelimit' => new Zend_Db_Expr("'" . $params["reservelimit"] . "'")));
		$subSelectShiftId->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));
	
		$this->insertSelect('t_shiftlimits', $subSelectShiftId, $this::fieldArrayForInsert());
	}
	
	// 津田用：学期単位での受入数データを新規挿入
	public function insertFromTermIdAndShiftclassAndDaynoAndDow($termid, $shiftclass, $dayno, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$shiftclasses = explode(",", $shiftclass);
	
		if (!is_array($params))
			$params = array();
	
		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectTermsStart = $db->select()
		->from('m_terms', array('startdate' => 'startdate'))
		->where('id = ?', $termid);
	
		$subSelectTermsEnd = $db->select()
		->from('m_terms', array('enddate' => 'enddate'))
		->where('id = ?', $termid);
	
		$subSelectDockinds = $db->select()
		->from('m_dockinds', array('id' => 'id'))
		->where('shiftclass = ?', $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$subSelectDockinds->orWhere("shiftclass = ?", $shiftclasses[1]);
		}
	
		$subSelectShiftId = $db->select()
		->from('m_shifts', '')
		->join( array('insert_date' => new Zend_Db_Expr("(SELECT (" . $subSelectTermsStart . ") + i as d from generate_series(0,190) as i)"))
				, '1=1'
				, array('reservationdate' => 'insert_date.d'))
				->where('insert_date.d <= ?',  $subSelectTermsEnd)
				->where('EXTRACT(DOW FROM to_timestamp(CAST(insert_date.d AS TEXT),\'yyyy/mm/dd\')) = ?', $dow)
				->where('m_term_id = ?', $termid)
				->where('m_dockind_id IN (?)', $subSelectDockinds)
				->where('dayno = ?', $dayno);
		
		$subSelectShiftId->columns(array('m_shift_id' => 'id'));
		$subSelectShiftId->columns(array('reservelimit' => new Zend_Db_Expr("'" . $params["reservelimit"] . "'")));
		$subSelectShiftId->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));
		
		$this->insertSelect('t_shiftlimits', $subSelectShiftId, $this::fieldArrayForInsert());
	}

	// 関大用(旧仕様)：日付単位での受入数データを新規挿入
// 	public function insertFromTermIdAndCampusIdAndDaynoAndReservationdate($termid, $campusid, $dayno, $reservationdate, $params)
// 	{
// 		$db = Zend_Db_Table::getDefaultAdapter();

// 		if (!is_array($params))
// 			$params = array();

// 		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
// 		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

// 		$subSelectDockinds = $db->select()
// 		->from('m_places', array('id' => 'id'))
// 		->where('m_campus_id = ?', $campusid);

// 		$subSelectShiftId = $db->select()
// 		->from('m_shifts', array('m_shift_id' => 'id'))
// 		->where('m_term_id = ?', $termid)
// 		->where('m_place_id IN (?)', $subSelectDockinds)
// 		->where('dayno = ?', $dayno);

// 		$subSelectShiftId->columns(array('reservationdate' => new Zend_Db_Expr("date '" . $reservationdate . "'")));
// 		$subSelectShiftId->columns(array('reservelimit' => new Zend_Db_Expr("'" . $params["reservelimit"] . "'")));
// 		$subSelectShiftId->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
// 		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
// 		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
// 		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
// 		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));

// 		$this->insertSelect('t_shiftlimits', $subSelectShiftId, $this::fieldArrayForInsert());
// 	}

	public function deleteFromTermIdAndPlaceIdAndDaynoAndReservationdate($termid, $placeid, $dayno, $reservationdate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subSelectShiftId = $db->select()
		->from('m_shifts', array('id'))
		->where('m_term_id = ?', $termid)
		->where('m_place_id = ?', $placeid)
		->where('dayno = ?', $dayno)
		;
	
		$where = $this->getAdapter()->quoteInto('m_shift_id IN (?)', $subSelectShiftId)
		. $this->getAdapter()->quoteInto(' AND reservationdate = ?', $reservationdate);
	
		return $this->delete($where);
	}
	
	// 関大用(新仕様)：日付単位での受入数データを新規挿入
	public function insertFromTermIdAndPlaceIdAndDaynoAndReservationdate($termid, $placeid, $dayno, $reservationdate, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectShiftId = $db->select()
		->from('m_shifts', '')
		->where('m_term_id = ?', $termid)
		->where('m_place_id = ?', $placeid)
		->where('dayno = ?', $dayno);
	
		$subSelectShiftId->columns(array('reservationdate' => new Zend_Db_Expr("date '" . $reservationdate . "'")));
		$subSelectShiftId->columns(array('m_shift_id' => 'id'));
		$subSelectShiftId->columns(array('reservelimit' => new Zend_Db_Expr("'" . $params["reservelimit"] . "'")));
		$subSelectShiftId->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));
	
		$this->insertSelect('t_shiftlimits', $subSelectShiftId, $this::fieldArrayForInsert());
	}
	
	// 津田：日付単位での受入数データを新規挿入
	public function insertFromTermIdAndShiftclassAndDaynoAndReservationdate($termid, $shiftclass, $dayno, $reservationdate, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$shiftclasses = explode(",", $shiftclass);
	
		if (!is_array($params))
			$params = array();
	
		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectDockinds = $db->select()
		->from('m_dockinds', array('id' => 'id'))
		->where('shiftclass = ?', $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$subSelectDockinds->orWhere("shiftclass = ?", $shiftclasses[1]);
		}
	
		$subSelectShiftId = $db->select()
		->from('m_shifts', '')
		->where('m_term_id = ?', $termid)
		->where('m_dockind_id IN (?)', $subSelectDockinds)
		->where('dayno = ?', $dayno);
	
		$subSelectShiftId->columns(array('reservationdate' => new Zend_Db_Expr("date '" . $reservationdate . "'")));
		$subSelectShiftId->columns(array('m_shift_id' => 'id'));
		$subSelectShiftId->columns(array('reservelimit' => new Zend_Db_Expr("'" . $params["reservelimit"] . "'")));
		$subSelectShiftId->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));
	
		$this->insertSelect('t_shiftlimits', $subSelectShiftId, $this::fieldArrayForInsert());
	}
	
	// 関大用(旧仕様)：日付単位での受入数データを新規挿入
// 	public function insertFromTermIdAndCampusIdAndDaynoAndWeektopAndDow($termid, $campusid, $dayno, $weektop, $dow, $params)
// 	{
// 		$db = Zend_Db_Table::getDefaultAdapter();
	
// 		if (!is_array($params))
// 			$params = array();
	
// 		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
// 		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
// 		$subSelectDockinds = $db->select()
// 		->from('m_places', array('id' => 'id'))
// 		->where('m_campus_id = ?', $campusid);
	
// 		$subSelectShiftId = $db->select()
// 		->from('m_shifts', array('m_shift_id' => 'id'))
// 		->where('m_term_id = ?', $termid)
// 		->where('m_place_id IN (?)', $subSelectDockinds)
// 		->where('dayno = ?', $dayno);
	
// 		$subSelectShiftId->columns(array('reservationdate' => new Zend_Db_Expr("(date '$weektop' + $dow - 1)")));
// 		$subSelectShiftId->columns(array('reservelimit' => new Zend_Db_Expr("'" . $params["reservelimit"] . "'")));
// 		$subSelectShiftId->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
// 		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
// 		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
// 		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
// 		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));
	
// 		$this->insertSelect('t_shiftlimits', $subSelectShiftId, $this::fieldArrayForInsert());
// 	}
	
	// 関大用(新仕様)：日付単位での受入数データを新規挿入
	public function insertFromTermIdAndPlaceIdAndDaynoAndWeektopAndDow($termid, $placeid, $dayno, $weektop, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectShiftId = $db->select()
		->from('m_shifts', '')
		->where('m_term_id = ?', $termid)
		->where('m_place_id = ?', $placeid)
		->where('dayno = ?', $dayno);
	
		$subSelectShiftId->columns(array('reservationdate' => new Zend_Db_Expr("(date '$weektop' + $dow - 1)")));
		$subSelectShiftId->columns(array('m_shift_id' => 'id'));
		$subSelectShiftId->columns(array('reservelimit' => new Zend_Db_Expr("'" . $params["reservelimit"] . "'")));
		$subSelectShiftId->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));
	
		$this->insertSelect('t_shiftlimits', $subSelectShiftId, $this::fieldArrayForInsert());
	}

	// 津田用：日付単位での受入数データを新規挿入
	public function insertFromTermIdAndShiftclassAndDaynoAndWeektopAndDow($termid, $shiftclass, $dayno, $weektop, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$shiftclasses = explode(",", $shiftclass);

		if (!is_array($params))
			$params = array();

		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

		$subSelectDockinds = $db->select()
		->from('m_dockinds', array('id' => 'id'))
		->where('m_dockinds.shiftclass = ?', $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$subSelectDockinds->orWhere("m_dockinds.shiftclass = ?", $shiftclasses[1]);
		}

		$subSelectShiftId = $db->select()
		->from('m_shifts', '')
		->where('m_term_id = ?', $termid)
		->where('m_dockind_id IN (?)', $subSelectDockinds)
		->where('dayno = ?', $dayno);

		$subSelectShiftId->columns(array('reservationdate' => new Zend_Db_Expr("(date '$weektop' + $dow - 1)")));
		$subSelectShiftId->columns(array('m_shift_id' => 'id'));
		$subSelectShiftId->columns(array('reservelimit' => new Zend_Db_Expr("'" . $params["reservelimit"] . "'")));
		$subSelectShiftId->columns(array('limitname' => new Zend_Db_Expr("'閉室中'")));
		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));

		$this->insertSelect('t_shiftlimits', $subSelectShiftId, $this::fieldArrayForInsert());
	}
}

