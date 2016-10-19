<?php
require_once('CommonController.php');

class LaboController extends CommonController
{
	const HISTORY_CLASS_INSERT	= 1;
	const HISTORY_CLASS_UPDATE	= 2;
	const HISTORY_CLASS_DELETE	= 3;
	const HISTORY_CLASS_COMMENT	= 4;
	
	public function init()
	{
		parent::init();
	
		$this->_helper->AclCheck('Reserve', 'View');
	}
	
	// トップ
	public function indexAction()
	{
		$this->view->assign('subtitle', 'トップページ');
		////////////////////////////////
		// 新着情報 作成
		// 自身の予約でコメントがついていないものの更新日時
		// コメントがついているものならコメントの更新日時
		// 上記の中から最新3件を表示

		$tReserves = new Class_Model_TReserves();
		$reserves = $tReserves->selectFromStudentNews(Zend_Auth::getInstance()->getIdentity()->id, 5, 0);
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

		// おしらせ3件
		$tInfomations = new Class_Model_TInfomations();
		$infomations = $tInfomations->selectLimitedAll("createdate desc", "5");

		$this->view->assign('reserves', $reserves);			// 予約全部(不要か？)
		$this->view->assign('news', $arrayReserves);		// 新着

		// more部分の新着を区別して取得
		$moreReserves = $tReserves->selectFromStudentNews(Zend_Auth::getInstance()->getIdentity()->id, 20, 5);
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


		////////////////////////////////
		// 個人カレンダー 作成
		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d');

		$w = date('w', strtotime($ymd));

		// 週初め(日曜)取得（月曜にしたかったら $w + 1 にする）
		$weektop = date('Y-m-d', strtotime('-' . $w . ' day', strtotime($ymd)));
		$weekend = date('Y-m-d', strtotime('+6 day', strtotime($weektop)));
		$lastweek = date('Y-m-d', strtotime('-7 day', strtotime($weektop)));
		$nextweek = date('Y-m-d', strtotime('+7 day', strtotime($weektop)));
		$this->view->assign('weektop', $weektop);
		$this->view->assign('weekend', $weekend);
		$this->view->assign('lastweek', $lastweek);
		$this->view->assign('nextweek', $nextweek);

		// 日付リストと祝休日
		$year = date('Y', strtotime($weektop));
		$month = date('m', strtotime($weektop));
		$day = date('d', strtotime($weektop));
		$times['from_time'] = strtotime($year . "-" . $month . "-1 0:0:0");											// 指定月の初日タイムスタンプ
		$times['to_time'] = strtotime($year . "-" . $month . "-" . date("t", $times['from_time']) . " 23:59:59");	// 指定月の最終日タイムスタンプ
		$holidays = HolidayUtil::getHolidayNames($times['from_time'], $times['to_time']);	// 祝日取得
		$datelist = array();
		for ($i = 0; $i < 7; $i++)
		{
			$nowdate	= date('Y-m-d', strtotime('+' . $i . ' day', strtotime($weektop)));
			$nowmonth	= (int)date('m', strtotime($nowdate));
			$nowday		= (int)date('d', strtotime($nowdate));

			$datelist[] = array(
				"ymd" => $nowdate,
				'holiday'	=> (isset($holidays[$nowmonth][$nowday]) ? 1 : 0),	// 祝日か？
				'name'		=> (isset($holidays[$nowmonth][$nowday]) ? $holidays[$nowmonth][$nowday] : ''),			// 祝日なら祝日名
				'submit'	=> $tReserves->countSubmitdate(Zend_Auth::getInstance()->getIdentity()->id, $nowdate),	// 提出日
			);
		}
		$this->view->assign('datelist', $datelist);

		// 指定の週のすべての予定を取得
		$calreserves = $tReserves->selectFromMemberIdAndTermRange(Zend_Auth::getInstance()->getIdentity()->id, $weektop, $weekend);
		$this->view->assign('calreserves', $calreserves);
		
		// イベントの取得
		$tInfomations = new Class_Model_TInfomations();
		$calinfo = $tInfomations->selectFromDate($weektop, $weekend, array("startdate asc", "(enddate - startdate) asc", "createdate asc") );
		$this->view->assign('calinfo', $calinfo);
		
		// アクティブなタブの情報を取得して維持する
		$active = $this->getRequest()->active;
		if (empty($active)){
			$active = 0;
		}
		$this->view->assign('active', $active);
		
		// 最小時間と最大時間の取得
		$mJyugyoTimetables = new Class_Model_MJyugyoTimetables();
		$times = $mJyugyoTimetables->getMinAndMax();
		$this->view->assign('mintime', $times[0]->starttime);
		$this->view->assign('maxtime', $times[1]->endtime);
		
		// 履修科目一覧を取得
		$mSubjects = new Class_Model_MSubjects();
		$subjects = $mSubjects->selectTimetablesFromStudentId($this->member->student_id, $weektop, $weekend);
		
		$this->view->assign('subjects', $subjects);
	}

