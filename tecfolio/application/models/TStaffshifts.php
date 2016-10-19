<?php

require_once('BaseTModels.class.php');

class Class_Model_TStaffshifts extends BaseTModels
{
	/**
	 * @var string 対応テーブル名
	 */
	const TABLE_NAME = 't_staffshifts';
	protected $_name   = Class_Model_TStaffshifts::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TStaffshifts::TABLE_NAME;

		return array(
				$prefix . '_id' => 'id',

				$prefix . '_m_member_id' => 'm_member_id',
				$prefix . '_m_shift_id' => 'm_shift_id',
				$prefix . '_dow' => 'dow',

				$prefix . '_createdate' => 'createdate',
				$prefix . '_creator' => 'creator',
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	public static function fieldArrayForInsert($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TStaffshifts::TABLE_NAME;
	
		return array(
				$prefix . '_m_member_id' => 'm_member_id',
				$prefix . '_m_shift_id' => 'm_shift_id',
				$prefix . '_dow' => 'dow',
	
				$prefix . '_createdate' => 'createdate',
				$prefix . '_creator' => 'creator',
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
		);
	}


	private function setJoinFieldForTwc($select)
	{
		$select
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'staffshifts.m_shift_id = shifts.id',
				Class_Model_MShifts::fieldArray()
		)
		
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				Class_Model_MTimetables::fieldArray()
		)

		->joinLeft(
				array('members' => 'm_members'),
				'staffshifts.m_member_id = members.id',
				Class_Model_MMembers::fieldArrayTwc()
		)
		
		->joinLeft(
				array('dockinds' => 'm_dockinds'),
				'shifts.m_dockind_id = dockinds.id',
				Class_Model_MDockinds::fieldArrayTwc()
		)
		
		->joinLeft(
				array('shiftclasses' => 'm_shiftclasses'),
				'dockinds.shiftclass = shiftclasses.id',
				Class_Model_MShiftclasses::fieldArray()
		)
		;

