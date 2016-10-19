<?php
require_once('CommonController.php');

class StaffController extends CommonController
{
	public $dowmax = 5;
	
	public function init()
	{
		parent::init();
		
		$this->_helper->AclCheck('Management', 'View');
	}

	// トップ
	public function indexAction()
	{
		$this->view->assign('subtitle', 'トップページ');
		////////////////////////////////
		// 新着情報 作成
		// 自身の担当でコメントがついていないものの更新日時
		// コメントがついているものならコメントの更新日時
		// 上記の中から最新5件を表示
		$tReserves = new Class_Model_TReserves();
		$tHistory = new Class_Model_TReserveHistory();
		$reserves = $tHistory->selectFromStaffNews(Zend_Auth::getInstance()->getIdentity()->id, 5, 0);

		$arrayReserves = $reserves->toArray();
		foreach ($arrayReserves as &$reserve)
		{
			// 予約 or 履歴 or 相談中
			if (strtotime(Zend_Registry::get('nowdatetime')) < strtotime($reserve['reservationdate'] . ' ' . $reserve['m_timetables_starttime']))
				$reserve['type'] = 0;	// 予約
			else if (strtotime(Zend_Registry::get('nowdatetime')) > strtotime($reserve['reservationdate'] . ' ' . $reserve['m_timetables_endtime']))
				$reserve['type'] = 1;	// 履歴
			else
				$reserve['type'] = 2;	// 相談中
		}

		// おしらせ5件
		$tInfomations = new Class_Model_TInfomations();
		$infomations = $tInfomations->selectLimitedAll("createdate desc", "5");

		$this->view->assign('reserves', $reserves);			// 予約全部(不要か？)
		$this->view->assign('news', $arrayReserves);		// 新着

		// more部分の新着を区別して取得
		$moreReserves = $tHistory->selectFromStaffNews(Zend_Auth::getInstance()->getIdentity()->id, 20, 5);
		$arrayMoreReserves = $moreReserves->toArray();
		foreach ($arrayMoreReserves as &$reserve)
		{
			// 予約 or 履歴 or 相談中
			if (strtotime(Zend_Registry::get('nowdatetime')) < strtotime($reserve['reservationdate'] . ' ' . $reserve['m_timetables_starttime']))
				$reserve['type'] = 0;	// 予約
			else if (strtotime(Zend_Registry::get('nowdatetime')) > strtotime($reserve['reservationdate'] . ' ' . $reserve['m_timetables_endtime']))
				$reserve['type'] = 1;	// 履歴
			else
				$reserve['type'] = 2;	// 相談中
		}
		$this->view->assign('morenews', $arrayMoreReserves);

		$this->view->assign('infomations', $infomations);	// おしらせ

		// 当日の予約一覧
		$nowdate = Zend_Registry::get('nowdate');
		$nowdatereserves = $tReserves->selectFromTermRange($nowdate, $nowdate, 1, 0, array('m_timetables_starttime ASC'));
		$this->view->assign('nowdatereserves', $nowdatereserves);

	}