	// 新規予約
	public function reserveAction()
	{
		$this->_helper->AclCheck('Reserve', 'Edit');
		
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '新規予約');

		// 文書の種類と相談場所と授業科目マスタ
		$mDockinds = new Class_Model_MDockinds();
		$dockinds = $mDockinds->selectAllDisplay();

		$mPlaces = new Class_Model_MPlaces();
		$places = $mPlaces->selectAllDisplay();

		$places_cnt = count($places);
		$this->view->assign('places_cnt', $places_cnt);

		$tCourseList = new Class_Model_TCourseList();
		$subjects = $tCourseList->selectFromStudentId($this->member->student_id);

		$this->view->assign('dockinds', $dockinds);
		$this->view->assign('places', $places);
		$this->view->assign('subjects', $subjects);
		$this->view->assign('progresssal', $this->progresssal_edit);

		// 現在の期を取得(日時指定があればその期を取得)
		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));

		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->getTermFromDate($ymd);
		if (!empty($term))
			$this->view->assign('term', $term);
		
		// 現在の年度を取得
		$mNendo = new Class_Model_MNendo();
		$nendo_row = $mNendo->selectRow();
		$this->view->assign('nendo', $nendo_row->current_nendo + 1);
	}

	// 予約一覧(ajax: 戻り値は JSONの配列)
	public function getreservelistAction()
	{
		$page = $this->getRequest()->page;
		if (empty($page))
			$page = 1;

		$tReserves = new Class_Model_TReserves();
		$select = $tReserves->GetSelectFromMemberIdNoHistory(Zend_Auth::getInstance()->getIdentity()->id, Zend_Registry::get('nowdatetime'), 0, array('reserves.reservationdate ASC', 'shifts.dayno ASC'));

		// ページネーターを取得
		$adapter   = new Zend_Paginator_Adapter_DbSelect($select);
		$paginator = new Zend_Paginator($adapter);

		$paginator->setCurrentPageNumber($page);	// 現在ページ
		$paginator->setItemCountPerPage(10);		// 1ページあたりの表示件数
		$pages = $paginator->getPages();

		// 配列へ変換
		$data = array();
		foreach ($paginator as $page)
		{
			$data[] = $page;
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
			'data' => $data,	// データ本体
			'request' => ''	// 引き回したパラメータ
		);

		echo json_encode($data);
		exit;
	}

	// 新規予約(ajax: 戻り値は JSONの配列)
	public function newreserveAction()
	{
		$isUpload = !empty($_FILES['item7']);

		$reservationdate_array = $this->getRequest()->item3;
		$rdarray = explode(" ", $reservationdate_array);
		$reservationdate = $rdarray[0];
		$m_shift_id = $this->getRequest()->shiftid;
		$jwaricd = $this->getRequest()->item4;
		$submitdate = $this->getRequest()->submitdate;
		$progress = $this->getRequest()->item6;
		$question = $this->getRequest()->item8;

		if (empty($submitdate))
			$submitdate = null;

		if (empty($progress))
			$progress = 0;

		if (empty($question))
			$question = '';
 		
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
			'm_member_id_reserver'	=> $this->member->id,
			'student_id'			=> $this->member->student_id,
			'name_jp'				=> $this->member->name_jp,
			'email'					=> $this->member->email,
			'sex'					=> $this->member->sex,
			'setti_cd'				=> $this->member->setti_cd,
			'syozkcd1'				=> $this->member->syozkcd1,
			'syozkcd2'				=> $this->member->syozkcd2,
			'entrance_year'			=> $this->member->entrance_year,
			'gaknenkn'				=> $this->member->gaknenkn,
			'reservationdate'		=> $reservationdate,
			'nendo'					=> $nendo_row->current_nendo,
			'm_shift_id'			=> $m_shift_id,
			'jwaricd'				=> $jwaricd,
			'class_subject'			=> $class_subject,
			'sekiji_top_kyoinmei'	=> $kyoin,
			'submitdate'			=> $submitdate,
			'progress'				=> $progress,
			'question'				=> $question,
		);
		
		$translate = Zend_Registry::get('Zend_Translate');
		
		$tReserves	= new Class_Model_TReserves();
		$tShiftlimits = new Class_Model_TShiftlimits();
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{	
			// 排他制御
			$lock		= $tShiftlimits->selectShiftForUpdate($m_shift_id, $reservationdate);
			
			// 既に同じ予約が入っていないかチェック(二重送信防止)
			$duplicate = $tReserves->selectDuplicateRow($this->member->id, $m_shift_id, $reservationdate, $nendo_row->current_nendo);
			if (count($duplicate) > 0)
				$result['error'] .= $translate->_("既に同じ内容の予約が入っています。ページを更新してください。") . "\n";
			
			if ($result['error'] != '')
			{
				$db->rollback();
				echo json_encode($result);
				exit;
			}
			
			// 予約受入数に達していないかチェック
			$reserves	= $tReserves->selectShiftForReserve($m_shift_id, $reservationdate);
			$limits		= $tShiftlimits->selectShift($m_shift_id, $reservationdate);
			if (count($reserves) >= (count($limits) > 0 ? $limits[0]->reservelimit : 0))
				$result['error'] .= $translate->_("指定された予約枠に空きが無くなりました。文書の種類/相談場所/日時を再度選択してください。") . "\n";
			
			if ($result['error'] != '')
			{
				$db->rollback();
				echo json_encode($result);
				exit;
			}
			
			$this->reserveid = $tReserves->insert($params);

			// 予約変更履歴テーブル更新

			$params_history = array(
				't_reserve_id' 			=> $this->reserveid,
				'm_member_id_reserver'	=> $this->member->id,
				'student_id'			=> $this->member->student_id,
				'name_jp'				=> $this->member->name_jp,
				'email'					=> $this->member->email,
				'sex'					=> $this->member->sex,
				'setti_cd'				=> $this->member->setti_cd,
				'syozkcd1'				=> $this->member->syozkcd1,
				'syozkcd2'				=> $this->member->syozkcd2,
				'entrance_year'			=> $this->member->entrance_year,
				'gaknenkn'				=> $this->member->gaknenkn,
				'reservationdate'		=> $reservationdate,
				'nendo'					=> $nendo_row->current_nendo,
				'm_shift_id'			=> $m_shift_id,
				'jwaricd'				=> $jwaricd,
				'class_subject'			=> $class_subject,
				'submitdate'			=> $submitdate,
				'progress'				=> $progress,
				'question'				=> $question,
				'historyclass'			=> self::HISTORY_CLASS_INSERT,
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
							'm_member_id'		=> Zend_Auth::getInstance()->getIdentity()->id,
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

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		// メール通知とリマインダー
		Sendmail::noticeMail($this, Sendmail::NOTICE_NEW, $this->reserveid);

		echo json_encode(array('success' => $this->reserveid));	// 成功時はidを返す
		exit;
	}


	// 編集画面
	public function editreserveAction()
	{
		$this->_helper->AclCheck('Reserve', 'Edit');
		
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '予約詳細・変更');

		// 文書の種類と相談場所と授業科目マスタ
		$mDockinds = new Class_Model_MDockinds();
		$dockinds = $mDockinds->selectAllDisplay();

		$mPlaces = new Class_Model_MPlaces();
		$places = $mPlaces->selectAllDisplay();

		$tCourseList = new Class_Model_TCourseList();
		$subjects = $tCourseList->selectFromStudentId($this->member->student_id);

		$this->view->assign('dockinds', $dockinds);
		$this->view->assign('places', $places);
		$this->view->assign('subjects', $subjects);
		$this->view->assign('progresssal', $this->progresssal_edit);

		// 現在の期を取得(日時指定があればその期を取得)
		$set_year = $this->getRequest()->year;
		if (empty($set_year))
			$set_year = date('Y');

		$set_month = $this->getRequest()->month;
		if (empty($set_month))
			$set_month = date('m');

		$set_day = $this->getRequest()->day;
		if (empty($set_day))
			$set_day = date('d');

		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->getTermFromDate(sprintf("%04d-%02d-%02d", $set_year, $set_month, $set_day));
		if (!empty($term))
			$this->view->assign('term', $term);


		$this->reserveid = $this->getRequest()->reserveid;
		if (!empty($this->reserveid))
		{
			$tReserves = new Class_Model_TReserves();
			$reserve = $tReserves->selectFromId($this->reserveid);
			$this->view->assign('reserve', $reserve);

			$mShifts = new Class_Model_MShifts();
			$shift = $mShifts->selectFromId($reserve->m_shift_id);
			$this->view->assign('shift', $shift);

			$dockind	= $mDockinds->selectFromId($shift->m_dockind_id);
			$place		= $mPlaces->selectFromId($shift->m_place_id);
			
			if (empty($reserve->class_subject))
				$reserve->class_subject = '';
			$subject	= $reserve->class_subject;

			$this->view->assign('doctype', $dockind->document_category);
			$this->view->assign('place', $place->consul_place);
			$this->view->assign('subject', $subject);
			$this->view->assign('reserveid', $this->reserveid);

			// 添付ファイル
			$tReserveFile = new Class_Model_TReserveFiles();
			$reservefiles = $tReserveFile->selectFromReserveId($this->reserveid);
			$this->view->assign('reservefiles', $reservefiles);
		}

		$page = $this->getRequest()->page;
		if (!empty($page))
			$this->view->assign('page', $page);
		
		// 現在の年度を取得
		$mNendo = new Class_Model_MNendo();
		$nendo_row = $mNendo->selectRow();
		$this->view->assign('nendo', $nendo_row->current_nendo + 1);
	}

	// 予約編集(ajax: 戻り値は JSONの配列)
	public function updatereserveAction()
	{
		$isUpload = !empty($_FILES['item7']);

		$reserveid = $this->getRequest()->reserveid;
		$reservationdate_array = $this->getRequest()->item3;
		$rdarray = explode(" ", $reservationdate_array);
		$reservationdate = $rdarray[0];
		$m_shift_id = $this->getRequest()->shiftid;
		$jwaricd = $this->getRequest()->item4;
		$submitdate = $this->getRequest()->submitdate;
		$progress = $this->getRequest()->item6;
		$question = $this->getRequest()->item8;

		if (empty($submitdate))
			$submitdate = null;

		if (empty($progress))
			$progress = 0;

		if (empty($question))
			$question = '';

		$tReserves	= new Class_Model_TReserves();
		$tShiftlimits = new Class_Model_TShiftlimits();
		
		$translate = Zend_Registry::get('Zend_Translate');
		
		$reserve = $tReserves->selectFromId($reserveid);	// 現在の予約取得
 		
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

		$params = array(
			'm_member_id_reserver'	=> $this->member->id,
			'student_id'			=> $this->member->student_id,
			'name_jp'				=> $this->member->name_jp,
			'email'					=> $this->member->email,
			'sex'					=> $this->member->sex,
			'setti_cd'				=> $this->member->setti_cd,
			'syozkcd1'				=> $this->member->syozkcd1,
			'syozkcd2'				=> $this->member->syozkcd2,
			'entrance_year'			=> $this->member->entrance_year,
			'gaknenkn'				=> $this->member->gaknenkn,
			'reservationdate'		=> $reservationdate,
			'm_shift_id'			=> $m_shift_id,
			'jwaricd'				=> $jwaricd,
			'class_subject'			=> $class_subject,
			'sekiji_top_kyoinmei'	=> $kyoin,
			'submitdate'			=> $submitdate,
			'progress'				=> $progress,
			'question'				=> $question,
		);

		// 予約変更履歴テーブル更新
		$params_history = array(
			't_reserve_id' 			=> $reserveid,
			'm_member_id_reserver'	=> $this->member->id,
			'student_id'			=> $this->member->student_id,
			'name_jp'				=> $this->member->name_jp,
			'email'					=> $this->member->email,
			'sex'					=> $this->member->sex,
			'setti_cd'				=> $this->member->setti_cd,
			'syozkcd1'				=> $this->member->syozkcd1,
			'syozkcd2'				=> $this->member->syozkcd2,
			'entrance_year'			=> $this->member->entrance_year,
			'gaknenkn'				=> $this->member->gaknenkn,
			'reservationdate'		=> $reservationdate,
			'nendo'					=> $reserve->nendo,
			'm_shift_id'			=> $m_shift_id,
			'jwaricd'				=> $jwaricd,
			'class_subject'			=> $class_subject,
			'submitdate'			=> $submitdate,
			'progress'				=> $progress,
			'question'				=> $question,
			'historyclass'			=> self::HISTORY_CLASS_UPDATE,
		);
		
		$changedShift = $tReserves->getChangedShift($reserveid, $reservationdate, $m_shift_id);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// 排他制御
			$lock		= $tShiftlimits->selectShiftForUpdate($m_shift_id, $reservationdate);
			
			// 予約日かシフトに変更があった場合、予約受入数に達していないかチェック
			if ($reserve->reservationdate != $reservationdate || $reserve->m_shift_id != $m_shift_id)
			{
				$reserves	= $tReserves->selectShift($m_shift_id, $reservationdate);
				$limits		= $tShiftlimits->selectShift($m_shift_id, $reservationdate);
				if (count($reserves) >= (count($limits) > 0 ? $limits[0]->reservelimit : 0))
					$result['error'] .= $translate->_("指定された予約枠に空きが無くなりました。文書の種類/相談場所/日時を再度選択してください。") . "\n";
			}
			
			if ($result['error'] != '')
			{
				$db->rollback();
				echo json_encode($result);
				exit;
			}
			
			$tReserves->updateFromId($reserveid, $params);

			$tReserveHistory = new Class_Model_TReserveHistory();
			// 変更時には同IDで過去の情報を全て削除扱い(お知らせ非表示)とする
			$tReserveHistory->updateFromReserveId($reserveid, array('delete_flag' => 1));
			
			$tReserveHistory->insert($params_history);

			$tFiles = new Class_Model_TFiles();
			$tWorkFile = new Class_Model_TWorkFiles();
			$tReserveFile = new Class_Model_TReserveFiles();

			// 添付ファイル削除
			$keepfiles = $this->getRequest()->keepfile;
			$tReserveFile = new Class_Model_TReserveFiles();
			$reservefiles = $tReserveFile->selectFromReserveId($reserveid);
			foreach ($reservefiles as $reservefile)
			{	// 添付の関連付けだけ削除する
				if (count($keepfiles) <= 0 || !in_array($reservefile['id'], $keepfiles))
					$tReserveFile->deleteFromId($reservefile['id']);
			}

			// ファイルアップロード
			if ($isUpload)
			{
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
						$wfparams = array(
							'm_member_id'		=> Zend_Auth::getInstance()->getIdentity()->id,
							't_file_id'			=> $t_file_id,
						);
						$workfileid = $tWorkFile->insert($wfparams);

						// ワークファイルと予約ファイルを関連付け
						$rfparams = array(
							't_reserve_id'		=> $reserveid,
							't_work_file_id'	=> $workfileid,
						);
						$reservefileid = $tReserveFile->insert($rfparams);
					}
				}
			}
			
			// シフトに影響を及ぼす予約変更があれば、スタッフのシフトを解除する
			if(count($changedShift) > 0)
			{
				$tLeadings = new Class_Model_TLeadings();
				$tLeadings->deleteFromReserveId($reserveid);
				
				$tLeadingsTmp = new Class_Model_TLeadingsTmp();
				$tLeadingsTmp->deleteFromReserveId($reserveid);
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
		Sendmail::noticeUpdateMail($this, Sendmail::NOTICE_UPDATE, $reserveid, $reserve);

		echo json_encode(array('success' => $reserveid));
		exit;
	}

	// 予約閲覧のみ
	public function viewreserveAction()
	{
		$this->editreserveAction();
	}

	// 指定の日の週のカレンダー用シフト情報を取得(ajax: 戻り値は JSONの配列)
	public function getweekshiftAction()
	{
		$date			= $this->getRequest()->ymd;
		$m_dockind_id	= $this->getRequest()->dockind;
		$m_place_id		= $this->getRequest()->place;
		$pre_m_dockind_id	= $this->getRequest()->preDockind;

		if (!empty($date) && !empty($m_dockind_id) && !empty($m_place_id))
		{
			list($year, $month, $day) = sscanf($date, "%04d-%02d-%02d");

			// 週で取得
			$Week = new Calendar_Week( $year, $month, $day, 0 );	// 月曜から
			$Week->build();

			$tReserves = new Class_Model_TReserves();
			$tShiftlimits = new Class_Model_TShiftlimits();
			$tShiftcharges = new Class_Model_TShiftcharges();
			$mShifts = new Class_Model_MShifts();
			$mTerms = new Class_Model_MTerms();
			$tClosuredates = new Class_Model_TClosuredates();

			$weeks = array();
			while ( $Day = $Week->fetch() )
			{
				if ( $Day->isFirst() )
				{	// 最初
				}

				if (!$Day->isEmpty())
				{
					$ymd = sprintf("%04d-%02d-%02d", $Day->thisYear(), $Day->thisMonth(), $Day->thisDay());
					$w = date('w', $Day->getTimestamp());
					
					// 学期をまたがる週の場合を考慮し、日毎に学期とシフトを取得するよう変更
					$term = $mTerms->getTermFromDate($ymd);
					
					if(empty($term))	continue;
					
					$shifts = $mShifts->selectShiftGroup($term->id, $m_dockind_id, $m_place_id);
					
					if ($w >= 0 && $w <= 5)
					{	// 日～金
						
						if ($w != 0)
						{
							$closes = $tClosuredates->selectFromYmdAndPlaceId($ymd, $m_place_id);
							
							$weeks[$w]['closes'] = count($closes);
						}
						
						// この日のシフト情報を取得
						foreach ($shifts as $shift)
						{
							if ($w == 0)
							{	// 日曜日の場所にシフト情報を入れる
								$weeks[$w][$shift->dayno] = array(
									'starttime' => date('H:i', strtotime($shift->m_timetables_starttime)),
									'endtime' => date('H:i', strtotime($shift->m_timetables_endtime)),
								);
							}
							else
							{	// 月～金
								
								// 場所・時間帯・日付からデータを取得する

								// 現在の期の予約、予約受け入れ数、担当者数を取得
								$reserves	= $tReserves->selectShiftKwl($m_place_id, $shift->dayno, $ymd);
								$limits		= $tShiftlimits->selectShiftKwl($m_place_id, $shift->dayno, $ymd);
								$charges	= $tShiftcharges->selectShiftKwl($m_place_id, $shift->dayno, $ymd);

								// 追加情報
								$reserve	= count($reserves); // 予約数
								$reserveid	= $this->getRequest()->reserveid;

								// 予約の中にすでに自分の予約があるか確認
								if(empty($reserveid))
								{
									$myidreserves = $tReserves->selectFromMemberIdAndShiftAllDockind(Zend_Auth::getInstance()->getIdentity()->id, $shift->m_term_id, $shift->dayno, $ymd);	// 全ての自分の予約
									$thisreserves = array();
								}
								else
								{
									$myidreserves = $tReserves->selectFromMemberIdAndShiftAllDockind(Zend_Auth::getInstance()->getIdentity()->id, $shift->m_term_id, $shift->dayno, $ymd, $reserveid);
									$thisreserves = $tReserves->selectRowFromMemberIdAndShiftAllDockind(Zend_Auth::getInstance()->getIdentity()->id, $shift->m_term_id, $m_place_id, $shift->dayno, $ymd, $reserveid);	// 該当コマの自分の予約
								}

								$emptylimit =  (strtotime(Zend_Registry::get('nowdatetime')) > strtotime($ymd . ' ' . $shift->m_timetables_starttime));	// 現在の日付／時刻以前のものはすべて空にする

								// 時間による禁則
								if ($emptylimit)
									$limit = '';
								else
									$limit = (count($limits) > 0 ? $limits[0]->reservelimit : 0);	// 予約受け入れ数

								$charge		= count($charges); 										// 担当者数
								$rest		= (count($limits) > 0 ? ($limit - $reserve <= 0 ? 0 : $limit - $reserve) : 0);		// 受け入れ可能数
								$weeks[$w][$shift->dayno] = array(
									'm_shift_id' => $shift->id,
									'date' => $ymd,
									'reserve' => $reserve,
									'limit' => $limit,
									'charge' => $charge,
									'rest' => $rest,
									'limitname' => (count($limits) > 0 ? $limits[0]->limitname : ''),
									'myidreservecount' => count($myidreserves),
									'thisreservecount' => count($thisreserves),
								);
							}
						}
					}

					if ( $Day->isLast() )
					{	// 最後
					}
				}
			}

			// 文書の種類と相談場所の一覧を追加
			$mDockinds = new Class_Model_MDockinds();
			$dockinds = $mDockinds->selectAllDisplay();

			$mPlaces = new Class_Model_MPlaces();
			$places = $mPlaces->selectAllDisplay();

			// 連想配列内のindexはidと一致させる
			foreach($dockinds as $key => $value)
				$weeks['dockinds'][$value->id] = $value->toArray();
			
			foreach($places as $key => $value)
				$weeks['places'][$value->id] = $value->toArray();

			// 戻すデータ
			echo json_encode($weeks);
		}
		exit;
	}

	// 予約キャンセル(ajax: 戻り値は JSONの配列)
	public function cancelreserveAction()
	{
		$reserveid = $this->getRequest()->reserveid;

		// パラメータチェック
		$result = array('error' => '');

		if (empty($reserveid))
			$result['error'] .= "削除対象を指定してください\n";

		if ($result['error'] != '')
		{
			echo json_encode($result);
 			exit;
 		}

		$Reserves	= new Class_Model_TReserves();
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// 予約添付ファイル削除
			$tReserveFile = new Class_Model_TReserveFiles();
			$tReserveFile->deleteFromReserveId($reserveid);

			// 指導削除
			$tLeadings = new Class_Model_TLeadings();
			$tLeadings->deleteFromReserveId($reserveid);
			
			$tLeadingsTmp = new Class_Model_TLeadingsTmp();
			$tLeadingsTmp->deleteFromReserveId($reserveid);

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

			// メール通知とリマインダー(消した後だと実体を参照できなくなるためここで送信する)
			Sendmail::noticeMail($this, Sendmail::NOTICE_CANCEL, $reserveid);

			// 予約変更履歴テーブル更新
			// キャンセルデータ挿入前に関連する行の削除フラグを更新
			$tReserveHistory = new Class_Model_TReserveHistory();
			$tReserveHistory->updateFromReserveId($reserveid, array('delete_flag' => 1));

			$reserved = $Reserves->selectFromId($reserveid);

			$params_history = array(
				't_reserve_id' 			=> $reserveid,
				'm_member_id_reserver'	=> Zend_Auth::getInstance()->getIdentity()->id,
				'student_id'			=> $reserved->student_id,
				'name_jp'				=> $reserved->name_jp,
				'email'					=> $reserved->email,
				'sex'					=> $this->member->sex,
				'setti_cd'				=> $this->member->setti_cd,
				'syozkcd1'				=> $this->member->syozkcd1,
				'syozkcd2'				=> $this->member->syozkcd2,
				'entrance_year'			=> $this->member->entrance_year,
				'gaknenkn'				=> $this->member->gaknenkn,
				'reservationdate'		=> $reserved->reservationdate,
				'nendo'					=> $reserved->nendo,
				'm_shift_id'			=> $reserved->m_shift_id,
				'jwaricd'				=> $reserved->jwaricd,
				'class_subject'			=> $reserved->class_subject,
				'submitdate'			=> $reserved->submitdate,
				'progress'				=> $reserved->progress,
				'question'				=> $reserved->question,
				'historyclass'			=> self::HISTORY_CLASS_DELETE,
			);

			$tReserveHistory->insert($params_history);

			// 予約削除
			$Reserves->deleteFromId($reserveid);
			
			// リマインダーメールキュー削除
			$tReminders = new Class_Model_TReminders();
			$tReminders->deleteFromReserveId($reserveid);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('success' => "1"));
		exit;
	}

	// 履歴詳細
	public function historyAction()
	{
		$this->_helper->AclCheck('Reserve', 'Edit');
		
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '履歴詳細');

		// 文書の種類と相談場所と授業科目マスタ
		$mDockinds = new Class_Model_MDockinds();
		$dockinds = $mDockinds->selectAllDisplay();

		$mPlaces = new Class_Model_MPlaces();
		$places = $mPlaces->selectAllDisplay();

		$mSubjects = new Class_Model_MSubjects();
		$subjects = $mSubjects->selectFromStudentId($this->member->student_id);

		$this->view->assign('dockinds', $dockinds);
		$this->view->assign('places', $places);
		$this->view->assign('subjects', $subjects);
		$this->view->assign('progresssal', $this->progresssal);

		$this->reserveid = $this->getRequest()->reserveid;

		$tReserves = new Class_Model_TReserves();

		if (empty($this->reserveid))
		{	// 空だった場合、最新の履歴があればそのIDを使う
			$reserves = $tReserves->selectFromMemberIdHistory(Zend_Auth::getInstance()->getIdentity()->id, Zend_Registry::get('nowdatetime'), 1, array('reserves.reservationdate DESC'));
			if (count($reserves) != 0)
				$this->reserveid = $reserves[0]->id;
		}

		if (!empty($this->reserveid))
		{
			$tReserves = new Class_Model_TReserves();
			$reserve = $tReserves->selectFromId($this->reserveid);
			$this->view->assign('reserve', $reserve);
			$mShifts = new Class_Model_MShifts();
			$shift = $mShifts->selectFromId($reserve->m_shift_id);
			$this->view->assign('shift', $shift);

			$dockind	= $mDockinds->selectFromId($shift->m_dockind_id);
			$place		= $mPlaces->selectFromId($shift->m_place_id);
			if(empty($reserve->class_subject))
				$reserve->class_subject = '';
			$subject	= $reserve->class_subject;

			$this->view->assign('doctype', $dockind->document_category);
			$this->view->assign('place', $place->consul_place);
			$this->view->assign('subject', $subject);

			$this->view->assign('reserveid', $this->reserveid);

			// 添付ファイル
			$tReserveFile = new Class_Model_TReserveFiles();
			$reservefiles = $tReserveFile->selectFromReserveId($this->reserveid);
			$this->view->assign('reservefiles', $reservefiles);
			
			// 指導履歴取得
			$tLeadings = new Class_Model_TLeadings();
			$leadings = $tLeadings->selectInputCommentFromReserveId($this->reserveid);
			if (!empty($leadings))
			{
				$this->view->assign('leadings', $leadings);

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

	// 履歴一覧(ajax: 戻り値は JSONの配列)
	public function gethistorylistAction()
	{
		$page = $this->getRequest()->page;
		if (empty($page))
			$page = 1;

		$tReserves = new Class_Model_TReserves();
		$select = $tReserves->GetSelectFromMemberIdHistory(Zend_Auth::getInstance()->getIdentity()->id, Zend_Registry::get('nowdatetime'), 0, array('reserves.reservationdate DESC', 'shifts.dayno DESC'));

		// ページネーターを取得
		$adapter   = new Zend_Paginator_Adapter_DbSelect($select);
		$paginator = new Zend_Paginator($adapter);

		$paginator->setCurrentPageNumber($page);	// 現在ページ
		$paginator->setItemCountPerPage(10);		// 1ページあたりの表示件数
		$pages = $paginator->getPages();

		// 配列へ変換
		$data = array();
		foreach ($paginator as $page)
		{
			$data[] = $page;
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
			'data' => $data,	// データ本体
			'request' => ''	// 引き回したパラメータ
			);

		echo json_encode($data);
		exit;
	}


	// コメント追加・更新(ajax: 戻り値は JSONの配列)
	public function newcommentAction()
	{
		$isUpload = !empty($_FILES['attach']);

		$reserveid 			= $this->getRequest()->reserveid;
		$reservecommentid	= $this->getRequest()->reservecommentid;
		$reservecomment		= $this->getRequest()->comment;

		// パラメータチェック
		$result = array('error' => '');
		if (empty($reservecomment))
			$result['error'] .= "コメントを入力してください\n";

		if ($result['error'] != '')
		{
			echo json_encode($result);
 			exit;
 		}

		$params = array(
			't_reserve_id'		=> $reserveid,
			'reservecomment'	=> $reservecomment,
		);

		$ReserveComments	= new Class_Model_TReserveComments();
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$tFiles = new Class_Model_TFiles();
			$tWorkFile = new Class_Model_TWorkFiles();
			$tReserveCommentFile = new Class_Model_TReserveCommentFiles();

			if ($reservecommentid == 0)
			{	// 新規
				$reservecommentid = $ReserveComments->insert($params);
			}
			else
			{	// 更新
				$ReserveComments->updateFromId($reservecommentid, $params);

				// 添付ファイル削除
				$keepfiles = $this->getRequest()->keepfile;
				$reservecommentfiles = $tReserveCommentFile->selectFromReserveCommentId($reservecommentid);
				foreach ($reservecommentfiles as $reservecommentfile)
				{	// ひとまず添付の関連付けだけ削除する
					if (count($keepfiles) <= 0 || !in_array($reservecommentfile['id'], $keepfiles))
						$tReserveCommentFile->deleteFromId($reservecommentfile['id']);
				}
			}
			
			// 予約変更履歴テーブル更新
			$Reserves = new Class_Model_TReserves();
			$reserved = $Reserves->selectFromId($reserveid);
			
			// 現在の年度を取得
			$mNendo = new Class_Model_MNendo();
			$nendo_row = $mNendo->selectRow();
			
			$params_history = array(
					't_reserve_id' 			=> $reserved->id,
					'm_member_id_reserver'	=> $this->member->id,
					'student_id'			=> $this->member->student_id,
					'name_jp'				=> $this->member->name_jp,
					'email'					=> $this->member->email,
					'sex'					=> $this->member->sex,
					'setti_cd'				=> $this->member->setti_cd,
					'syozkcd1'				=> $this->member->syozkcd1,
					'syozkcd2'				=> $this->member->syozkcd2,
					'entrance_year'			=> $this->member->entrance_year,
					'gaknenkn'				=> $this->member->gaknenkn,
					'reservationdate'		=> $reserved->reservationdate,
					'nendo'					=> $nendo_row->current_nendo,
					'm_shift_id'			=> $reserved->m_shift_id,
					'jwaricd'				=> $reserved->jwaricd,
					'class_subject'			=> $reserved->class_subject,
					'submitdate'			=> $reserved->submitdate,
					'progress'				=> $reserved->progress,
					'question'				=> $reserved->question,
					'historyclass'			=> self::HISTORY_CLASS_COMMENT,
			);
			
			$tReserveHistory = new Class_Model_TReserveHistory();
			$tReserveHistory->insert($params_history);

			// ファイルアップロード
			if ($isUpload)
			{
				$filecount = count($_FILES['attach']['name']);

				for ($index = 0; $index < $filecount; $index++)
				{
					if (!empty($_FILES['attach']['tmp_name'][$index]))
					{
						$t_file_id = $tFiles->insertFile(
							$_FILES['attach']['tmp_name'][$index],
							$_FILES['attach']['name'][$index],
							$_FILES['attach']['type'][$index],
							$_FILES['attach']['size'][$index]);

						// ワークファイルとして登録
						$wfparams = array(
							'm_member_id'		=> Zend_Auth::getInstance()->getIdentity()->id,
							't_file_id'			=> $t_file_id,
						);
						$workfileid = $tWorkFile->insert($wfparams);

						// ワークファイルと予約ファイルを関連付け
						$rfparams = array(
							't_reserve_comment_id'	=> $reservecommentid,
							't_work_file_id'		=> $workfileid,
						);
						$reservecommentfileid = $tReserveCommentFile->insert($rfparams);
					}
				}
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
		Sendmail::noticeMail($this, Sendmail::NOTICE_STUDENTCOMMENT, $reserveid);

		echo json_encode(array('success' => $reserveid));
		exit;
	}
}