		return $select;
	}
	
	private function setJoinField($select)
	{
		$select
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'staffshifts.m_shift_id = shifts.id',
				Class_Model_MShifts::fieldArray()
		)
		
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				Class_Model_MTimetables::fieldArray()
		)
	
		->joinLeft(
				array('members' => 'm_members'),
				'staffshifts.m_member_id = members.id',
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
				'staffshifts.m_shift_id = shifts.id',
				Class_Model_MShifts::fieldArray()
		)
	
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				Class_Model_MTimetables::fieldArray()
		)
	
		->joinLeft(
				array('members' => 'm_members'),
				'staffshifts.m_member_id = members.id',
				Class_Model_MMembers::fieldArrayTwc()
		)
		;
	
		return $select;
	}

	// 指定のスタッフシフトを検索
	public function selectStaffshift($m_member_id, $m_shift_id, $dow)
	{
		$select = $this->select();
		$select->where('m_member_id = ?', $m_member_id);
		$select->where('m_shift_id = ?', $m_shift_id);
		$select->where('dow = ?', $dow);
		return $this->fetchRow($select);
	}


	// メンバーIDと期間IDとキャンパスIDで取得
	public function selectFromMemberIdAndTermIdAndCampusId($m_member_id, $termid, $campusid=1, $dockind=1)
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
				->where('shifts.m_dockind_id = ?', $dockind)
				->where('shifts.m_place_id IN (?)', $subselect1)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")

		->where('staffshifts.m_member_id = ?', $m_member_id)

		->where('staffshifts.m_shift_id IN (?)', $subselect2)

		//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchAll($this->setJoinField($select));
	}
	
	public function insertFromMemberIdAndTermIdAndCampusId($m_member_id, $termid, $pre_termid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// 前学期のスタッフIDの学期IDのみを今学期のIDに変更して取得
		$preStaffShift = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.dow'))
		->join(
				array('shifts' => 'm_shifts'),
				'staffshifts.m_shift_id = shifts.id',
				array('shifts.m_dockind_id', 'shifts.m_place_id', 'shifts.dayno')
		)
		->join(
				array('original' => 'm_shifts'),
				new Zend_Db_Expr("shifts.m_dockind_id = original.m_dockind_id AND shifts.m_place_id = original.m_place_id AND shifts.dayno = original.dayno"),
				array('original.id')
		)
		->where('staffshifts.m_member_id = ?', $m_member_id)
		->where('shifts.m_term_id = ?', $pre_termid)
		->where('original.m_term_id = ?', $termid)
		;
		
		// 挿入するデータを整形する
		$select = $db->select()->from(
					array('pre'=> $preStaffShift),
					array(
							'm_member_id'	=> new Zend_Db_Expr("'" . $m_member_id . "'"),
							'm_shift_id'	=> 'pre.id',
							'dow'			=> 'pre.dow',
					)
				)
				;
		
		$select->columns(array('createdate' => new Zend_Db_Expr("'" . Zend_Registry::get('nowdatetime') . "'")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("'" . Zend_Registry::get('nowdatetime') . "'")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		
		$this->insertSelect('t_staffshifts', $select, $this::fieldArrayForInsert());
	}
	
	// 学期挿入時にシフトを更新
	public function insertStaffshiftFromTerm($termid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		// 現在のスタッフシフトのm_shift_idのみを挿入された学期IDに変更して取得
		$subselect = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.dow'))
		->join(
				array('shifts' => 'm_shifts'),
				'staffshifts.m_shift_id = shifts.id',
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
						'm_member_id'	=> 'sub.m_member_id',
						'm_shift_id'	=> 'sub.id',
						'dow'			=> 'sub.dow',
				)
		)
		->group(new Zend_Db_Expr("1,2,3,4,5,6"));
	
		$select->columns(array('createdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
	
		$this->insertSelect('t_staffshifts', $select, Class_Model_TStaffshifts::fieldArrayForInsert());
	}
	
	// 文書挿入時にシフトを更新
	public function insertStaffshiftFromDockind($dockindid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		// 現在のスタッフシフトのm_shift_idのみを挿入された文書IDに変更して取得
		$subselect = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.dow'))
		->join(
				array('shifts' => 'm_shifts'),
				'staffshifts.m_shift_id = shifts.id',
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
						'm_member_id'	=> 'sub.m_member_id',
						'm_shift_id'	=> 'sub.id',
						'dow'			=> 'sub.dow',
				)
		)
		->group(new Zend_Db_Expr("1,2,3,4,5,6"));
	
		$select->columns(array('createdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
	
		$this->insertSelect('t_staffshifts', $select, Class_Model_TStaffshifts::fieldArrayForInsert());
	}

	// 場所挿入時にシフトを更新
	public function insertStaffshiftFromPlace($placeid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		// 現在のスタッフシフトのm_shift_idのみを挿入された場所IDに変更して取得
		$subselect = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.dow'))
		->join(
				array('shifts' => 'm_shifts'),
				'staffshifts.m_shift_id = shifts.id',
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
						'm_member_id'	=> 'sub.m_member_id',
						'm_shift_id'	=> 'sub.id',
						'dow'			=> 'sub.dow',
				)
		)
		->group(new Zend_Db_Expr("1,2,3,4,5,6"));

		$select->columns(array('createdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		
		$this->insertSelect('t_staffshifts', $select, Class_Model_TStaffshifts::fieldArrayForInsert());
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

	// 期間IDとキャンパスIDで取得
	public function selectFromTermIdAndCampusId($termid, $campusid=1)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('places'=>'m_places'),
				array('places.id'));
		if($campusid != 0)
			$subselect1->where('places.m_campus_id = ?', $campusid);

		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))

				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_place_id IN (?)', $subselect1)
				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")

		->where('staffshifts.m_shift_id IN (?)', $subselect2)

		//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 日付期間とキャンパスIDで取得
	public function selectFromYmdAndCampusId($startdate, $campusid=0, $enddate=null)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subTerm = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.id'))
	
				->where('(terms.startdate <= ?', $startdate)
				->where('terms.enddate >= ?)', $startdate)
				->orWhere('(terms.startdate <= ?', $enddate)
				->where('terms.enddate >= ?)', $enddate)
				->orWhere('(terms.startdate >= ?', $startdate)
				->where('terms.enddate <= ?)', $enddate)
				;
	
		$subselect1 = $db->select()->from(
				array('places'=>'m_places'),
				array('places.id'));
		if($campusid != 0)
			$subselect1->where('places.m_campus_id = ?', $campusid);
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id IN (?)', $subTerm)
				->where('shifts.m_place_id IN (?)', $subselect1)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")
	
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
	
		//->order(array('staffshifts.createdate DESC'))
		;
		
		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 期間IDで取得
	public function selectFromTermId($termid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id = ?', $termid)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")
	
		->where('staffshifts.m_shift_id IN (?)', $subselect)
	
		//->order(array('staffshifts.createdate DESC'))
		;
	
		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 期間IDとシフト種別IDで取得
	public function selectFromTermIdAndShiftclass($termid, $shiftclass=1)
	{
		$shiftclasses = explode(",", $shiftclass);
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		if($shiftclass != 9)
			$subselect1->where('dockinds.shiftclass = ?', $shiftclasses[0]);
		
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
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")
	
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
	
		//->order(array('staffshifts.createdate DESC'))
		;
	
		return $this->fetchAll($this->setJoinFieldForTwc($select));
	}

	// 関大：指定の期間、キャンパスに属するスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTermIdAndCampusIdIncludingDetailsPerDay($campusid, $weektop, $num, $memberid=0)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subTerm = $db->select()->from(
				array('terms'=>'m_terms'),
				array('terms.id'))
				->where("terms.startdate <= (date '$weektop' + $num)")
				->where("terms.enddate >= (date '$weektop' + $num)");
		
		$subselect1 = $db->select()->from(
				array('places'=>'m_places'),
				array('places.id'))
				->where('places.m_campus_id = ?', $campusid);
		
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
		
				->where('shifts.m_term_id IN (?)', $subTerm)
				->where('shifts.m_place_id IN (?)', $subselect1)
				;

		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')		// type='0'のshiftidは削除
				->where("shiftdetails.shiftdate = (date '$weektop' + $num)")
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
		// スタッフのシフトカレンダー用に追加
		if($memberid !== 0)
			$subdelete->where('shiftdetails.m_member_id = ?', $memberid);

		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidは登録
				->where("shiftdetails.shiftdate = (date '$weektop' + $num)")
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
		// スタッフのシフトカレンダー用に追加
		if($memberid !== 0)
			$subshiftdate->where('shiftdetails.m_member_id = ?', $memberid);

		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'))
		->where('staffshifts.dow = ?', ($num + 1));

		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")

		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)
		->where('staffshifts.dow = ?', ($num + 1))

		//->order(array('staffshifts.createdate DESC'))
		;
		// スタッフのシフトカレンダー用に追加
		if($memberid !== 0)
			$select->where('staffshifts.m_member_id = ?', $memberid);
		
		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 関大：指定の期間、キャンパスに属するスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTermIdAndCampusIdIncludingDetails($termid, $campusid, $weektop, $memberid=0)
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
				;
	
		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')		// type='0'のshiftidは削除
				->where("shiftdetails.shiftdate >= (date '$weektop')")
				->where("shiftdetails.shiftdate <= (date '$weektop' + 4)")
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
		// スタッフのシフトカレンダー用に追加
		if($memberid !== 0)
			$subdelete->where('shiftdetails.m_member_id = ?', $memberid);
	
		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidは登録
				->where("shiftdetails.shiftdate >= (date '$weektop')")
				->where("shiftdetails.shiftdate <= (date '$weektop' + 4)")
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
		// スタッフのシフトカレンダー用に追加
		if($memberid !== 0)
			$subshiftdate->where('shiftdetails.m_member_id = ?', $memberid);

		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));

		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")

		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)

		//->order(array('staffshifts.createdate DESC'))
		;
		// スタッフのシフトカレンダー用に追加
		if($memberid !== 0)
			$select->where('staffshifts.m_member_id = ?', $memberid);

		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 津田：指定の期間、シフト種別に属するスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTermIdAndShiftclassIncludingDetails($termid, $shiftclass, $weektop)
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
	
		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')		// type='0'のshiftidは削除
				->where("shiftdetails.shiftdate >= (date '$weektop')")
				->where("shiftdetails.shiftdate <= (date '$weektop' + 4)")
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
	
		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidは登録
				->where("shiftdetails.shiftdate >= (date '$weektop')")
				->where("shiftdetails.shiftdate <= (date '$weektop' + 4)")
				;
	
		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));
	
		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")
	
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)
		->order(array('members.shift_roles ASC'));
	
		//->order(array('staffshifts.createdate DESC'))
		;
		
		return $this->fetchAll($this->setJoinFieldForTwc($select));
	}

	// 期間IDとキャンパスIDとメンバーIDで取得
	public function selectFromTermIdAndCampusIdAndMemberId($memberid, $termid, $campusid=1, $dockind=1)
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
				->where('shifts.m_dockind_id = ?', $dockind)
				->where('shifts.m_place_id IN (?)', $subselect1)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")

		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('staffshifts.m_member_id = ?', $memberid)

		//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchAll($this->setJoinField($select));
	}

	// メンバーIDと期間IDとキャンパスIDで取得
	public function selectFromMemberIdAndTermIdAndShiftclass($m_member_id, $termid, $shiftclass)
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$shiftclasses = explode(",", $shiftclass);

		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		if($shiftclasses[0] != 9)
				$subselect1->where('dockinds.shiftclass = ?', $shiftclasses[0]);
		
		if(!empty($shiftclasses[1]))
		{
			$subselect1->orWhere('dockinds.shiftclass = ?', $shiftclasses[1]);
		}

		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))

				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")

		->where('staffshifts.m_member_id = ?', $m_member_id)

		->where('staffshifts.m_shift_id IN (?)', $subselect2)

		//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchAll($this->setJoinField($select));
	}

	// 指定のメンバーID、キャンパスID, シフト時間帯、曜日に属するシフトを選択
	public function selectFromShiftinput($termid, $campusid, $dayno, $m_member_id, $dow)
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
		->from(array('staffshifts' => $this->_name), "*")
		->where('staffshifts.m_member_id = ?', $m_member_id)
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('staffshifts.dow = ?', $dow)
		;

		return $this->fetchAll($select);
	}
	
	// 指定のメンバーID、シフト種別ID, シフト時間帯、曜日に属するシフトを選択
	public function selectFromShiftinputForTwc($termid, $shiftclass, $dayno, $m_member_id, $dow)
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
	
				->where('shifts.dayno = ?', $dayno)
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")
		->where('staffshifts.m_member_id = ?', $m_member_id)
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('staffshifts.dow = ?', $dow)
		;
	
		return $this->fetchAll($select);
	}

	// 指定のキャンパスに属する相談場所で指定の順番のシフトを列挙し、そのシフトを参照している指定曜日のシフトスタッフを削除する
	public function deleteFromShiftinput($termid, $campusid, $dayno, $m_member_id, $dow)
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
				$db->quoteInto('dow = ?', $dow),
		);

		$db->delete($this->_name, $where);
	}
	
	// 指定のキャンパスに属する相談場所で指定の順番のシフトを列挙し、そのシフトを参照している指定曜日のシフトスタッフを削除する
	public function deleteFromShiftinputByMultiTermId(array $termids, $campusid, $dayno, $m_member_id, $dow)
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
				->where('shifts.m_place_id IN (?)', $subselect1)
				;
		
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
				$subselect2->where('(shifts.m_term_id = ?', $value);
			elseif($key === $array_last_key)
				$subselect2->orWhere('shifts.m_term_id = ?)', $value);
			else
				$subselect2->orWhere('shifts.m_term_id = ?', $value);
		}
	
		$where = array(
				$db->quoteInto('m_member_id = ?', $m_member_id),
				$db->quoteInto('m_shift_id IN (?)', $subselect2),
				$db->quoteInto('dow = ?', $dow),
		);
	
		$db->delete($this->_name, $where);
	}

	// 指定の文書IDで指定の順番のシフトを列挙し、そのシフトを参照している指定曜日のシフトスタッフを削除する
	public function deleteFromShiftinputForTwc($termid, $shiftclass, $dayno, $m_member_id, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$shiftclasses = explode(",", $shiftclass);

		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'))

				->where('dockinds.shiftclass = ?', $shiftclasses[0])
				;
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
						$db->quoteInto('dow = ?', $dow),
				);

				$db->delete($this->_name, $where);
	}
	
	// 指定の文書IDで指定の順番のシフトを列挙し、そのシフトを参照している指定曜日のシフトスタッフを削除する
	public function deleteFromShiftinputByMultiTermIdForTwc(array $termids, $shiftclass, $dayno, $m_member_id, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$shiftclasses = explode(",", $shiftclass);
	
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'))
	
				->where('dockinds.shiftclass = ?', $shiftclasses[0])
				;
				if(count($shiftclasses) > 1)
				{
					$subselect1->orWhere("dockinds.shiftclass = ?", $shiftclasses[1]);
				}
	
				$subselect2 = $db->select()->from(
						array('shifts'=>'m_shifts'),
						array('shifts.id'))
	
						->where('shifts.dayno = ?', $dayno)
						->where('shifts.m_dockind_id IN (?)', $subselect1)
						;
				
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
						$subselect2->where('(shifts.m_term_id = ?', $value);
					elseif($key === $array_last_key)
						$subselect2->orWhere('shifts.m_term_id = ?)', $value);
					else
						$subselect2->orWhere('shifts.m_term_id = ?', $value);
				}
	
				$where = array(
						$db->quoteInto('m_member_id = ?', $m_member_id),
						$db->quoteInto('m_shift_id IN (?)', $subselect2),
						$db->quoteInto('dow = ?', $dow),
				);
	
				$db->delete($this->_name, $where);
	}


	// 指定の期間、場所、シフト内連番、曜日に属するスタッフシフトを取得
	public function selectFromTodayStaff($termid, $campusid, $dayno, $dow)
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
				->where('shifts.dayno = ?', $dayno)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")

		->where('staffshifts.dow = ?', $dow)

		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('members.id is not null')

		//->order(array('staffshifts.createdate DESC'))
		;
		
		return $this->fetchAll($this->setJoinField($select));
	}

	// 指定の期間、シフト種別ID、シフト内連番、曜日に属するスタッフシフトを取得
	public function selectFromTodayStaffForTwc($termid, $shiftclass, $dayno, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$shiftclasses = explode(",", $shiftclass);

		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		
		if($shiftclasses[0] != 9)
			$subselect1->where('dockinds.shiftclass = ?', $shiftclasses[0]);
		
		if(!empty($shiftclasses[1]))
			$subselect1->orWhere('dockinds.shiftclass = ?', $shiftclasses[1]);

		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))

				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				->where('shifts.dayno = ?', $dayno)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")

		->where('staffshifts.dow = ?', $dow)

		->where('staffshifts.m_shift_id IN (?)', $subselect2)

		//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchAll($this->setJoinFieldTwc($select));
	}

	// 関大：指定の期間、場所、シフト内連番、曜日に属すスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTodayStaffIncludingDetails($termid, $campusid, $dayno, $dow, $weektop)
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
				->where('shifts.dayno = ?', $dayno)
				;

		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')
				->where("shiftdetails.shiftdate = (date '$weektop' + $dow - 1)")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;

		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidは登録
				->where("shiftdetails.shiftdate = (date '$weektop' + $dow - 1)")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;

		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));

		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")

		->where('staffshifts.dow = ?', $dow)
		
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)	// type='0'のshiftidは削除
		->where('members.id is not null')

		//->order(array('staffshifts.createdate DESC'))
		;
		
		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 関大：指定の期間、場所、シフト内連番、曜日あるいは日付に属すスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTodayStaffIncludingDetailsForAvailable($memberid, $shiftid, $dow, $ymd)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.id = ?', $shiftid)
				;
	
		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')
				->where("shiftdetails.shiftdate = (date '$ymd')")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect)
				->where('shiftdetails.m_member_id = ?', $memberid)
				;
	
		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidは登録
				->where("shiftdetails.shiftdate = (date '$ymd')")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect)
				->where('shiftdetails.m_member_id = ?', $memberid)
				;
	
		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")
		->where('staffshifts.m_member_id = ?', $memberid)
		->where('staffshifts.dow = ?', $dow)
	
		->where('staffshifts.m_shift_id IN (?)', $subselect)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)	// type='0'のshiftidは削除
	
		//->order(array('members.name_kana asc'))
		;
		
		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 関大：指定の期間、場所、シフト内連番、曜日あるいは日付に属すスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTodayStaffIncludingDetailsYmd($termid, $campusid, $dayno, $dow, $ymd)
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
				->where('shifts.dayno = ?', $dayno)
				;
	
		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')
				->where("shiftdetails.shiftdate = (date '$ymd')")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
	
		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidは登録
				->where("shiftdetails.shiftdate = (date '$ymd')")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
	
		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));
	
		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")
	
		->where('staffshifts.dow = ?', $dow)
	
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)	// type='0'のshiftidは削除
		->where('members.id is not null')
	
		->order(array('members.name_kana asc'))
		;
		
		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 津田：指定の期間、キャンパス、シフト内連番、曜日に属すスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTodayStaffIncludingDetailsForTwc($termid, $shiftclass, $dayno, $dow, $weektop)
	{
		$shiftclasses = explode(",", $shiftclass);
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		
		if($shiftclasses[0] != 9)
			$subselect1->where('dockinds.shiftclass = ?', $shiftclasses[0]);
		
		if(count($shiftclasses) > 1)
			$subselect1->orWhere("dockinds.shiftclass = ?", $shiftclasses[1]);
		
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
		
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				->where('shifts.dayno = ?', $dayno)
				;
	
		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')
				->where("shiftdetails.shiftdate = (date '$weektop' + $dow - 1)")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
	
		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidは登録
				->where("shiftdetails.shiftdate = (date '$weektop' + $dow - 1)")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
	
		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));
	
		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")
	
		->where('staffshifts.dow = ?', $dow)
	
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)	// type='0'のshiftidは削除
	
		//->order(array('staffshifts.createdate DESC'))
		;
	
		return $this->fetchAll($this->setJoinFieldTwc($select));
	}

	// 指定の期間、キャンパス、シフト内連番、曜日に属すスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromMemberIdAndTermIdAndCampusIdIncludingDetails($memberid, $termid, $campusid, $weektop)
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
				;

		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.m_member_id = ?', $memberid)
				->where('shiftdetails.type = \'0\'')		// type='0'の(shiftid, dow)は除外
				->where("shiftdetails.shiftdate >= ?", $weektop)
				->where("shiftdetails.shiftdate <= (date ? + 4)", $weektop)
				;

		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.m_member_id = ?', $memberid)
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidはUNION
				->where("shiftdetails.shiftdate >= ?", $weektop)
				->where("shiftdetails.shiftdate <= (date ? + 4)", $weektop)
				;

		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));

		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")
		->where('staffshifts.m_member_id = ?', $memberid)
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)

		//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 津田：指定の期間、シフト種別、シフト内連番、曜日に属すスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromMemberIdAndTermIdAndShiftclassIncludingDetails($memberid, $termid, $shiftclass, $weektop)
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
	
		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.m_member_id = ?', $memberid)
				->where('shiftdetails.type = \'0\'')		// type='0'の(shiftid, dow)は除外
				->where("shiftdetails.shiftdate >= ?", $weektop)
				->where("shiftdetails.shiftdate <= (date ? + 4)", $weektop)
				;
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				;
	
		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.m_member_id = ?', $memberid)
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidはUNION
				->where("shiftdetails.shiftdate >= ?", $weektop)
				->where("shiftdetails.shiftdate <= (date ? + 4)", $weektop)
				;
	
		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));
	
		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")
		->where('staffshifts.m_member_id = ?', $memberid)
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)
	
		//->order(array('staffshifts.createdate DESC'))
		;
	
		return $this->fetchAll($this->setJoinFieldTwc($select));
	}

	// 関大：指定の学期、キャンパス、曜日に属するスタッフシフトを取得
	public function selectFromTodayStaffExceptDayno($termid, $campusid, $dow)
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

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")

		->where('staffshifts.dow = ?', $dow)

		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('members.id is not null')

		//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 津田：指定の学期、シフト種別、曜日に属するスタッフシフトを取得
	public function selectFromTodayStaffExceptDaynoForTwc($termid, $shiftclass, $dow)
	{
		$shiftclasses = explode(",", $shiftclass);
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		
		if($shiftclasses[0] != 9)
			$subselect1->where('dockinds.shiftclass = ?', $shiftclasses[0]);
				
		if(count($shiftclasses) > 1)
			$subselect1->orWhere("dockinds.shiftclass = ?", $shiftclasses[1]);
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
	
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")
	
		->where('staffshifts.dow = ?', $dow)
	
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
	
		//->order(array('staffshifts.createdate DESC'))
		;
		
		return $this->fetchAll($this->setJoinFieldTwc($select));
	}
	
	// 関大：指定の学期、キャンパス、日付+曜日あるいは曜日に属すスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTodayStaffExceptDaynoIncludingDetails($termid, $campusid, $dow, $weektop)
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
				;
	
		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')		// type='0'の(shiftid, dow)は除外
				->where("shiftdetails.shiftdate = (date '$weektop' + $dow - 1)")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
	
		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidはUNION
				->where("shiftdetails.shiftdate = (date '$weektop' + $dow - 1)")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
	
		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));
	
		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")
		->where('staffshifts.dow = ?', $dow)
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)
		->where('members.id is not null')
		;
		
		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 津田：指定の学期、シフト種別、日付+曜日あるいは曜日に属すスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTodayStaffExceptDaynoIncludingDetailsForTwc($termid, $shiftclass, $dow, $weektop)
	{
		$shiftclasses = explode(",", $shiftclass);
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		
		if($shiftclasses[0] != 9)
			$subselect1->where('dockinds.shiftclass = ?', $shiftclasses[0]);
			
		if(count($shiftclasses) > 1)
			$subselect1->orWhere("dockinds.shiftclass = ?", $shiftclasses[1]);
		
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
		
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				;
	
		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')		// type='0'の(shiftid, dow)は除外
				->where("shiftdetails.shiftdate = (date '$weektop' + $dow - 1)")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
	
		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidはUNION
				->where("shiftdetails.shiftdate = (date '$weektop' + $dow - 1)")
				->where('shiftdetails.dow = ?', $dow)
				->where('shiftdetails.m_shift_id IN (?)', $subselect2)
				;
	
		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));
	
		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")
		->where('staffshifts.dow = ?', $dow)
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)
		;

		return $this->fetchAll($this->setJoinFieldTwc($select));
	}

	//masuda-------------------------------------------------------------------------
	public function selectFromTodayAllStaff($termid, $campusid, $dow)
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

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")

		->where('staffshifts.dow = ?', $dow)

		->where('staffshifts.m_shift_id IN (?)', $subselect2)

		//->order(array('staffshifts.createdate DESC'))
		;

		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 関大：指定の学期、キャンパス、曜日、日付あるいは曜日に属すスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTodayAllStaffExceptDaynoIncludingDetails($termid, $campusid, $dow, $ymd, $order=array('members.name_kana asc'))
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('places'=>'m_places'),
				array('places.id'))
				->where('places.m_campus_id = ?', $campusid);
	
		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')		// type='0'の(shiftid, dow)は除外
				->where("shiftdetails.shiftdate = ?", $ymd)
				;
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_place_id IN (?)', $subselect1)
				;
	
		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidはUNION
				->where("shiftdetails.shiftdate = ?", $ymd)
				;
	
		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));
	
		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")
		->where('staffshifts.dow = ?', $dow)
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)
		->order($order)
		;
	
		return $this->fetchAll($this->setJoinField($select));
	}
	
	// 津大：指定の学期、曜日、日付あるいは曜日に属すスタッフシフトを、
	// shiftdetailsテーブルのデータを含めて取得
	public function selectFromTodayAllStaffExceptDaynoIncludingDetailsForTwc($termid, $shiftclass=0, $dow, $ymd)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		if($shiftclass != 0)
			$subselect1->where('dockinds.shiftclass = ?', $shiftclass);
		
		$subdelete = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'0\'')		// type='0'の(shiftid, dow)は除外
				->where("shiftdetails.shiftdate = ?", $ymd)
				;
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_dockind_id IN (?)', $subselect1)
				;
	
		$subshiftdate = $db->select()->from(
				array('shiftdetails'=>'t_shiftdetails'),
				array('shiftdetails.m_member_id', 'shiftdetails.m_shift_id', 'shiftdetails.dow'))
				->where('shiftdetails.type = \'1\'')		// type='1'のshiftidはUNION
				->where("shiftdetails.shiftdate = ?", $ymd)
				;
	
		$staffshifts = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), array('staffshifts.m_member_id', 'staffshifts.m_shift_id', 'staffshifts.dow'));
	
		$select = $this->select()->setIntegrityCheck(false)
		//->from(array('staffshifts' => $this->_name), "*")
		->from(array('staffshifts' => $db->select()->union(array($staffshifts, $subshiftdate))), "*")
		->where('staffshifts.dow = ?', $dow)
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
		->where('(staffshifts.m_member_id, staffshifts.m_shift_id, staffshifts.dow) NOT IN (?)', $subdelete)
		;
	
		return $this->fetchAll($this->setJoinFieldTwc($select));
	}
	
	// 津大：シフト種別でシフト取得
	public function selectFromTodayAllStaffForTwc($termid, $shiftclass, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$shiftclasses = explode(",", $shiftclass);

		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'))
				->where('dockinds.shiftclass = ?', $shiftclasses[0]);
		if(!empty($shiftclasses[1]))
		{
			$subselect1->orWhere('dockinds.shiftclass = ?', $shiftclasses[1]);
		}
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id = ?', $termid)
				->where('shifts.m_place_id IN (?)', $subselect1)
	
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")
	
		->where('staffshifts.dow = ?', $dow)
	
		->where('staffshifts.m_shift_id IN (?)', $subselect2)
	
		//->order(array('staffshifts.createdate DESC'))
		;
	
		return $this->fetchAll($this->setJoinFieldForTwc($select));
	}
	
	// 津田：本日の予約状況用、担当者・日付毎の担当予約件数をカウント
	public function getChargedReserveCountPerStaffAndDate($reservationdate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$select = $db->query(
				'SELECT COUNT(*) as count, t_leadings.m_member_id_charge, t_reserves.reservationdate FROM t_leadings, t_reserves 
						WHERE t_leadings.t_reserve_id = t_reserves.id AND t_reserves.reservationdate = ?
						GROUP BY m_member_id_charge, t_reserves.reservationdate', $reservationdate
		);
		
		return $select->fetchAll();
	}
	
	// 津大：本日の予約状況用
	public function selectFromTodayAllStaffForTwcReserveStatus($termid, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
				->where('shifts.m_term_id = ?', $termid)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('staffshifts' => $this->_name), "*")
	
		->where('staffshifts.dow = ?', $dow)
	
		->where('staffshifts.m_shift_id IN (?)', $subselect)
	
		//->order(array('staffshifts.createdate DESC'))
		;
	
		return $this->fetchAll($this->setJoinFieldForTwc($select));
	}
}

