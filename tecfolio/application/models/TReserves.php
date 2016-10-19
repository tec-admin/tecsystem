<?php

require_once('BaseTModels.class.php');

// t_reserves テーブルクラス
class Class_Model_TReserves extends BaseTModels
{
	const TABLE_NAME = 't_reserves';
	protected $_name   = Class_Model_TReserves::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TReserves::TABLE_NAME;

		return array(
				$prefix . '_id' => 'id',
				
				$prefix . '_m_member_id_reserver' => 'm_member_id_reserver',
				$prefix . '_student_id' => 'student_id',
				$prefix . '_name_jp' => 'name_jp',
				$prefix . '_email' => 'email',
				
				$prefix . '_sex' => 'sex',
				$prefix . '_setti_cd' => 'setti_cd',
				$prefix . '_syozkcd1' => 'syozkcd1',
				$prefix . '_syozkcd2' => 'syozkcd2',
				$prefix . '_entrance_year' => 'entrance_year',
				$prefix . '_gaknenkn' => 'gaknenkn',
				
				$prefix . '_reservationdate' => 'reservationdate',
				$prefix . '_nendo' => 'nendo',
				
				$prefix . '_m_shift_id' => 'm_shift_id',
				
				$prefix . '_jwaricd' => 'jwaricd',
				$prefix . '_class_subject' => 'class_subject',
				$prefix . '_sekiji_top_kyoinmei' => 'sekiji_top_kyoinmei',
				
				$prefix . '_submitdate' => 'submitdate',
				$prefix . '_progress' => 'progress',
				$prefix . '_question' => 'question',
				
				$prefix . '_createdate' => 'createdate',
				$prefix . '_creator' => 'creator',
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
					
				$prefix . '_run_reserve' => 'run_reserve',
		);
	}
	
	private function addWhereNendo($select, $reservationdate=null)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$nendo = $db->select()
		->from(
				array('nendo'=>'m_nendo'),
				array('current_nendo')
		);
		$select
			->where("(jyu_nendo = ?", $nendo)
			->orWhere("jyu_nendo is null)");
		
		return $select;
	}

	private function joinField($select)
	{
		return $this->addFile($this->joinFieldBase($select));
		
		return $select;
	}

	private function joinFieldNoDuplicateId($select)
	{
		return $this->addSubj($this->joinFieldBase($select));
	}
	
	private function joinFieldLeading($select)
	{
		return $this->addLeading($this->joinFieldBase($select));
	}
	
	private function joinFieldBase($select)
	{
		$select
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shifts.id = reserves.m_shift_id',
				Class_Model_MShifts::fieldArray()
		)
	
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				Class_Model_MTimetables::fieldArray()
		)
	
		->joinLeft(
				array('reserver' => 'm_members'),
				'reserves.m_member_id_reserver = reserver.id',
				Class_Model_MMembers::fieldArray('reserver')
		)
	
		->joinLeft(
				array('reservecomments' => 't_reserve_comments'),
				'reservecomments.t_reserve_id = reserves.id',
				Class_Model_TReserveComments::fieldArray()
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
		;
	
		return $select;
	}
	
	private function addFile($select)
	{
		$select->joinLeft(
				array('reserve_files' => 't_reserve_files'),
				'reserve_files.t_reserve_id = reserves.id',
				Class_Model_TReserveFiles::fieldArray()
		)
		;
		return $select;
	}
	
	private function addSubj($select)
	{
		$select->joinLeft(
				array('subjects' => 'm_subjects'),
				'subjects.jwaricd = reserves.jwaricd',
				array('m_subjects_class_subject' => 'class_subject')
		)
		;
		return $select;
	}
	
	private function addLeading($select)
	{
		$select
		->joinLeft(
				array('leadings' => 't_leadings'),
				'leadings.t_reserve_id = reserves.id',
				Class_Model_TLeadings::fieldArray()
		)
	
		// leading を参照してるのでこちらへ入れる
		->joinLeft(
				array('charge' => 'm_members'),
				'leadings.m_member_id_charge = charge.id',
				Class_Model_MMembers::fieldArray('charge')
		)
		;
		return $select;
	}
	
	private function joinFieldLeftLeading($select)
	{
		$select
		->joinLeft(
				array('leadings' => 't_leadings'),
				'leadings.t_reserve_id = reserves.id',
				Class_Model_TLeadings::fieldArray()
		)

		// leading を参照してるのでこちらへ入れる
		->joinLeft(
				array('charge' => 'm_members'),
				'leadings.m_member_id_charge = charge.id',
				Class_Model_MMembers::fieldArray('charge')
		)
		;
		return $select;
	}

	private function joinFieldInnerLeading($select)
	{
		$select
		->join(
				array('leadings' => 't_leadings'),
				'leadings.t_reserve_id = reserves.id',
				Class_Model_TLeadings::fieldArray()
		)

		// leading を参照してるのでこちらへ入れる
		->joinLeft(
				array('charge' => 'm_members'),
				'leadings.m_member_id_charge = charge.id',
				Class_Model_MMembers::fieldArray('charge')
		)
		;
		return $select;
	}
	
	
	private function setJoinField($select)
	{
		return $this->joinFieldLeftLeading($this->joinField($select));
	}
	
	private function setJoinFieldNoDuplicateId($select)
	{
		return $this->joinFieldLeftLeading($this->joinFieldNoDuplicateId($select));
	}
	
	
	
	// t_leadings のみ inner join にする
	private function setJoinFieldInnerLeading($select)
	{
		return $this->joinFieldInnerLeading($this->joinField($select));
	}
	
	// 変更される予約がシフトに影響を及ぼすかどうかを調べる
	public function getChangedShift($reserveid, $reservationdate, $m_shift_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// 変更前の予約：選択されなければ予約日の変更
		$reserve = $db->select()->from(
				array('reserves'=>'t_reserves'),
				array('reserves.m_shift_id')
		)
		->where('reserves.id = ?', $reserveid)
		->where('reserves.reservationdate = ?', $reservationdate)
		;
	
		// 変更前の予約のシフトID各要素
		$before = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.m_term_id', 'shifts.m_place_id', 'shifts.dayno')
		)
		->where('shifts.id = ?', $reserve)
		;
	
		// 変更後のシフトID各要素との対照
		$after = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id')
		)
		->where('shifts.id = ?', $m_shift_id)
		->where('(shifts.m_term_id, shifts.m_place_id, shifts.dayno) NOT IN (?)', $before)
		;
		
		return $db->fetchAll($after);
	}
	
	// 利用データダウンロード用データを取得する
	public function selectAllForDownload()
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('reserves' => $this->_name), '*')
		->order('reservationdate asc');
	
		return $this->fetchAll($this->setJoinFieldNoDuplicateId($select));
	}

	// シフト（日時含む）で取得
	public function selectShift($m_shift_id, $reservationdate)
	{
		$select = $this->select();
		$select->where('m_shift_id = ?', $m_shift_id);
		$select->where('reservationdate = ?', $reservationdate);
		
		return $this->fetchAll($select);
	}
	
	// シフト（日時含む）で取得・予約時用
	// 引数$m_shift_idと同一の学期・場所・日付連番を条件とする
	public function selectShiftForReserve($m_shift_id, $reservationdate)
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
		
		$select = $this->select();
		$select->where('m_shift_id IN (?)', $subShift);
		$select->where('reservationdate = ?', $reservationdate);
		
		return $this->fetchAll($select);
	}
	
	// 関大：場所・シフト連番・日付で取得
	public function selectShiftKwl($m_place_id, $dayno, $reservationdate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_place_id = ?', $m_place_id)
				->where('shifts.dayno = ?', $dayno)
				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_shift_id IN (?)', $subselect)
				->where('reserves.reservationdate = ?', $reservationdate)
				;

		return $this->fetchAll($select);
	}
	
	public function getShiftclassFromDockindId($m_dockind_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$shiftclass = $db->select()->from(
				array('dockinds' => 'm_dockinds'),
				array('dockinds.shiftclass'))
				->where('dockinds.id = ?', $m_dockind_id)
				;
		
		$l_shiftclass = $db->select()->from(
				array('l_shiftclasses' => 'm_l_shiftclasses'),
				array('l_shiftclasses.m_shiftclass_id'))
				->where(new Zend_Db_Expr("SUBSTR(l_shiftclasses.m_shiftclass_id,1,1)") . ' = ?', $m_dockind_id)
				->orWhere(new Zend_Db_Expr("SUBSTR(l_shiftclasses.m_shiftclass_id,3,1)") . ' = ?', $m_dockind_id)
				;
		
	}
	
	// 津大：シフト種別・シフト連番・日付で取得
	public function selectShiftTwc($m_dockind_id, $dayno, $reservationdate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// 予約数は'1,2'、受入数・担当者数は'1'で取得する必要がある
		$shiftclass = Class_Model_MLShiftclasses::getShiftclassFromDockindId($m_dockind_id);
		
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
				array('reserves' => $this->_name), "*")
	
				->where('reserves.m_shift_id IN (?)', $subselect2)
				->where('reserves.reservationdate = ?', $reservationdate)
				;
	
		return $this->fetchAll($select);
	}

	// 指定のメンバーIDのシフト（日時含む）で取得
	// ※ 同じメンバーが同じシフトを予約できない前提なら fetchRow() でも良いがひとまず fetchAll() とする
	public function selectFromMemberIdAndShift($m_member_id, $m_shift_id, $reservationdate)
	{
		$select = $this->select();
		$select->where('m_member_id_reserver = ?', $m_member_id);
		$select->where('m_shift_id = ?', $m_shift_id);
		$select->where('reservationdate = ?', $reservationdate);
		return $this->fetchAll($select);
	}

	// 指定のメンバーのIDのシフトのすべての文書タイプで取得（m_place_idは含まない）
	public function selectFromMemberIdAndShiftAllDockind($m_member_id, $m_term_id, $dayno, $reservationdate, $reserveid='0')
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_term_id = ?', $m_term_id)
				->where('shifts.dayno = ?', $dayno)
				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), 
				array('id', 'm_member_id_reserver', 'reservationdate', 'm_shift_id'))

				->where('reserves.m_member_id_reserver = ?', $m_member_id)
				->where('reserves.m_shift_id IN (?)', $subselect)
				->where('reserves.reservationdate = ?', $reservationdate)
				->where('reserves.id != ?', $reserveid)
				;
		
		return $this->fetchAll($select);
	}
	
	// 指定のメンバーのIDのシフトのすべての文書タイプで取得
	public function selectRowFromMemberIdAndShiftAllDockind($m_member_id, $m_term_id, $m_place_id, $dayno, $reservationdate, $reserveid='0')
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_term_id = ?', $m_term_id)
				->where('shifts.m_place_id = ?', $m_place_id)
				->where('shifts.dayno = ?', $dayno)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name),
				array('id', 'm_member_id_reserver', 'reservationdate', 'm_shift_id'))
	
				->where('reserves.m_member_id_reserver = ?', $m_member_id)
				->where('reserves.m_shift_id IN (?)', $subselect)
				->where('reserves.reservationdate = ?', $reservationdate)
				->where('reserves.id = ?', $reserveid)
				;
				
				return $this->fetchAll($select);
	}


	// 指定のメンバーのIDのシフトの特定の文書タイプで取得
	public function selectFromMemberIdAndShiftByDockindId($m_member_id, $m_dockind_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$currentdate = date("Y/m/d", time());
		$currenttime = date("H:i:s", time());

		$subselect1 = $db->select()
		->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_dockind_id = ?', $m_dockind_id);

		$subselectTable = $db->select()
		->from(
				array('timetables'=>'m_timetables'),
				array('timetables.id'))
				->where('timetables.starttime > ?', $currenttime);

		$subselect2 = $db->select()
		->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_dockind_id = ?', $m_dockind_id)
				->where('shifts.dayno IN (?)', $subselectTable);

		$select = $this->select()->setIntegrityCheck(false)

		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_member_id_reserver = ?', $m_member_id)
				->where('((reserves.m_shift_id IN (?)', $subselect1)		// 未来の日付の予約
				->where('reserves.reservationdate > ?)', $currentdate)
				->orWhere('(reserves.m_shift_id IN (?)', $subselect2)		// あるいは、今日の日付で現在時刻以上(未来)の予約
				->where('reserves.reservationdate = ?))', $currentdate);

		return $this->fetchAll($select);
	}


	// 指定のメンバーのIDのシフトの特定の文書タイプで取得（ただし第三引数の文書タイプを除く・編集画面用）
	public function selectFromMemberIdAndShiftByDockindIdExceptCurrentPages($m_member_id, $m_dockind_id, $pre_m_dockind_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$currentdate = date("Y/m/d", time());
		$currenttime = date("H:i:s", time());

		$subselect1 = $db->select()

		->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_dockind_id = ?', $m_dockind_id)
				->where('shifts.m_dockind_id != ?', $pre_m_dockind_id);
		
		$subselectTable = $db->select()
		->from(
				array('timetables'=>'m_timetables'),
				array('timetables.id'))
				->where('timetables.starttime > ?', $currenttime);
			
		$subselect2 = $db->select()

		->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_dockind_id = ?', $m_dockind_id)
				->where('shifts.m_dockind_id != ?', $pre_m_dockind_id)
				->where('shifts.dayno IN (?)', $subselectTable);


		$select = $this->select()->setIntegrityCheck(false)

		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_member_id_reserver = ?', $m_member_id)
				->where('((reserves.m_shift_id IN (?)', $subselect1)
				->where('reserves.reservationdate > ?)', $currentdate)
				->orWhere('(reserves.m_shift_id IN (?)', $subselect2)
				->where('reserves.reservationdate = ?))', $currentdate);

		return $this->fetchAll($this->setJoinField($select));
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


	// IDで取得（※オーバーライド）
	public function selectFromId($reserveid)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.id = ?', $reserveid);
		
		return $this->fetchRow($this->setJoinFieldNoDuplicateId($select));
	}
	
	
	public function selectKyoinFromId($reserveid)
	{
		$select = $this->select()->distinct()->setIntegrityCheck(false)
			->from(
				array('reserves' => $this->_name), 
				""
			)
	
			->where('reserves.id = ?', $reserveid)
			
			->join(
				array('subjects' => 'm_subjects'),
				'subjects.jwaricd = reserves.jwaricd',
				''
			)
			->join(
				array('kyoin' => 'm_members'),
				'subjects.kyoincd = kyoin.staff_no',
				array('name_jp')
			);
			
		
		return $this->fetchAll($select);
	}


	// 生徒用新着取得
	public function selectFromStudentNews($studentid, $limit=5, $offset=0)
	{
		// 予約用
		$select1 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('members' => 'm_members'), array('MyID' => 'id')
		)
		->where('members.id = ?', $studentid);
		
		$select1
		->joinLeft(
				array('reserves' => $this->_name),
				'members.id = reserves.m_member_id_reserver',
				'*'
		)
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shifts.id = reserves.m_shift_id',
				''
		)
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				array('m_timetables_starttime' => 'starttime',
						'm_timetables_endtime' => 'endtime')
		)
		->joinLeft(
				array('leadings' => 't_leadings'),
				'1 = 0',
				array('t_leadings_submit_flag' => 'submit_flag',
						't_leadings_leading_comment' => 'leading_comment')
		)
		->joinLeft(
				array('charge' => 'm_members'),
				'leadings.m_member_id_charge = charge.id',
				array('charge_name_jp' => 'name_jp')
		)
		->joinLeft(
				array('mentors' => 't_mentors'),
				'1 = 0',
				Class_Model_Tecfolio_TMentors::fieldArray()
		)
		->joinLeft(
				array('requester' => 'm_members'),
				'mentors.creator = requester.id',
				array('requester_id' => 'id', 'requester_name_jp' => 'name_jp')
		)
		->where('reserves.m_member_id_reserver = ?', $studentid)
		;
		// ソート用カラムを作成
		$select1->columns(array('studentnews_date' => 'reserves.createdate'));
		
		
		// 指導用
		$select2 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('members' => 'm_members'), array('MyID' => 'id')
		)
		->where('members.id = ?', $studentid);
		
		$select2
		->joinLeft(
				array('reserves' => $this->_name),
				'members.id = reserves.m_member_id_reserver',
				'*'
		)
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shifts.id = reserves.m_shift_id',
				''
		)
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				array('m_timetables_starttime' => 'starttime',
						'm_timetables_endtime' => 'endtime')
		)
		->joinRight(
				array('leadings' => 't_leadings'),
				'leadings.t_reserve_id = reserves.id',
				array('t_leadings_submit_flag' => 'submit_flag',
						't_leadings_leading_comment' => 'leading_comment')
		)
		->joinLeft(
				array('charge' => 'm_members'),
				'leadings.m_member_id_charge = charge.id',
				array('charge_name_jp' => 'name_jp')
		)
		->joinLeft(
				array('mentors' => 't_mentors'),
				'1 = 0',
				Class_Model_Tecfolio_TMentors::fieldArray()
		)
		->joinLeft(
				array('requester' => 'm_members'),
				'mentors.creator = requester.id',
				array('requester_id' => 'id', 'requester_name_jp' => 'name_jp')
		)
		->where('reserves.m_member_id_reserver = ?', $studentid)
		->where('leadings.leading_comment != \'\'')
		;
		// ソート用カラムを作成
		$select2->columns(array('studentnews_date' => 'leadings.lastupdate'));
		
		
		// メンター用
		$select3 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('members' => 'm_members'), array('MyID' => 'id')
		)
		->where('members.id = ?', $studentid);
		
		$select3
		->joinLeft(
				array('reserves' => $this->_name),
				'1 = 0',
				'*'
		)
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shifts.id = reserves.m_shift_id',
				''
		)
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				array('m_timetables_starttime' => 'starttime',
						'm_timetables_endtime' => 'endtime')
		)
		->joinLeft(
				array('leadings' => 't_leadings'),
				'1 = 0',
				array('t_leadings_submit_flag' => 'submit_flag',
						't_leadings_leading_comment' => 'leading_comment')
		)
		->joinLeft(
				array('charge' => 'm_members'),
				'leadings.m_member_id_charge = charge.id',
				array('charge_name_jp' => 'name_jp')
		)
		->joinRight(
				array('mentors' => 't_mentors'),
				'members.id = mentors.m_member_id',
				Class_Model_Tecfolio_TMentors::fieldArray()
		)
		->joinLeft(
				array('requester' => 'm_members'),
				'mentors.creator = requester.id',
				array('requester_id' => 'id', 'requester_name_jp' => 'name_jp')
		)
		->where('mentors.m_member_id = ?', $studentid)
		;
		// ソート用カラムを作成
		$select3->columns(array('studentnews_date' => 'mentors.lastupdate'));
		
		
		// メンター依頼者用
		$select4 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('members' => 'm_members'), array('MyID' => 'id')
		)
		->where('members.id = ?', $studentid);
		
		$select4
		->joinLeft(
				array('reserves' => $this->_name),
				'1 = 0',
				'*'
		)
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shifts.id = reserves.m_shift_id',
				''
		)
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				array('m_timetables_starttime' => 'starttime',
						'm_timetables_endtime' => 'endtime')
		)
		->joinLeft(
				array('leadings' => 't_leadings'),
				'1 = 0',
				array('t_leadings_submit_flag' => 'submit_flag',
						't_leadings_leading_comment' => 'leading_comment')
		)
		->joinLeft(
				array('charge' => 'm_members'),
				'leadings.m_member_id_charge = charge.id',
				array('charge_name_jp' => 'name_jp')
		)
		->joinRight(
				array('mentors' => 't_mentors'),
				'members.id = mentors.creator',
				Class_Model_Tecfolio_TMentors::fieldArray()
		)
		->joinLeft(
				array('requester' => 'm_members'),
				'mentors.creator = requester.id',
				array('requester_id' => 'id', 'requester_name_jp' => 'name_jp')
		)
		->where('requester.id = ?', $studentid)
		;
		// ソート用カラムを作成
		$select4->columns(array('studentnews_date' => 'mentors.lastupdate'));
		
		// 二つの表をUNION x 3
		$select_tmp1 = $this->select()
		->union(array($select1, $select2));
		
		$select_tmp2 = $this->select()
		->union(array($select_tmp1, $select3));
		
		$select = $this->select()
		->union(array($select_tmp2, $select4))
		->order('studentnews_date DESC');

		if ($limit > 0)
			$select->limit($limit, $offset);
		
		return $this->fetchAll($select);
	}

	// メンバーIDで取得
	public function selectFromMemberId($m_member_id, $page=1, $limit=0)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_member_id_reserver = ?', $m_member_id)

				->order(array('reserves.createdate DESC'));

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->setJoinField($select));
	}

	// メンバーIDと指定の範囲の日付のデータをすべて取得
	public function selectFromMemberIdAndTermRange($m_member_id, $startdate, $enddate, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.reservationdate >= ?', $startdate)
				->where('reserves.reservationdate <= ?', $enddate)

				->order($order);

		if (!empty($m_member_id))
			$select->where('reserves.m_member_id_reserver = ?', $m_member_id);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->setJoinField($select));
	}

	// 指定の範囲のデータをすべて取得
	public function selectFromTermRange($startdate, $enddate, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.reservationdate >= ?', $startdate)
				->where('reserves.reservationdate <= ?', $enddate)

				->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->joinFieldLeading($select));
	}

	// 指定の範囲のデータをID重複なくすべて取得
	public function selectFromTermRangeForCount($startdate, $enddate, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*");
		if($startdate != 0)
			$select->where('reserves.reservationdate >= ?', $startdate);
		if($enddate != 0)
			$select->where('reserves.reservationdate <= ?', $enddate);

		$select->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);
		
		return $this->fetchAll($this->joinFieldBase($select));
	}

	// 指定の範囲のデータをID重複なくすべて取得
	public function selectFromTermRangeAndPlaceIdAndDockindIdForCount($startdate, $enddate, $placeid, $dockindid, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
	{
// 		$nowdate = date('Y', strtotime(Zend_Registry::get('nowdate')));
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'));
		if($placeid != 0)
			$subselect->where('shifts.m_place_id = ?', $placeid);
		if($dockindid != 0)
			$subselect->where('shifts.m_dockind_id = ?', $dockindid);

		$select = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*");
		
		if($startdate != 0)
			$select->where('reserves.reservationdate >= ?', $startdate);
		if($enddate != 0)
			$select->where('reserves.reservationdate <= ?', $enddate);
			
			$select
				->where('reserves.m_shift_id IN (?)', $subselect)
// 				->where('reserves.entrance_year <= to_number(?, \'9999\')', $nowdate)
// 				->where('reserves.entrance_year >= to_number(?, \'9999\') - 3', $nowdate)
				->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);
		
		return $this->fetchAll($this->joinFieldNoDuplicateId($select));
	}
	
	// 指定の範囲のデータをID重複なくすべて取得
	public function selectFromTermRangeAndPlaceIdAndShiftclassForCount($startdate, $enddate, $placeid, $shiftclass, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
	{
		$nowdate = date('Y', strtotime(Zend_Registry::get('nowdate')));
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subselectDockinds = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));
		if($shiftclass != 0)
			$subselectDockinds->where('dockinds.shiftclass = ?', $shiftclass);
		
		$subselectShifts = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'));
		if($placeid != 0)
		{
			$subselectShifts->where('shifts.m_place_id = ?', $placeid);
		}
		$subselectShifts->where('shifts.m_dockind_id IN (?)', $subselectDockinds);
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")
	
				->where('reserves.reservationdate >= ?', $startdate)
				->where('reserves.reservationdate <= ?', $enddate)
				->where('reserves.m_shift_id IN (?)', $subselectShifts)
				->where('reserver.entrance_year <= to_number(?, \'9999\')', $nowdate)
				->where('reserver.entrance_year >= to_number(?, \'9999\') - 3', $nowdate)
	
				->order($order);
	
		if ($limit > 0)
			$select->limitPage($page, $limit);
	
		return $this->fetchAll($this->joinFieldNoDuplicateId($select));
	}

	// 指定の範囲の駆け込み予約を取得
	public function selectRunReserveFromTermRangeForCount($startdate, $enddate, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")
				->where('reserves.run_reserve = \'1\'');
		if($startdate != 0)
			$select->where('reserves.reservationdate >= ?', $startdate);
		if($enddate != 0)
			$select->where('reserves.reservationdate <= ?', $enddate);
				
		$select->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->joinFieldBase($select));
	}

	// 指定の日付以前をすべて取得（履歴のみ用）
	public function GetSelectFromMemberIdHistory($m_member_id, $nowdatetime, $limit=0, $order="")
	{
		// 時間と日付を分解
		$nowdate = date('Y-m-d', strtotime($nowdatetime));
		$nowtime = date('H:i:s', strtotime($nowdatetime));

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_member_id_reserver = ?', $m_member_id)
				//->where('reserves.reservationdate < ?', $nowdate);
		->where("reserves.reservationdate < '$nowdate' OR (reserves.reservationdate = '$nowdate' AND shifts.endtime < '$nowtime')");

		if (!empty($order))
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);
		
		return $this->joinFieldBase($select);
	}

	public function selectFromMemberIdHistory($m_member_id, $nowdatetime, $limit=0, $order="")
	{
		$select = $this->GetSelectFromMemberIdHistory($m_member_id, $nowdatetime, $limit, $order);

		return $this->fetchAll($select);
	}

	// 指定の日付以前をすべて取得（予約のみ用）
	public function GetSelectFromMemberIdNoHistory($m_member_id, $nowdatetime, $limit=0, $order="")
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// 時間と日付を分解
		$nowdate = date('Y-m-d', strtotime($nowdatetime));
		$nowtime = date('H:i:s', strtotime($nowdatetime));
		
		$subselectTable = $db->select()
		->from(
				array('timetables'=>'m_timetables'),
				array('timetables.id'))
				->where('timetables.starttime > ?', $nowtime);

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_member_id_reserver = ?', $m_member_id)
				//->where('reserves.reservationdate < ?', $nowdate);
		->where("reserves.reservationdate > '$nowdate' OR (reserves.reservationdate = '$nowdate' AND shifts.dayno IN ($subselectTable))");

		if (!empty($order))
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);
		
		return $this->joinFieldBase($select);
	}

	public function selectFromMemberIdNoHistory($m_member_id, $nowdatetime, $limit=0, $order="")
	{
		$select = $this->GetSelectFromMemberIdNoHistory($m_member_id, $nowdatetime, $limit, $order);

		return $this->fetchAll($select);
	}

	// オブジェクトですべて取得（一覧用）
	public function GetAll($limit=0, $order=array())
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				//->where('reserves.m_member_id_reserver = ?', $m_member_id)
		;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->joinFieldLeading($select);
	}

	// 指定された入力値で取得
	public function GetSelectFromInput($placeid, $termid, $date_from, $date_to, $dayno, $studentname, $staffname, $dockindid, $subjectname, $limit=0, $order=array())
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id')
			)
			;
		if($placeid != '0' && !empty($placeid))
			$subselect->where('shifts.m_place_id = ?', $placeid);
		if($termid != '0' && !empty($termid))
			$subselect->where('shifts.m_term_id = ?', $termid);
		if($dayno != '0' && !empty($dayno))
			$subselect->where('shifts.dayno = ?', $dayno);
		if($dockindid != '0' && !empty($dockindid))
			$subselect->where('shifts.m_dockind_id = ?', $dockindid);


		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")
				->where('reserves.m_shift_id IN (?)', $subselect)
				;

		if($date_from != '0' && !empty($date_from))
			$select->where('reserves.reservationdate >= ?', $date_from);
		if($date_to != '0' && !empty($date_to))
			$select->where('reserves.reservationdate <= ?', $date_to);

		if($studentname != '0' && !empty($studentname))
			$select->where('reserver.name_jp LIKE ?', '%'.$studentname.'%');
		if($staffname != '0' && !empty($staffname))
			$select->where('charge.name_jp LIKE ?', '%'.$staffname.'%');

		if($subjectname != '0' && !empty($subjectname))
		{
			$subsubject = $db->select()->from(
					array('subjects'=>'m_subjects'),
					array('subjects.id'))
					->where('subjects.class_subject LIKE ?', '%'.$subjectname.'%')
					;

			$select->where('reserves.jwaricd IN (?)', $subsubject);
		}

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->setJoinField($select);
	}
	
	// 指定された入力値で取得
	public function GetSelectFromInputJQ($placeid, $termid, $limit=0, $order=array())
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id')
			)
			;
		if($placeid != '0' && !empty($placeid))
			$subselect->where('shifts.m_place_id = ?', $placeid);
		if($termid != '0' && !empty($termid))
			$subselect->where('shifts.m_term_id = ?', $termid);

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")
				->where('reserves.m_shift_id IN (?)', $subselect)
				;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);
		
		$select
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shifts.id = reserves.m_shift_id',
				Class_Model_MShifts::fieldArray()
		)
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				Class_Model_MTimetables::fieldArray()
		)
		->joinLeft(
				array('reserver' => 'm_members'),
				'reserves.m_member_id_reserver = reserver.id',
				Class_Model_MMembers::fieldArray('reserver')
		)
		->joinLeft(
				array('reservecomments' => 't_reserve_comments'),
				'reservecomments.t_reserve_id = reserves.id',
				Class_Model_TReserveComments::fieldArray()
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
		->joinRight(
				array('leadings' => 't_leadings'),
				'(leadings.t_reserve_id = reserves.id AND leadings.submit_flag != \'0\')',
				Class_Model_TLeadings::fieldArray()
		)
		->joinLeft(
				array('charge' => 'm_members'),
				'leadings.m_member_id_charge = charge.id',
				Class_Model_MMembers::fieldArray('charge')
		)
		;
		
		return $this->fetchAll($select);
	}

	// 指定された相談者のメンバーIDでオブジェクトで取得
	public function GetSelectFromReserverId($m_member_id, $limit=0, $order=array())
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_member_id_reserver = ?', $m_member_id)
				;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->joinFieldLeading($select);
	}

	// 指定された授業科目IDでオブジェクトで取得
	public function GetSelectFromSubjectId($jwaricd, $limit=0, $order=array())
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.jwaricd = ?', $jwaricd)
				;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->joinFieldLeading($select);
	}

	// 指定された相談者のメンバーIDで相談済み(対応するt_leadingsがある場合のみ)オブジェクトで取得
	public function GetSelectFromReserverIdLeading($m_member_id, $limit=0, $order=array())
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_member_id_reserver = ?', $m_member_id)
				;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->setJoinFieldInnerLeading($select);
	}

	// 指定された担当者のメンバーIDのシフト時間でオブジェクト取得（予定が存在する場合）
	public function GetSelectFromChargeIdForScheduleSort($m_member_id, $limit=0, $order=array())
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('staffshifts'=>'t_staffshifts'),
				array('staffshifts.m_shift_id', 'staffshifts.dow'))
				->where('staffshifts.m_member_id = ?', $m_member_id);
		
		$subselectTable = $db->select()
		->from(
				array('timetables'=>'m_timetables'),
				array('timetables.id'))
				->where('timetables.starttime > ?', date("H:i:s", time()));

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('(reserves.m_shift_id, EXTRACT(DOW FROM reserves.reservationdate)) IN (?)', $subselect)
				->where('(reserves.reservationdate > ?', date("Y/m/d", time()) )
				->orWhere('(reserves.reservationdate = ?', date("Y/m/d", time()) )
				->where('shifts.dayno IN (?)))', $subselectTable )
				;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);
		
		return $this->setJoinField($select);
	}

	// 指定された担当者のメンバーIDのシフト時間でオブジェクト取得（予定が存在しない場合）
	public function GetSelectFromChargeIdForHistorySort($m_member_id, $limit=0, $order=array())
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('staffshifts'=>'t_staffshifts'),
				array('staffshifts.m_shift_id', 'staffshifts.dow'))
				->where('staffshifts.m_member_id = ?', $m_member_id);

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('(reserves.m_shift_id, EXTRACT(DOW FROM reserves.reservationdate)) IN (?)', $subselect)
				;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);
		
		return $this->setJoinField($select);
	}

	// 仕様変更により未使用
	// 指定された担当者のメンバーIDのシフト時間でオブジェクト取得
	public function GetSelectFromChargeIdForAdvice($m_member_id, $limit=0, $order=array())
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('staffshifts'=>'t_staffshifts'),
				array('staffshifts.m_shift_id', 'staffshifts.dow'))
				->where('staffshifts.m_member_id = ?', $m_member_id);

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('(reserves.m_shift_id, EXTRACT(DOW FROM reserves.reservationdate)) IN (?)', $subselect)
				;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->setJoinField($select);
	}

	// 指定された相談者のメンバーIDで相談済みオブジェクトで取得
	public function GetSelectFromReserverIdForAdvice($m_member_id, $reserverid, $limit=0, $order=array())
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('staffshifts'=>'t_staffshifts'),
				array('staffshifts.m_shift_id', 'staffshifts.dow'))
				->where('staffshifts.m_member_id = ?', $m_member_id);

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('(reserves.m_shift_id, EXTRACT(DOW FROM reserves.reservationdate)) IN (?)', $subselect)
				->where('reserves.m_member_id_reserver = ?', $reserverid)
				;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->joinFieldLeading($select);
	}

	// 指定された担当者のメンバーIDでオブジェクトで取得
	public function GetSelectFromChargeId($m_member_id_charge, $limit=0, $order=array())
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('leadings'=>'t_leadings'),
				array('leadings.t_reserve_id'))
				->where('leadings.m_member_id_charge = ?', $m_member_id_charge);

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.id IN (?)', $subselect)
				;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);
		
		return $this->joinFieldLeading($select);
	}

	// 指定された担当者のメンバーIDと予約者のメンバーIDでオブジェクトで取得
	public function GetSelectFromChargeIdAndReserverId($m_member_id_charge, $m_member_id, $limit=0, $order=array())
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('leadings'=>'t_leadings'),
				array('leadings.t_reserve_id'))
				->where('leadings.m_member_id_charge = ?', $m_member_id_charge);

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.id IN (?)', $subselect)
				->where('reserves.m_member_id_reserver = ?', $m_member_id)

				;

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->setJoinField($select);
	}

	public function countSubmitdate($m_member_id, $date)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		return $db->fetchOne('SELECT COUNT(*) FROM ' . $this->_name . ' WHERE m_member_id_reserver = :reserver AND submitdate = :targetdate', array('reserver' => $m_member_id, 'targetdate' => $date));
	}


	// 指定のキャンパスIDと日付ですべて取得
	public function selectFromCampusIdAndDate($campusid, $date, $page=1, $limit=0, $order=array('shifts.dayno ASC'))
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('places'=>'m_places'),
				array('places.id'))
				->where('places.m_campus_id = ?', $campusid);

		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))

				->where('shifts.m_place_id IN (?)', $subselect1)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_shift_id IN (?)', $subselect2)
				->where('reserves.reservationdate = ?', $date)

				->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->joinFieldLeading($select));
	}

	// 指定のシフト種別と日付ですべて取得
	public function selectFromShiftClassAndDate($shiftclass, $date, $page=1, $limit=0, $order=array('shifts.dayno ASC'))
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$shiftclasses = explode(",", $shiftclass);

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

				->where('shifts.m_dockind_id IN (?)', $subselect1)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_shift_id IN (?)', $subselect2)
				->where('reserves.reservationdate = ?', $date)

				->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->setJoinField($select));
	}


	// 指定のキャンパスIDと日付の範囲ですべて取得
	public function selectFromCampusIdAndRange($campusid, $startdate, $enddate, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
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

				->where('shifts.m_place_id IN (?)', $subselect1)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_shift_id IN (?)', $subselect2)
				->where('reserves.reservationdate >= ?', $startdate)
				->where('reserves.reservationdate <= ?', $enddate)
				->joinLeft(
						array('shifts' => 'm_shifts'),
						'shifts.id = reserves.m_shift_id',
						Class_Model_MShifts::fieldArray()
				)
				->joinLeft(
						array('timetables' => 'm_timetables'),
						'shifts.dayno = timetables.id',
						Class_Model_MTimetables::fieldArray()
				)
				->joinLeft(
						array('reserver' => 'm_members'),
						'reserves.m_member_id_reserver = reserver.id',
						Class_Model_MMembers::fieldArray('reserver')
				)
				->joinLeft(
						array('reservecomments' => 't_reserve_comments'),
						'reservecomments.t_reserve_id = reserves.id',
						Class_Model_TReserveComments::fieldArray()
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
						array('leadings' => 't_leadings'),
						'leadings.t_reserve_id = reserves.id',
						Class_Model_TLeadings::fieldArray()
				)
				->joinLeft(
						array('charge' => 'm_members'),
						'leadings.m_member_id_charge = charge.id',
						Class_Model_MMembers::fieldArray('charge')
				)
				->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);
		
		return $this->fetchAll($select);
	}

	// 指定の場所IDと日付の範囲ですべて取得
	public function selectFromPlaceIdAndRange($placeid, $startdate, $enddate, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))

				->where('shifts.m_place_id = ?', $placeid)
				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*");
		if($placeid != 0)
			$select->where('reserves.m_shift_id IN (?)', $subselect);
			
		$select
		->where('reserves.reservationdate >= ?', $startdate)
		->where('reserves.reservationdate <= ?', $enddate)

		->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->setJoinField($select));
	}

	// 指定のシフト種別と日付の範囲ですべて取得
	public function selectFromShiftclassAndRange($shiftclass, $startdate, $enddate, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$shiftclasses = explode(",", $shiftclass);

		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'));

		if($shiftclasses[0] != 0)
		{
			$subselect1->where('dockinds.shiftclass = ?', $shiftclasses[0]);
				
			if(count($shiftclasses) > 1)
			{
				$subselect1->orWhere("dockinds.shiftclass = ?", $shiftclasses[1]);
			}
		}

		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))

				->where('shifts.m_dockind_id IN (?)', $subselect1)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_shift_id IN (?)', $subselect2)
				->where('reserves.reservationdate >= ?', $startdate)
				->where('reserves.reservationdate <= ?', $enddate)

				->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->setJoinFieldTwc($select));
	}

	// 指定の日付ですべて取得
	public function selectFromDate($date, $page=1, $limit=0, $order=array('shifts.dayno ASC'))
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.reservationdate = ?', $date)

				->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->setJoinField($select));
	}

	// 指定の日付ですべて取得
	public function selectFromDateForTwc($date, $page=1, $limit=0, $order=array('shifts.dayno ASC'))
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.reservationdate = ?', $date)

				->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->setJoinFieldNoDuplicateIdTwc($select));
	}

	// 指定のキャンパスIDとメンバーIDで取得
	public function selectOverlap($campusid, $m_member_id, $reservationdate, $dayno, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect1 = $db->select()->from(
				array('places'=>'m_places'),
				array('places.id'))
				->where('places.m_campus_id = ?', $campusid);

		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))

				->where('shifts.m_place_id IN (?)', $subselect1)
				->where('shifts.dayno = ?', $dayno)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_shift_id IN (?)', $subselect2)
				->where('reserves.reservationdate = ?', $reservationdate)

				->order($order);

		if ($m_member_id !== 0)
			$select->where('reserves.m_member_id_reserver = ?', $m_member_id);

		if ($limit > 0)
			$select->limitPage($page, $limit);
		
		return $this->fetchAll($this->joinFieldLeading($select));
	}

	// 指定のシフト種別IDとメンバーIDで取得
	public function selectOverlapForTwc($shiftclass, $m_member_id, $reservationdate, $dayno, $page=1, $limit=0, $order=array('reserves.reservationdate ASC'))
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

				->where('shifts.m_dockind_id IN (?)', $subselect1)
				->where('shifts.dayno = ?', $dayno)

				;

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")

				->where('reserves.m_shift_id IN (?)', $subselect2)
				->where('reserves.reservationdate = ?', $reservationdate)

				->order($order);

		if ($m_member_id !== 0)
			$select->where('reserves.m_member_id_reserver = ?', $m_member_id);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($this->setJoinFieldTwc($select));
	}
	
	// 関大(新仕様)：指定の期間、場所に属する受入数を取得
	public function selectLimitFromPlaceIdAndWeektop($termid, $placeid, $weektop)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
	
		// termidを条件に入れると、学期の境目のデータは表示されなくなる
		->where('shifts.m_term_id = ?', $termid)
		->where('shifts.m_place_id = ?', $placeid)
		;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('reserves' => $this->_name),
				array('reserves.id', 'EXTRACT(DOW FROM to_timestamp(CAST(reserves.reservationdate AS TEXT),\'yyyy/mm/dd\')) AS dow' ))
	
				->where('reserves.m_shift_id IN (?)', $subselect2)
				->where('EXTRACT(DOW FROM to_timestamp(CAST(reserves.reservationdate AS TEXT),\'yyyy/mm/dd\')) >= 1')
				->where('EXTRACT(DOW FROM to_timestamp(CAST(reserves.reservationdate AS TEXT),\'yyyy/mm/dd\')) <= 5')
				->where('reservationdate >= ?', $weektop)
				->where('reservationdate <= (date ? + 4)', $weektop)
	
				->order(array('dow ASC','shifts.dayno ASC'))
	
				->joinLeft(
						array('shifts' => 'm_shifts'),
						'reserves.m_shift_id = shifts.id',
						array('shifts.dayno')
				)
				;
	
		return $this->fetchAll($select);
	}
	
	// 津大：指定の期間、シフト種別に属する受入数を取得
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
		->from(array('reserves' => $this->_name),
				array('reserves.id', 'EXTRACT(DOW FROM to_timestamp(CAST(reserves.reservationdate AS TEXT),\'yyyy/mm/dd\')) AS dow' ))
	
				->where('reserves.m_shift_id IN (?)', $subselect2)
				->where('EXTRACT(DOW FROM to_timestamp(CAST(reserves.reservationdate AS TEXT),\'yyyy/mm/dd\')) >= 1')
				->where('EXTRACT(DOW FROM to_timestamp(CAST(reserves.reservationdate AS TEXT),\'yyyy/mm/dd\')) <= 5')
				->where('reservationdate >= ?', $weektop)
				->where('reservationdate <= (date ? + 4)', $weektop)
	
				->order(array('dow ASC','shifts.dayno ASC'))
	
				->joinLeft(
						array('shifts' => 'm_shifts'),
						'reserves.m_shift_id = shifts.id',
						array('shifts.dayno')
				)
				->joinLeft(
						array('dockinds' => 'm_dockinds'),
						'dockinds.id = shifts.m_dockind_id',
						array('dockinds.shiftclass')
				)
				;
		
		return $this->fetchAll($select);
	}
	
	// 予約IDから関連情報を含めて取得
	public function GetSelectFromReserveId($reserveid, $order=array())
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('reserves' => $this->_name), "*")
	
				->where('reserves.id = ?', $reserveid)
				;
	
		if (count($order) > 0)
			$select->order($order);
		
	
		if (APPLICATION_TYPE != 'twc')
		{
			return $this->fetchRow($this->setJoinFieldNoDuplicateId($select));
		}
		else
		{
			return $this->fetchRow($this->setJoinFieldNoDuplicateIdTwc($select));
		}
	}
	
	// 20160119 多重送信防止
	public function selectDuplicateRow($m_member_id, $m_shift_id, $reservationdate, $nendo)
	{
		$select = $this->select();
		$select->where('m_member_id_reserver = ?', $m_member_id);
		$select->where('m_shift_id = ?', $m_shift_id);
		$select->where('reservationdate = ?', $reservationdate);
		$select->where('nendo = ?', $nendo);
	
		return $this->fetchAll($select);
	}

}