	// シフト入力画面
	public function shiftAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '学期単位のシフト入力');
		
		$dowarray = $this->getDowArray();
		$this->view->assign('dowarray', $dowarray);

		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAllDisplay();
		$this->view->assign('campuses', $campuses);

		$campusid = $this->getRequest()->shiftclass;
		if (empty($campusid))
			$campusid = $campuses[0]->id;

		$campus = $mCampuses->selectFromId($campusid);
		$this->view->assign('campusname', $campus->campus_name);

		$this->view->assign('campusid', $campusid);

		// 期間取得
		$mTerms = new Class_Model_MTerms();

		$allterm = $mTerms->getThisTermAndNextTermFromDate();
		$this->view->assign('allterm', $allterm);

		$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
		$term = $mTerms->getTermFromDate($ymd);

		$termid = $this->getRequest()->termid;
		if (empty($termid))
			$termid = $term->id;
		
		$term = $mTerms->selectFromId($termid);

		$this->view->assign('termid', $termid);

		if (!empty($term))
		{
			$this->view->assign('term', $term);

			$mShifts = new Class_Model_MShifts();

			// シフト取得
			// ※ 同じキャンパス内の最初の場所の最初の文書タイプのみ取得する
			$mDockinds = new Class_Model_MDockinds();
			$dockinds	= $mDockinds->selectAllDisplay();

			$mPlaces = new Class_Model_MPlaces();
			$places = $mPlaces->selectFromCampusId($campusid);

			$shifts = $mShifts->selectShiftGroup($termid, $dockinds[0]->id, $places[0]->id);

			$this->view->assign('shifts', $shifts);
		}

		$this->view->assign('dowmax', $this->dowmax);

		if (!empty($templates))
		{
			$this->_helper->viewRenderer->setNoRender();
			$html = $this->view->render($templates);
			$this->getResponse()->setBody($html);
		}
	}
	
	// 前の学期のシフトを引き継ぐ
	public function copypreviousshiftsAction()
	{
		$termid = $this->getRequest()->termid;
		$campusid = $this->getRequest()->shiftclass;
		
		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->selectFromId($termid);
		$pre_term = $mTerms->getPreviousTermFromStartDate($term->startdate);
		
		$tStaffshifts = new Class_Model_TStaffshifts();
		$staffshifts = $tStaffshifts->insertFromMemberIdAndTermIdAndCampusId(Zend_Auth::getInstance()->getIdentity()->id, $termid, $pre_term->id);
		
		exit;
	}

	// シフト入力取得(ajax: 戻り値は JSONの配列)
	public function getshiftinputAction()
	{
		$actionName = $this->getRequest()->actionname;
		
		// 期間取得
		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
		
		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->getTermFromDate($ymd);

		$termid = $this->getRequest()->termid;
		if (empty($termid))
			$termid = $term->id;
		
		$term = $mTerms->selectFromId($termid);
		$pre_term = $mTerms->getPreviousTermFromStartDate($term->startdate);

		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAllDisplay();

		$campusid = $this->getRequest()->shiftclass;
		if (empty($campusid))
			$campusid = $campuses[0]->id;

		$mPlaces = new Class_Model_MPlaces();
		$places = $mPlaces->selectFromCampusId($campusid);

		// シフト取得
		// ※ 同じキャンパス内の最初の場所の最初の文書タイプのみ取得する
		$mDockinds = new Class_Model_MDockinds();
		$dockinds	= $mDockinds->selectAllDisplay();

		$mShifts = new Class_Model_MShifts();
		$shifts = $mShifts->selectShiftGroup($termid, $dockinds[0]->id, $places[0]->id);

		// シフトの連番の最初と最後を取得
		$startno = $shifts[0]->dayno;
		$endno = $shifts[count($shifts) - 1]->dayno;

		// 一日毎のシフト時間帯の数
		$countDayno = $mShifts->countDayno($termid);

		// スタッフシフト取得
		$tStaffshifts = new Class_Model_TStaffshifts();
		
		// シフトカレンダーとシフト入力で取得するスタッフシフトを区別する
		if($actionName == "calendar")
		{
			$shiftinput = array();
			for($i = 0; $i < 5; $i++)
			{
				$staffshifts = $tStaffshifts->selectFromTermIdAndCampusIdIncludingDetailsPerDay($campusid, $ymd, $i, Zend_Auth::getInstance()->getIdentity()->id);
				
				foreach ($staffshifts as $staffshift)
				{
					$dayno = $staffshift->m_shifts_dayno;
					$dow = $staffshift->dow;
						
					if (empty($shiftinput[$dow][$dayno]))
						$shiftinput[$dow][$dayno] = $staffshift->toArray();
				}
			}
		}
		else
		{
			$staffshifts = $tStaffshifts->selectFromMemberIdAndTermIdAndCampusId(Zend_Auth::getInstance()->getIdentity()->id, $termid, $campusid);
			
			$pre_staffshifts = $tStaffshifts->selectFromMemberIdAndTermIdAndCampusId(Zend_Auth::getInstance()->getIdentity()->id, $pre_term->id, $campusid);
			
			// 今学期のシフト数が0件、かつ前学期のシフト数が1件以上であればフラグを割り当てる
			$button_flg = (count($staffshifts) < 1 && count($pre_staffshifts) > 0) ? 'show' : 'hide';
			
			// 重複をなくし曜日ベースで配列化する
			$shiftinput = array();
			foreach ($staffshifts as $staffshift)
			{
				$dayno = $staffshift->m_shifts_dayno;
				$dow = $staffshift->dow;
			
				if (empty($shiftinput[$dow][$dayno]))
					$shiftinput[$dow][$dayno] = $staffshift->toArray();
			}
		}

		// シフト時間一覧を作成
		$list = array();
		for ($dow = 0; $dow < 7; $dow++)
		{
			if (empty($shiftinput[$dow]))
				$list[$dow] = '';
			else
			{
				$starttime = '';
				$endtime = '';
				foreach ($shiftinput[$dow] as $dayno => $staffshift)
				{
					if ($starttime == '')
						$starttime = $staffshift['m_timetables_starttime'];

					if ($endtime == '')
						$endtime = $staffshift['m_timetables_endtime'];

					if (strtotime($starttime) > strtotime($staffshift['m_timetables_starttime']))
						$starttime = $staffshift['m_timetables_starttime'];

					if (strtotime($endtime) < strtotime($staffshift['m_timetables_endtime']))
						$endtime = $staffshift['m_timetables_endtime'];
				}
				$starttime = date('H:i', strtotime($starttime));
				$endtime = date('H:i', strtotime($endtime));
				$list[$dow] = $starttime . '-' . $endtime;
			}
		}

		// 各曜日ごとのコマの属性を設定する
		if(!empty($shiftinput))
		{
			foreach ($shiftinput as $dow => $dows)
			{
				if(strtotime($term->shift_startdate) > strtotime(Zend_Registry::get('nowdate')) || strtotime($term->shift_enddate) < strtotime(Zend_Registry::get('nowdate')))
				{
					if (count($dows) >= $this->dowmax)
					{	// 各曜日の設定最大数に達している
						foreach($dows as $dayno => $staffshift)
						{
							if ($dayno > $startno && $dayno < $endno && !empty($shiftinput[$dow][$dayno - 1]) && !empty($shiftinput[$dow][$dayno + 1]))
								$shiftinput[$dow][$dayno]['class'] = 'expired attached inter';		// 削除できない
							else
								$shiftinput[$dow][$dayno]['class'] = 'expired attached terminal';	// 通常
						}
		
						foreach($shifts as $shift)
						{
							if (empty($shiftinput[$dow][$shift->dayno]))
								$shiftinput[$dow][$shift->dayno]['class'] = 'expired limit';	// 空いている曜日に最大数オーバーの属性を設定
						}
					}
					else
					{
						foreach($dows as $dayno => $staffshift)
						{
							if ($dayno > $startno && $dayno < $endno && !empty($shiftinput[$dow][$dayno - 1]) && !empty($shiftinput[$dow][$dayno + 1]))
								$shiftinput[$dow][$dayno]['class'] = 'expired attached inter';		// 削除できない
							else
								$shiftinput[$dow][$dayno]['class'] = 'expired attached terminal';	// 通常
						}
					}
			
					// 何もデータがない部分を埋める
					for ($dow = 1; $dow <= 5; $dow++)
					{
						for ($dayno = 1; $dayno <= $countDayno; $dayno++)
						{
							if (empty($shiftinput[$dow][$dayno]['class']))
							{
								$shiftinput[$dow][$dayno]['class'] = 'expired';
							}
						}
					}
				}
				else
				{
					if (count($dows) >= $this->dowmax)
					{	// 各曜日の設定最大数に達している
						foreach($dows as $dayno => $staffshift)
						{
							if ($dayno > $startno && $dayno < $endno && !empty($shiftinput[$dow][$dayno - 1]) && !empty($shiftinput[$dow][$dayno + 1]))
								$shiftinput[$dow][$dayno]['class'] = 'attached inter';		// 削除できない
							else
								$shiftinput[$dow][$dayno]['class'] = 'attached terminal';	// 通常
						}
					
						foreach($shifts as $shift)
						{
							if (empty($shiftinput[$dow][$shift->dayno]))
								$shiftinput[$dow][$shift->dayno]['class'] = 'limit';	// 空いている曜日に最大数オーバーの属性を設定
						}
					}
					else
					{
						foreach($dows as $dayno => $staffshift)
						{
							if ($dayno > $startno && $dayno < $endno && !empty($shiftinput[$dow][$dayno - 1]) && !empty($shiftinput[$dow][$dayno + 1]))
								$shiftinput[$dow][$dayno]['class'] = 'attached inter';		// 削除できない
							else
								$shiftinput[$dow][$dayno]['class'] = 'attached terminal';	// 通常
						}
					}
				}
			}
		}
		else
		{
			// $shiftinputが空でforeachが回らない場合も同様の処理を行う
			if(strtotime($term->shift_startdate) > strtotime(Zend_Registry::get('nowdate')) || strtotime($term->shift_enddate) < strtotime(Zend_Registry::get('nowdate')))
			{
				// 何もデータがない部分を埋める
				for ($dow = 1; $dow <= 5; $dow++)
				{
					for ($dayno = 1; $dayno <= $countDayno; $dayno++)
					{
						if (empty($shiftinput[$dow][$dayno]['class']))
						{
							$shiftinput[$dow][$dayno]['class'] = 'expired';
						}
					}
				}
			}
		}
		
		if($actionName !== "calendar")
		{
			$termdata = array(
					'name' 				=>	$term->year . '年度 ' . $term->name,
					'shift_startdate'	=>	$term->shift_startdate,
					'shift_enddate'		=>	$term->shift_enddate,
			);
			
			echo json_encode(array('list' => $list, 'shiftinput' => $shiftinput, 'button_flg' => $button_flg, 'termdata' => $termdata));
		}
		else
		{
			echo json_encode(array('list' => $list, 'shiftinput' => $shiftinput));
		}
		exit;
	}

	// シフト入力設定(ajax: 戻り値は JSONの配列)
	public function setshiftinputAction()
	{
		$id	= $this->getRequest()->shiftclass;

		$dayno		= $this->getRequest()->dayno;
		$dow		= $this->getRequest()->dow;

		if (!empty($id) && !empty($dow) && !empty($dayno))
		{
			// 期間取得
			$ymd = $this->getRequest()->ymd;
			if (empty($ymd))
				$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));

			$mTerms = new Class_Model_MTerms();
			$term = $mTerms->getTermFromDate($ymd);

			$termid = $this->getRequest()->termid;
			if (empty($termid))
				$termid = $term->id;

			// 指定の期の指定のキャンパスに属するシフトを取得
			$mShifts = new Class_Model_MShifts();
			
			$shifts = $mShifts->selectFromTermIdAndCampusIdAndDayno($termid, $id, $dayno);

			// 取得したシフト全てに対してスタッフを登録
			$tStaffshifts	= new Class_Model_TStaffshifts();

			$tStaffshifts->getAdapter()->beginTransaction();
			try
			{

				foreach ($shifts as $shift)
				{
					$params = array(
							'm_member_id'	=> Zend_Auth::getInstance()->getIdentity()->id,
							'm_shift_id'	=> $shift->id,
							'dow'			=> $dow,
					);

					$staffshiftid = $tStaffshifts->insert($params);
				}

				// シフト入力と同時に受入数を一つ増やす
				$tShiftlimits = new Class_Model_TShiftlimits();

				$shiftlimits = $tShiftlimits->selectLimitFromToday($termid, $id, $dayno, $dow);

				if (!empty($shiftlimits))
				{
					$params = array(
							'reservelimit' => new Zend_Db_Expr('(reservelimit + 1)'),
					);
					$tShiftlimits->updateFromTermIdAndCampusIdAndDaynoAndDow($termid, $id, $dayno, $dow, $params);
				}
				else
				{
					$params = array(
							'reservelimit' => '1',
					);
					$tShiftlimits->insertFromTermIdAndCampusIdAndDaynoAndDow($termid, $id, $dayno, $dow, $params);
				}

				$tStaffshifts->getAdapter()->commit();
			}
			catch (Exception $e)
			{
				$tStaffshifts->getAdapter()->rollback();
				echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
				exit;
			}

			echo json_encode('success');
		}
		else
			echo json_encode('failed');

		exit;
	}


	// シフト入力削除(ajax: 戻り値は JSONの配列)
	public function deleteshiftinputAction()
	{
		$id	= $this->getRequest()->shiftclass;

		$dayno	= $this->getRequest()->dayno;
		$dow	= $this->getRequest()->dow;
		
		if (!empty($id) && !empty($dow) && !empty($dayno))
		{
			// 期間取得
			$ymd = $this->getRequest()->ymd;
			if (empty($ymd))
				$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));

			$mTerms = new Class_Model_MTerms();
			$term = $mTerms->getTermFromDate($ymd);

			$termid = $this->getRequest()->termid;
			if (empty($termid))
				$termid = $term->id;

			// 指定されたキャンパス、連番、曜日に対応するスタッフシフトをすべて削除
			$tStaffshifts	= new Class_Model_TStaffshifts();

			$tStaffshifts->getAdapter()->beginTransaction();
			try
			{
				$params = array(
						'reservelimit' => new Zend_Db_Expr('(CASE WHEN reservelimit > 0 THEN (reservelimit - 1) ELSE 0 END)'),
				);
				
				$term = $mTerms->selectFromId($termid);
				$term_next = $mTerms->getNextTermFromDate($term->startdate);
				
				$tStaffshifts->deleteFromShiftinputByMultiTermId(array($termid, $term_next->id), $id, $dayno, Zend_Auth::getInstance()->getIdentity()->id, $dow);

				// シフト削除と同時に受入数を一つ減らす
				$tShiftlimits = new Class_Model_TShiftlimits();
				$shiftlimits = $tShiftlimits->selectLimitFromToday($termid, $id, $dayno, $dow);

				if (!empty($shiftlimits))
				{
					$tShiftlimits->updateFromTermIdAndCampusIdAndDaynoAndDow($termid, $id, $dayno, $dow, $params);
				}

				$tStaffshifts->getAdapter()->commit();
			}
			catch (Exception $e)
			{
				$tStaffshifts->getAdapter()->rollback();
				echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
				exit;
			}

			echo json_encode('success');
		}
		else
			echo json_encode('failed');

		exit;
	}

	// シフトカレンダー
	public function calendarAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', 'シフトカレンダー');

		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAllDisplay();
		$this->view->assign('campuses', $campuses);

		$campusid = $this->getRequest()->shiftclass;
		if (empty($campusid))
			$campusid = $campuses[0]->id;

		$campus = $mCampuses->selectFromId($campusid);
		$this->view->assign('campusname', $campus->campus_name);

		$this->view->assign('campusid', $campusid);
		
		// 期間取得
		$mTerms = new Class_Model_MTerms();

		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
		$this->view->assign('ymd', $ymd);
		
		$w = date('w', strtotime($ymd));

		// 週初め取得
		$weektop = date('Y-m-d', strtotime('-' . ($w - 1) . ' day', strtotime($ymd)));	// 月曜
		$weekend = date('Y-m-d', strtotime('+4 day', strtotime($weektop)));				// 金曜
		$lastweek = date('Y-m-d', strtotime('-7 day', strtotime($weektop)));
		$nextweek = date('Y-m-d', strtotime('+7 day', strtotime($weektop)));
		$weeks = array();
		for ($dow = 0; $dow < 5; $dow++)
			$weeks[] = date('Y-m-d', strtotime('+' . $dow . ' day', strtotime($weektop)));

		//土曜日の場合来週を表示する
		$this->view->assign('we', $w);

		if($w == 6){
			$weektop = $nextweek;
			$weekend = date('Y-m-d', strtotime('+4 day', strtotime($weektop)));
			$lastweek = date('Y-m-d', strtotime('-7 day', strtotime($weektop)));
			$nextweek = date('Y-m-d', strtotime('+7 day', strtotime($weektop)));
			$weeks = array();
			for ($dow = 0; $dow < 5; $dow++)
				$weeks[] = date('Y-m-d', strtotime('+' . $dow . ' day', strtotime($weektop)));
		}

		$this->view->assign('weektop', $weektop);
		$this->view->assign('weekend', $weekend);
		$this->view->assign('lastweek', $lastweek);
		$this->view->assign('nextweek', $nextweek);
		$this->view->assign('weeks', $weeks);


		$term = $mTerms->getTermFromDate($ymd);
		if (!empty($term))
		{
			$this->view->assign('term', $term);

			$mShifts = new Class_Model_MShifts();

			// シフト取得
			// ※ 同じキャンパス内の最初の場所の最初の文書タイプのみ取得する
			$mDockinds = new Class_Model_MDockinds();
			$dockinds	= $mDockinds->selectAllDisplay();

			$mPlaces = new Class_Model_MPlaces();
			$places = $mPlaces->selectFromCampusId($campusid);

			$shifts = $mShifts->selectShiftGroup($term->id, $dockinds[0]->id, $places[0]->id);

			$this->view->assign('shifts', $shifts);

		}

		// 文書の種類と相談場所と授業科目マスタ
		$mDockinds = new Class_Model_MDockinds();
		$dockinds = $mDockinds->selectAllDisplay();

		$mPlaces = new Class_Model_MPlaces();
		$places = $mPlaces->selectAllDisplay();

		$this->view->assign('dockinds', $dockinds);
		$this->view->assign('places', $places);
		$this->view->assign('progresssal', $this->progresssal);

		$this->view->assign('dowmax', $this->dowmax);
	}

	// ユーザー検索(ajax: 戻り値は JSONの配列)
	public function searchuserAction()
	{
		$result = array('result' => 'fail');
		$student_id = $this->getRequest()->studentid;
		if (empty($student_id) || $student_id == '')
		{
			echo json_encode($result);
			exit;
		}

		$mMembers = new Class_Model_MMembers();
		$member = $mMembers->selectFromStudentIdJP($student_id);
		if (empty($member))
		{
			echo json_encode($result);
			exit;
		}
		
		$mSubjects = new Class_Model_MSubjects();
		$subject = $mSubjects->selectFromStudentIdJP($student_id);
		
		$result['subject']	= $subject->toArray();
		$result['member']	= $member->toArray();
		$result['result']	= 'success';

		echo json_encode($result);
		exit;
	}

	// 駆け込み予約(ajax: 戻り値は JSONの配列)
	public function newreserveAction()
	{
		$isUpload = !empty($_FILES['item7']);

		$m_member_id = $this->getRequest()->reserver;
		$reservationdate = $this->getRequest()->reservationdate;

		$m_dockind_id = $this->getRequest()->item1;
		$m_place_id = $this->getRequest()->item2;
		$dayno = $this->getRequest()->dayno;

		$jwaricd = $this->getRequest()->item4;
		$submitdate = $this->getRequest()->submitdate;
		$progress = $this->getRequest()->item6;
		$question = $this->getRequest()->item8;

		// パラメータチェック
		$result = array('error' => '');
		
		$translate = Zend_Registry::get('Zend_Translate');

		if (empty($m_member_id))
			$result['error'] .= $translate->_("相談者を指定してください") . "\n";

		if (empty($m_dockind_id))
			$result['error'] .= $translate->_("文書の種類を選択してください") . "\n";

		if (empty($m_place_id))
			$result['error'] .= $translate->_("相談場所を選択してください") . "\n";

		if (empty($m_subject_id))
			$m_subject_id = null;

		if (empty($submitdate))
			$submitdate = null;

		if (empty($progress))
			$progress = null;

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		// 期間、文書種別、相談場所、通し番号からシフトを確定する
		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->getTermFromDate($reservationdate);

		$mShifts = new Class_Model_MShifts();
		$shift = $mShifts->selectShift($term->id, $m_dockind_id, $m_place_id, $dayno);

		// 同一キャンパスの同時刻に同一人物から予約が入っていないかチェック
		$mPlaces = new Class_Model_MPlaces();
		$place = $mPlaces->selectFromId($m_place_id);

		$tReserves	= new Class_Model_TReserves();
		$myreserves = $tReserves->selectOverlap($place->m_campus_id, $m_member_id, $reservationdate, $dayno);
		if (count($myreserves) > 0)
			$result['error'] .= $translate->_("既に同じ相談者の予約が登録されています") . "\n";

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		if (empty($question))
			$question = '';
		
		$mMembers = new Class_Model_MMembers();
		$member = $mMembers->selectFromId($m_member_id);
		
		// 現在の年度を取得
		$mNendo = new Class_Model_MNendo();
		$nendo_row = $mNendo->selectRow();
			
		if (empty($jwaricd))
		{
			$jwaricd		= '';
			$class_subject	= '';
			$kyoin			= '';
		}
		else
		{
			$mSubjects	= new Class_Model_MSubjects();
			$subject = $mSubjects->selectFromId($jwaricd);
			$class_subject = $subject->subjects_class_subject;
				
			if(!empty($subject->subjects_jyu_knr_no) && !empty($subject->subjects_jyu_nendo))
			{
				// 担当教員の取得
				$tJyugyoKanri	= new Class_Model_TJyugyoKanri();
				$teachers		= $tJyugyoKanri->selectFromId($subject->subjects_jyu_knr_no, $subject->subjects_jyu_nendo);
			
				if(!empty($teachers->tanto_other_falg))
					$kyoin = $teachers->sekiji_top_kyoinmei . ' 他';
				elseif(!empty($teachers->sekiji_top_kyoinmei))
					$kyoin = $teachers->sekiji_top_kyoinmei;
				else
					$kyoin = '';
			}
			else
			{
				$kyoin = '';
			}
		}

		// 例:RSV_ID2014080123595912345678
		$id = "RSV_ID" . date("YmdHis", time()) . sprintf("%08d", mt_rand(0,99999999));

		$params = array(
				'id'					=> $id,
				'm_member_id_reserver'	=> $m_member_id,
				'student_id'			=> $member->student_id,
				'name_jp'				=> $member->name_jp,
				'email'					=> $member->email,
				'sex'					=> $member->sex,
				'setti_cd'				=> $member->setti_cd,
				'syozkcd1'				=> $member->syozkcd1,
				'syozkcd2'				=> $member->syozkcd2,
				'entrance_year'			=> $member->entrance_year,
				'gaknenkn'				=> $member->gaknenkn,
				'reservationdate'		=> $reservationdate,
				'nendo'					=> $nendo_row->current_nendo,
				'm_shift_id'			=> $shift->id,
				'jwaricd'				=> $jwaricd,
				'class_subject'			=> $class_subject,
				'sekiji_top_kyoinmei'	=> $kyoin,
				'submitdate'			=> $submitdate,
				'progress'				=> $progress,
				'question'				=> $question,
				'run_reserve'			=> '1',
		);

		$tReserves->getAdapter()->beginTransaction();
		try
		{
			$params['m_member_id_reserver']	= $m_member_id;
			$this->reserveid = $tReserves->insert($params);
			
			// 予約変更履歴テーブル更新
			$params_history = array(
					't_reserve_id' 			=> $this->reserveid,
					'm_member_id_reserver'	=> $m_member_id,
					'student_id'			=> $member->student_id,
					'name_jp'				=> $member->name_jp,
					'email'					=> $member->email,
					'sex'					=> $member->sex,
					'setti_cd'				=> $member->setti_cd,
					'syozkcd1'				=> $member->syozkcd1,
					'syozkcd2'				=> $member->syozkcd2,
					'entrance_year'			=> $member->entrance_year,
					'gaknenkn'				=> $member->gaknenkn,
					'reservationdate'		=> $reservationdate,
					'nendo'					=> $nendo_row->current_nendo,
					'm_shift_id'			=> $shift->id,
					'jwaricd'				=> $jwaricd,
					'class_subject'			=> $class_subject,
					'submitdate'			=> $submitdate,
					'progress'				=> $progress,
					'question'				=> $question,
					'run_reserve'			=> '1',
					'historyclass'			=> '1',
			);
			
			$tReserveHistory = new Class_Model_TReserveHistory();
			$tReserveHistory->insert($params_history);

			// ファイルアップロード
			if ($isUpload)
			{
				$tFiles = new Class_Model_TFiles();

				$filecount = count($_FILES['item7']['name']);

				for ($index = 0; $index < $filecount; $index++)
				{
					if (!empty($_FILES['item7']['tmp_name'][$index]))
					{
						$t_file_id = $tFiles->insertFile(
								$_FILES['item7']['tmp_name'][$index],
								$_FILES['item7']['name'][$index],
								$_FILES['item7']['type'][$index],
								$_FILES['item7']['size'][$index]);

						// ワークファイルとして登録
						$tWorkFile = new Class_Model_TWorkFiles();
						$wfparams = array(
								'm_member_id'		=> $m_member_id,
								't_file_id'			=> $t_file_id,
						);
						$workfileid = $tWorkFile->insert($wfparams);

						// ワークファイルと予約ファイルを関連付け
						$tReserveFile = new Class_Model_TReserveFiles();
						$rfparams = array(
								't_reserve_id'		=> $this->reserveid,
								't_work_file_id'	=> $workfileid,
						);
						$reservefileid = $tReserveFile->insert($rfparams);
					}
				}
			}

			$tReserves->getAdapter()->commit();
		}
		catch (Exception $e)
		{
			$tReserves->getAdapter()->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		// メール通知とリマインダー
		//Sendmail::noticeMail($this, Sendmail::NOTICE_NEW, $this->reserveid);

		echo json_encode(array('success' => $this->reserveid));	// 成功時はidを返す
		exit;
	}


	// 今週の予約取得(ajax: 戻り値は JSONの配列)
	public function getweekreserveAction()
	{
		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAllDisplay();

		$campusid = $this->getRequest()->shiftclass;
		if (empty($campusid))
			$campusid = $campuses[0]->id;

		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
		$this->view->assign('ymd', $ymd);

		$w = date('w', strtotime($ymd));
		$weektop = date('Y-m-d', strtotime('-' . ($w - 1) . ' day', strtotime($ymd)));	// 月曜
		$weekend = date('Y-m-d', strtotime('+4 day', strtotime($weektop)));				// 金曜

		$tReserves = new Class_Model_TReserves();

		$reserves = $tReserves->selectFromCampusIdAndRange($campusid, $weektop, $weekend);

		$reserves = $reserves->toArray();

		// 曜日を設定する
		foreach ($reserves as &$reserve)
		{
			$reservationdate = $reserve['reservationdate'];
			$reserve['dow'] = date('w', strtotime($reservationdate));
		}

		echo json_encode($reserves);
		exit;
	}


	// 本日の予約取得(ajax: 戻り値は JSONの配列)
	public function gettodayreserveAction()
	{
		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAllDisplay();

		$campusid = $this->getRequest()->shiftclass;
		if (empty($campusid))
			$campusid = $campuses[0]->id;

		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
		$this->view->assign('ymd', $ymd);

		$tReserves = new Class_Model_TReserves();

		$reserves = $tReserves->selectFromCampusIdAndDate($campusid, $ymd);

		echo json_encode($reserves->toArray());
		exit;
	}


	// 本日のスタッフ取得(ajax: 戻り値は JSONの配列)
	public function gettodaystaffAction()
	{
		// 期間取得
		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));

		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->getTermFromDate($ymd);

		$dow = $this->getRequest()->dow;
		$dayno = $this->getRequest()->dayno;

		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAllDisplay();

		$campusid = $this->getRequest()->shiftclass;
		if (empty($campusid))
			$campusid = $campuses[0]->id;

		// スタッフシフト取得
		$tStaffshifts = new Class_Model_TStaffshifts();
		$staffshifts = $tStaffshifts->selectFromTodayStaffIncludingDetailsYmd($term->id, $campusid, $dayno, $dow, $ymd);

		// 同一スタッフの重複を削除
		$staffs = array();
		$staffshifts = $staffshifts->toArray();
		foreach ($staffshifts as $staffshift)
			$staffs[$staffshift['m_member_id']] = $staffshift;

		echo json_encode(array('staffs' => $staffs, 'count' => count($staffs)));
		exit;
	}


	// 指定シフトの予約取得(ajax: 戻り値は JSONの配列)
	public function getshiftreserveAction()
	{
		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
		$this->view->assign('ymd', $ymd);

		$dayno = $this->getRequest()->dayno;

		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAllDisplay();

		$campusid = $this->getRequest()->shiftclass;
		if (empty($campusid))
			$campusid = $campuses[0]->id;

		$tReserves = new Class_Model_TReserves();
		$reserves = $tReserves->selectOverlap($campusid, 0, $ymd, $dayno);

		echo json_encode($reserves->toArray());
		exit;
	}


	// 指導画面
	public function adviceAction()
	{
		$this->_helper->AclCheck('Management', 'Edit');

		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '予定/指導履歴');

		$this->view->assign('progresssal', $this->progresssal);

		$this->reserveid = $this->getRequest()->reserveid;
		$this->indexflg = 0;

		$tLeadings = new Class_Model_TLeadings();
		if (empty($this->reserveid))
		{
			$this->reserveid = 0;
			
			$ex = $tLeadings->selectFromChargeId(Zend_Auth::getInstance()->getIdentity()->id);
			if(count($ex) > 0)
				$this->indexflg = 1;
		}
		$tReserves = new Class_Model_TReserves();
		if (!empty($this->reserveid))
		{
			$reserver = $this->getRequest()->reserver;
			$this->view->assign('reserver', $reserver);

			$subjectid = $this->getRequest()->subjectid;
			$this->view->assign('subjectid', $subjectid);

			$chargeid = $this->getRequest()->chargeid;
			$this->view->assign('chargeid', $chargeid);

			$reserve = $tReserves->selectFromId($this->reserveid);
			$this->view->assign('reserve', $reserve);

			$mShifts = new Class_Model_MShifts();
			$shift = $mShifts->selectFromId($reserve->m_shift_id);
			$this->view->assign('shift', $shift);

			$this->view->assign('reserveid', $this->reserveid);

			// 相談時間を過ぎているか
			$reservetype = 0;
			if (strtotime(Zend_Registry::get('nowdatetime')) < strtotime($reserve->reservationdate . ' ' . $shift->m_timetables_starttime))
				$reservetype = 0;	// 予約時間前
			else if (strtotime(Zend_Registry::get('nowdatetime')) > strtotime($reserve->reservationdate . ' ' . $shift->m_timetables_endtime))
				$reservetype = 1;	// 予約時間終了後
			else
				$reservetype = 2;	// 相談中
			$this->view->assign('reservetype', $reservetype);

			// 添付ファイル
			$tReserveFile = new Class_Model_TReserveFiles();
			$reservefiles = $tReserveFile->selectFromReserveId($this->reserveid);
			$this->view->assign('reservefiles', $reservefiles);
			
			// 指導取得
			$tLeadingsTmp = new Class_Model_TLeadingsTmp();
			
			$leadingsTmp = $tLeadingsTmp->selectFromReserveIdAndMemberId($this->reserveid, Zend_Auth::getInstance()->getIdentity()->id);
			$leadTmp = $tLeadingsTmp->selectFromReserveId($this->reserveid);

			// 指導取得
			$leadings = $tLeadings->selectFromReserveIdAndMemberId($this->reserveid, Zend_Auth::getInstance()->getIdentity()->id);
			$lead = $tLeadings->selectFromReserveId($this->reserveid);
			if (!empty($leadingsTmp))
			{
				if(!empty($leadings->leading_comment))
					$leadingsTmp->leading_comment = $leadings->leading_comment;
				$this->view->assign('leadings', $leadingsTmp);
			
				$cancel_flag['canceled'] = '';
				if($leadingsTmp->cancel_flag == 1)
					$canceled[] = 'canceled';
				else
					$canceled[] = '';
			
				$this->view->assign('cancel_flag', $cancel_flag);
				$this->view->assign('canceled', $canceled);
			}
			elseif (!empty($leadings))
			{
				$this->view->assign('leadings', $leadings);

				$cancel_flag['canceled'] = '';
				if($leadings->cancel_flag == 1)
					$canceled[] = 'canceled';
				else
					$canceled[] = '';

				$this->view->assign('cancel_flag', $cancel_flag);
				$this->view->assign('canceled', $canceled);

				// コメント取得
				$tReserveComment = new Class_Model_TReserveComments();
				$reservecomments = $tReserveComment->selectFromReserveId($this->reserveid);
				if (!empty($reservecomments))
				{
					$this->view->assign('reservecomments', $reservecomments);

					// コメント添付ファイル取得
					$tReserveCommentFile = new Class_Model_TReserveCommentFiles();
					$reservecommentfiles = $tReserveCommentFile->selectFromReserveCommentId($reservecomments->id);
					$this->view->assign('reservecommentfiles', $reservecommentfiles);
				}
			}
			elseif(!empty($lead))
			{
				// 該当予約IDについて、ログインIDとは別IDでの指導履歴が存在する場合
				$reserveid = $this->getRequest()->reserveid;

				if (!empty($reserveid))
				{
					$this->view->assign('charge', $lead);
				}
				
				// コメント取得
				$tReserveComment = new Class_Model_TReserveComments();
				$reservecomments = $tReserveComment->selectFromReserveId($this->reserveid);
				if (!empty($reservecomments))
				{
					$this->view->assign('reservecomments', $reservecomments);
				
					// コメント添付ファイル取得
					$tReserveCommentFile = new Class_Model_TReserveCommentFiles();
					$reservecommentfiles = $tReserveCommentFile->selectFromReserveCommentId($reservecomments->id);
					$this->view->assign('reservecommentfiles', $reservecommentfiles);
				}
			}
			else
			{
				$cancel_flg['canceled'] = '';
				$checked[] = 'canceled';

				$this->view->assign('cancel_flg', $cancel_flg);
				$this->view->assign('checked', $checked);
			}

			// 自身が担当可能なシフトならスタッフシフト情報を取得
			$tStaffshifts = new Class_Model_TStaffshifts();
			$staffshift = $tStaffshifts->selectFromTodayStaffIncludingDetailsForAvailable(Zend_Auth::getInstance()->getIdentity()->id, $shift->id, date("w", strtotime($reserve->reservationdate)), $reserve->reservationdate);
			if (count($staffshift) > 0)
			{	// シフト的には担当可能

				// 同じ時間帯（同じ日、同じ順番）を担当済みでないか
				$mycharges = $tLeadings->selectFromChargeIdAndReserve(Zend_Auth::getInstance()->getIdentity()->id, $reserve->reservationdate, $shift->dayno);
				if (empty($mycharges) || count($mycharges) == 0)
				{	// 担当可能
					$this->view->assign('staffshift', $staffshift);
				}
			}
		}
		else
		{
			if (!empty($this->indexflg))
			{
				// トップページの場合
				$this->view->assign('reserve', null);
				$this->view->assign('indexflg', 1);
			}
			else
			{
				// シフトに対応する予約が存在しない場合
				$this->view->assign('reserve', null);
				$this->view->assign('indexflg', 0);
			}
		}

		$page = $this->getRequest()->page;
		if (!empty($page))
			$this->view->assign('page', $page);
	}

	// 担当解除
	public function releasechargeAction()
	{
		$this->_helper->AclCheck('Management', 'Edit');

		$reserveid = $this->getRequest()->reserveid;
		if (!empty($reserveid))
		{
			$tLeadings = new Class_Model_TLeadings();
			$tLeadings->getAdapter()->beginTransaction();
			try
			{
				// 指導削除
				$tLeadings->deleteFromReserveId($reserveid);

				// 予約コメント削除
				$tReserveComment = new Class_Model_TReserveComments();
				$reservecomments = $tReserveComment->selectFromReserveId($reserveid);
				if (!empty($reservecomments))
				{
					// コメント添付ファイル削除
					$tReserveCommentFile = new Class_Model_TReserveCommentFiles();
					$tReserveCommentFile->deleteFromReserveCommentId($reservecomments->id);
				}
				$tReserveComment->deleteFromReserveId($reserveid);

				$tLeadings->getAdapter()->commit();
			}
			catch (Exception $e)
			{
				$tLeadings->getAdapter()->rollback();
				die($e->getMessage());
				exit;
			}
		}

		$link = 'staff/advice/reserveid/' . $reserveid;

		$page = $this->getRequest()->page;
		if (!empty($page))
			$link .= '/page/' . $page;

		$this->_redirect($link);
	}

	// 担当と解除を切り替える(ajax: 戻り値は JSONの配列)
	// 担当時のコメント等はすべてからの状態で登録する
	public function changechargeAction()
	{
		$this->_helper->AclCheck('Management', 'Edit');

		$reserveid	= $this->getRequest()->reserveid;
		$flg 		= $this->getRequest()->flg;
		
		$translate = Zend_Registry::get('Zend_Translate');
		
		if (!empty($reserveid))
		{
			$tLeadings = new Class_Model_TLeadings();
			$leadings = $tLeadings->selectFromReserveId($reserveid);

			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			try
			{
				if (empty($leadings))
				{
					// 画面上の動作と実際の処理が同一であることを確認する
					// (同時に別の画面での処理が行われた場合を考慮)
					if($flg != 0)
					{
						$db->rollback();
						echo json_encode(array('error' => $translate->_("担当者情報が更新されました。ページの更新を行ってください") . "\n"));
						exit;
					}
					
					// 担当
					$params = array(
							't_reserve_id'			=> $reserveid,
							'submitdate'			=> Zend_Registry::get('nowdate'),
							'm_member_id_charge'	=> $this->member->id,
							'staff_no'				=> $this->member->staff_no,
							'name_jp'				=> $this->member->name_jp,
							'counsel'				=> '',
							'teaching'				=> '',
							'remark'				=> '',
							'summary'				=> '',
							'leading_comment'		=> '',
					);
					$leadingid = $tLeadings->insert($params);
				}
				else
				{
					// 画面上の動作と実際の処理が同一であることを確認する
					// (同時に別の担当者が担当することを防ぐため)
					if($flg != 1 || $leadings->staff_no != $this->member->staff_no)
					{
						$db->rollback();
						echo json_encode(array('error' => $translate->_("担当者情報が更新されました。ページの更新を行ってください") . "\n"));
						exit;
					}
					
					// 解除
					// 一時指導が存在すれば同時に削除
					$tLeadingsTmp = new Class_Model_TLeadingsTmp();
					$tLeadingsTmp->deleteFromReserveId($reserveid);
					
					$tLeadings->deleteFromReserveId($reserveid);
				}

				$db->commit();
			}
			catch (Exception $e)
			{
				$db->rollback();
				echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
				exit;
			}
		}
		echo json_encode(array('success' => $reserveid));	// 成功時は予約idを返す
		exit;

	}

	// 指導画面（リードオンリー用）
	public function viewAction()
	{
		$this->_helper->AclCheck('Management', 'Edit');

		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '指導履歴');

		$this->view->assign('progresssal', $this->progresssal);

		$this->reserveid = $this->getRequest()->reserveid;
		if (!empty($this->reserveid))
		{
			$tReserves = new Class_Model_TReserves();
			$reserve = $tReserves->selectFromId($this->reserveid);
			$this->view->assign('reserve', $reserve);

			$mShifts = new Class_Model_MShifts();
			$shift = $mShifts->selectFromId($reserve->m_shift_id);
			$this->view->assign('shift', $shift);

			$this->view->assign('reserveid', $this->reserveid);

			// 添付ファイル
			$tReserveFile = new Class_Model_TReserveFiles();
			$reservefiles = $tReserveFile->selectFromReserveId($this->reserveid);
			$this->view->assign('reservefiles', $reservefiles);

			// 授業科目
			$subjectid = $this->getRequest()->subjectid;
			if (!empty($subjectid))
			{
				$mSubject = new Class_Model_MSubjects();
				$subject = $mSubject->selectFromId($subjectid);
				$this->view->assign('subject', $subject);
			}

			// 指導取得
			$tLeadings = new Class_Model_TLeadings();
			$leadings = $tLeadings->selectFromReserveId($this->reserveid);
			if (!empty($leadings))
			{
				$this->view->assign('leadings', $leadings);

				$reserverid = $this->getRequest()->reserver;

				// 指導履歴がついているのでテンプレートを切り替える
				if (!empty($reserverid))
				{
					$mMembers = new Class_Model_MMembers();
					$reserver = $mMembers->selectFromId($reserverid);
					$this->view->assign('reserver', $reserver);	// メンバ指定あり
				}

				// コメント取得
				$tReserveComment = new Class_Model_TReserveComments();
				$reservecomments = $tReserveComment->selectFromReserveId($this->reserveid);
				if (!empty($reservecomments))
				{
					$this->view->assign('reservecomments', $reservecomments);

					// コメント添付ファイル取得
					$tReserveCommentFile = new Class_Model_TReserveCommentFiles();
					$reservecommentfiles = $tReserveCommentFile->selectFromReserveCommentId($reservecomments->id);
					$this->view->assign('reservecommentfiles', $reservecommentfiles);
				}
			}
		}

		$page = $this->getRequest()->page;
		if (!empty($page))
			$this->view->assign('page', $page);
	}

	// 新規指導(ajax: 戻り値は JSONの配列)
	public function newadviceAction()
	{
		$reserveid			= $this->getRequest()->reserveid;
		// ドタキャンフラグ
		if($this->getRequest()->cancel_flag[0] == "canceled"){
			$cancel_flag	= 1;
		}else{
			$cancel_flag	= 0;
		}
		$counsel			= $this->getRequest()->item1;
		$teaching			= $this->getRequest()->item2;
		$remark				= $this->getRequest()->item3;
		$summary			= $this->getRequest()->item4;
		
		$tmpsendflg	= $this->getRequest()->tmpsendflg;

		if (empty($counsel))
			$counsel = '';

		if (empty($teaching))
			$teaching = '';

		if (empty($remark))
			$remark = '';
		
		$params = array(
				't_reserve_id'			=> $reserveid,
				'submitdate'			=> Zend_Registry::get('nowdate'),
				'm_member_id_charge'	=> $this->member->id,
				'staff_no'				=> $this->member->staff_no,
				'name_jp'				=> $this->member->name_jp,
				'counsel'				=> $counsel,
				'teaching'				=> $teaching,
				'remark'				=> $remark,
				'summary'				=> $summary,
				'cancel_flag'			=> $cancel_flag,
		);
		
		// 20150302 一時保存機能追加
		if(!empty($tmpsendflg))		// 一時保存
		{
			$tLeadingsTmp = new Class_Model_TLeadingsTmp();
			$leadings = $tLeadingsTmp->selectFromReserveId($reserveid);
			
			$tLeadingsTmp->getAdapter()->beginTransaction();
			try
			{
				if (!empty($leadings))
				{
					$leadingid = $tLeadingsTmp->updateFromId($leadings->id, $params);
				}
				else
				{
					$leadingid = $tLeadingsTmp->insert($params);
				}
				$tLeadingsTmp->getAdapter()->commit();
			}
			catch (Exception $e)
			{
				$tLeadingsTmp->getAdapter()->rollback();
				echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
				exit;
			}
		}
		else	// 送信
		{
			$params['submit_flag'] = '1';
			
			$tLeadingsTmp = new Class_Model_TLeadingsTmp();
			$leadings = $tLeadingsTmp->selectFromReserveId($reserveid);
			
			// すでに存在するなら編集、ないなら新規
			$leadingid = 0;
			$tLeadings = new Class_Model_TLeadings();
			$leadings = $tLeadings->selectFromReserveId($reserveid);
			
			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			try
			{
				// 20150302 送信時には一時保存データを削除する
				if (!empty($tLeadingsTmp))
					$tLeadingsTmp->deleteFromReserveId($reserveid);
				
				if (!empty($leadings))
				{
					$leadingid = $tLeadings->updateFromId($leadings->id, $params);
				}
				else
				{
					$leadingid = $tLeadings->insert($params);
				}
				$db->commit();
			}
			catch (Exception $e)
			{
				$db->rollback();
				echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
				exit;
			}
		}

		echo json_encode(array('success' => $reserveid));	// 成功時は予約idを返す
		exit;
	}
	
	
	// 新規指導(ajax: 戻り値は JSONの配列)
	public function newcommentAction()
	{
		$reserveid			= $this->getRequest()->reserveid;
		$leading_comment	= $this->getRequest()->comment;
	
		if (empty($leading_comment))
			$leading_comment = '';
	
		$params = array(
				'leading_comment'		=> $leading_comment,
		);
	
		$tLeadingsTmp = new Class_Model_TLeadingsTmp();
		$leadings = $tLeadingsTmp->selectFromReserveId($reserveid);
			
		// すでに存在するなら編集、ないなら新規
		$leadingid = 0;
		$tLeadings = new Class_Model_TLeadings();
		$leadings = $tLeadings->selectFromReserveId($reserveid);
			
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			if (!empty($leadings))
			{
				$leadingid = $tLeadings->updateFromId($leadings->id, $params);
			}
			else
			{
				$leadingid = $tLeadings->insert($params);
			}
			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		// メール通知とリマインダー
		Sendmail::noticeMail($this, Sendmail::NOTICE_STAFFCOMMENT, $reserveid);
	
		echo json_encode(array('success' => $reserveid));	// 成功時は予約idを返す
		exit;
	}
	
	
	// 予約一覧 履歴も含む(ajax: 戻り値は JSONの配列)
	public function getreservelistAction()
	{
		$page = $this->getRequest()->page;
		if (empty($page))
			$page = 1;

		$tReserves = new Class_Model_TReserves();
		$reserver = $this->getRequest()->reserver;
		$chargeid = $this->getRequest()->chargeid;
		$subjectid = $this->getRequest()->subjectid;
		$reserverid = $this->getRequest()->reserverid;
		$reserveid = $this->getRequest()->reserveid;
		
		
		if(empty($reserveid) && ($reserver === '0') && ($subjectid === '0') && ($chargeid === '0'))
		{	// トップページ
			$select = $tReserves->GetSelectFromChargeId(Zend_Auth::getInstance()->getIdentity()->id, 0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));
		}
		elseif ((!empty($reserver)) && ($reserver !== '0'))
		{	// 予約者IDで取得
			$select = $tReserves->GetSelectFromReserverId($reserver, 0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));
		}
		elseif (!empty($subjectid))
		{	// 授業科目IDで取得
			$select = $tReserves->GetSelectFromSubjectId($subjectid, 0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));
		}
		elseif (!empty($chargeid))
		{	// 担当者IDで取得
			$select = $tReserves->GetSelectFromChargeId($chargeid, 0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));
		}
		elseif (!empty($reserverid))
		{	// 予約IDで取得
			$select = $tReserves->GetSelectFromReserverIdForAdvice($reserverid, 0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));
		}
		else
		{	// 全て取得
			$select = $tReserves->GetAll(0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));
		}

		// ページネーターを取得
		$adapter   = new Zend_Paginator_Adapter_DbSelect($select);
		$paginator = new Zend_Paginator($adapter);

		$paginator->setCurrentPageNumber($page);	// 現在ページ
		$paginator->setItemCountPerPage(10);		// 1ページあたりの表示件数
		$pages = $paginator->getPages();

		// 配列へ変換し、同時に予定と履歴を分ける
		// ※ 既に予約日時でソートされているので、現在時刻を境に分割されるだけ
		$schedule = array();
		$history = array();
		foreach ($paginator as $page)
		{
			if (strtotime($page['reservationdate'] . ' ' . $page['m_timetables_starttime']) < strtotime(Zend_Registry::get('nowdatetime')))
				$history[] = $page;
			else
				$schedule[] = $page;
		}

		// 予定は昇順にソート
		$sort_date = array();
		$sort_time = array();

		foreach($schedule as $key=>$value)
		{
			$sort_date[$key] = $value['reservationdate'];
			$sort_time[$key] = $value['m_timetables_starttime'];
		}

		array_multisort($sort_date, SORT_ASC, $sort_time, SORT_ASC, $schedule);

		$data = array(
				'pages' => array(	// ページ情報
						'first' => $pages->first,
						'firstItemNumber' => $pages->firstItemNumber,
						'firstPageInRange' => $pages->firstPageInRange,
						'current' => $pages->current,
						'currentItemCount' => $pages->currentItemCount,
						'last' => $pages->last,
						'lastItemNumber' => $pages->lastItemNumber,
						'lastPageInRange' => $pages->lastPageInRange,
						'pagesInRange' => $pages->pagesInRange,
						'totalItemCount' => $pages->totalItemCount,
						'pageCount' => $pages->pageCount,
						'previous' => empty($pages->previous) ? 0 : $pages->previous,
						'next' => empty($pages->next) ? 0 : $pages->next,
				),
				// データ本体
				'schedule' => $schedule,
				'history' => $history,

				// 引き回したいパラメータ
				'request' => ''
		);

		echo json_encode($data);
		exit;
	}

	// 履歴一覧(ajax: 戻り値は JSONの配列)
	public function gethistorylistAction()
	{
		$page = $this->getRequest()->page;
		if (empty($page))
			$page = 1;

		$tReserves = new Class_Model_TReserves();
		$reserver = $this->getRequest()->reserver;
		$chargeid = $this->getRequest()->chargeid;
		$subjectid = $this->getRequest()->subject;
		if (!empty($reserver))
		{	// 予約者IDで取得

			// 指導済み全て
			$select = $tReserves->GetSelectFromReserverIdLeading($reserver, 0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));
		}
		elseif (!empty($chargeid))
		{	// 担当者IDで取得
			$select = $tReserves->GetSelectFromChargeId($chargeid, 0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));
		}
		elseif (!empty($subjectid))
		{	// 授業科目IDで取得
			$select = $tReserves->GetSelectFromSubjectId($subjectid, 0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));
		}
		else	// 全て取得
			$select = $tReserves->GetAll(0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));

		// ページネーターを取得
		$adapter   = new Zend_Paginator_Adapter_DbSelect($select);
		$paginator = new Zend_Paginator($adapter);

		$paginator->setCurrentPageNumber($page);	// 現在ページ
		$paginator->setItemCountPerPage(10);			// 1ページあたりの表示件数
		$pages = $paginator->getPages();

		// 配列へ変換し、同時に予定と履歴を分ける
		// ※ 既に予約日時でソートされているので、現在時刻を境に分割されるだけ
		$schedule = array();
		$history = array();
		foreach ($paginator as $page)
		{
			if (strtotime($page['reservationdate'] . ' ' . $page['m_timetables_starttime']) < strtotime(Zend_Registry::get('nowdatetime')))
				$history[] = $page;
			else
				$schedule[] = $page;
		}

		$data = array(
				'pages' => array(	// ページ情報
						'first' => $pages->first,
						'firstItemNumber' => $pages->firstItemNumber,
						'firstPageInRange' => $pages->firstPageInRange,
						'current' => $pages->current,
						'currentItemCount' => $pages->currentItemCount,
						'last' => $pages->last,
						'lastItemNumber' => $pages->lastItemNumber,
						'lastPageInRange' => $pages->lastPageInRange,
						'pagesInRange' => $pages->pagesInRange,
						'totalItemCount' => $pages->totalItemCount,
						'pageCount' => $pages->pageCount,
						'previous' => empty($pages->previous) ? 0 : $pages->previous,
						'next' => empty($pages->next) ? 0 : $pages->next,
				),
				// データ本体
				'schedule' => $schedule,
				'history' => $history,

				// 引き回したいパラメータ
				'request' => ''
		);

		echo json_encode($data);
		exit;
	}
}
