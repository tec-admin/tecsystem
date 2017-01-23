<?php
require_once('CommonController.php');

class AdminController extends CommonController
{
	public $dowmax = 5;

	public function init()
	{
		parent::init();

		$this->_helper->AclCheck('Database', 'View');
	}

	// トップ
	public function indexAction()
	{
		$this->view->assign('subtitle', 'トップページ');
		////////////////////////////////
		// 新着情報 作成
		// ※ 仕様が不明なのでひとまずスタッフと同じにする
		$tReserves = new Class_Model_TReserves();
		$tHistory = new Class_Model_TReserveHistory();
		// 第一引数が0なら全取得
		$reserves = $tHistory->selectFromStaffNews('0', 5, 0);

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

		// 20140722 ishikawa
		// more部分の新着を区別して取得
		$moreReserves = $tHistory->selectFromStaffNews('0', 100, 5);
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
		// 管理者カレンダー 作成
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
			);
		}
		$this->view->assign('datelist', $datelist);

		// 指定の週のすべての予定を取得
		$calreserves = $tReserves->selectFromMemberIdAndTermRange(0, $weektop, $weekend);
		$this->view->assign('calreserves', $calreserves);

		// イベントの取得
		$tInfomations = new Class_Model_TInfomations();
		$calinfo = $tInfomations->selectFromDate($weektop, $weekend, array("startdate asc", "(enddate - startdate) asc", "createdate asc") );
		$this->view->assign('calinfo', $calinfo);

		// 最小時間と最大時間の取得
		$mJyugyoTimetables = new Class_Model_MJyugyoTimetables();
		$times = $mJyugyoTimetables->getMinAndMax();
		$this->view->assign('mintime', $times[0]->starttime);
		$this->view->assign('maxtime', $times[1]->endtime);
	}

	public function editinformationAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '新規お知らせ登録');

		// それ以前、それ以降を選ばせないために、
		// 最小の日付と最大の日付を取得する
		$mTerms = new Class_Model_MTerms();
		$dates = $mTerms->getMinandMaxDate();

		$this->view->assign('mindate', $dates->mindate);
		$this->view->assign('maxdate', $dates->maxdate);
	}

	public function informationAction()
	{
		parent::informationAction();

		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', 'お知らせ詳細');
	}

	// 新規お知らせ(ajax: 戻り値は JSONの配列)
	public function newinfomationAction()
	{
		$m_member_id_from = Zend_Auth::getInstance()->getIdentity()->id;

		$title = $this->getRequest()->title;
		$subtitle = $this->getRequest()->subtitle;
		$startdate = $this->getRequest()->startdate;
		$starthour = $this->getRequest()->starthour;
		$startminute = $this->getRequest()->startminute;
		$enddate = $this->getRequest()->enddate;
		$endhour = $this->getRequest()->endhour;
		$endminute = $this->getRequest()->endminute;
		$body = $this->getRequest()->body;
		$calendar_flag = $this->getRequest()->calendar_flag;
		$allday_flag = $this->getRequest()->allday_flag;

		$translate = Zend_Registry::get('Zend_Translate');

		// パラメータチェック
		$result = array('error' => '');

		if (empty($title))
			$result['error'] .= $translate->_("お知らせタイトルを入力してください") . "\n";

		if (empty($body) || $body === '<br>')		// IE対策
			$result['error'] .= $translate->_("お知らせ本文を入力してください") . "\n";

		if (!empty($calendar_flag))
		{
			if(empty($subtitle))
			{
				$result['error'] .= $translate->_("カレンダータイトルを入力してください") . "\n";
			}

			if (empty($allday_flag))
			{
				if (empty($startdate) || empty($enddate))
				{
					$result['error'] .= $translate->_("日付を選択してください") . "\n";
				}
			}
			else
			{
				if (empty($startdate))
				{
					$result['error'] .= $translate->_("日付を選択してください") . "\n";
				}
			}
		}

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		if (empty($question))
			$question = '';

		if (empty($calendar_flag))
			$calendar_flag = '0';

		if (empty($allday_flag))
			$allday_flag = '0';

		if (empty($starthour))
			$starthour = '00';

		if (empty($startminute))
			$startminute = '00';

		if (!empty($startdate))
			$startstr = $startdate . ' ' . $starthour . ':' . $startminute . ':00';
		else
			$startstr = null;

		if (empty($endhour))
			$endhour = '00';

		if (empty($endminute))
			$endminute = '00';

		if (!empty($enddate))
			$endstr = $enddate . ' ' . $endhour . ':' . $endminute . ':00';
		else
			$endstr = null;

		$params = array(
				'title'				=> $title,
				'body'				=> $body,
				'm_range_id'		=> 0,
				'm_member_id_from'	=> $m_member_id_from,
				'm_member_id_to'	=> 0,
				't_course_id'		=> 0,
				'subtitle'			=> $subtitle,
				'startdate'			=> $startstr,
				'enddate'			=> $endstr,
				'calendar_flag'		=> $calendar_flag,
				'allday_flag'		=> $allday_flag,
		);

		$tInfomations	= new Class_Model_TInfomations();
		$tInfomations->getAdapter()->beginTransaction();
		try
		{
			$this->infomationid = $tInfomations->insert($params);

			$tInfomations->getAdapter()->commit();
		}
		catch (Exception $e)
		{
			$tInfomations->getAdapter()->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			//die($e->getMessage());
			exit;
		}

		echo json_encode(array('success' => $this->infomationid));	// 成功時はidを返す
		exit;
	}


	// お知らせ更新(ajax: 戻り値は JSONの配列)
	public function updateinformationAction()
	{
		$m_member_id_from = Zend_Auth::getInstance()->getIdentity()->id;

		$informationid = $this->getRequest()->informationid;

		$title = $this->getRequest()->title;
		$subtitle = $this->getRequest()->subtitle;
		$startdate = $this->getRequest()->startdate;
		$starthour = $this->getRequest()->starthour;
		$startminute = $this->getRequest()->startminute;
		$enddate = $this->getRequest()->enddate;
		$endhour = $this->getRequest()->endhour;
		$endminute = $this->getRequest()->endminute;
		$body = $this->getRequest()->body;
		$calendar_flag = $this->getRequest()->calendar_flag;;
		$allday_flag = $this->getRequest()->allday_flag;

		$translate = Zend_Registry::get('Zend_Translate');

		// パラメータチェック
		$result = array('error' => '');

		if (empty($title))
			$result['error'] .= $translate->_("お知らせタイトルを入力してください") . "\n";

		if (empty($body))
			$result['error'] .= $translate->_("お知らせ本文を入力してください") . "\n";

		if (!empty($calendar_flag))
		{
			if(empty($subtitle))
				$result['error'] .= $translate->_("カレンダータイトルを入力してください") . "\n";

			if (empty($allday_flag))
			{
				if (empty($startdate) || empty($enddate))
				{
					$result['error'] .= $translate->_("日付を選択してください") . "\n";
				}
			}
			else
			{
				if (empty($startdate))
				{
					$result['error'] .= $translate->_("日付を選択してください") . "\n";
				}
			}
		}

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		if (empty($question))
			$question = '';

		if (empty($calendar_flag))
			$calendar_flag = '0';

		if (empty($allday_flag))
			$allday_flag = '0';

		if (empty($starthour))
			$starthour = '00';

		if (empty($startminute))
			$startminute = '00';

		if (!empty($startdate))
			$startstr = $startdate . ' ' . $starthour . ':' . $startminute . ':00';
		else
			$startstr = null;

		if (empty($endhour))
			$endhour = '00';

		if (empty($endminute))
			$endminute = '00';

		if (!empty($enddate))
			$endstr = $enddate . ' ' . $endhour . ':' . $endminute . ':00';
		else
			$endstr = null;

		$params = array(
				'title'				=> $title,
				'body'				=> $body,
				'm_range_id'		=> 0,
				'm_member_id_from'	=> $m_member_id_from,
				'm_member_id_to'	=> 0,
				't_course_id'		=> 0,
				'subtitle'			=> $subtitle,
				'startdate'			=> $startstr,
				'enddate'			=> $endstr,
				'calendar_flag'		=> $calendar_flag,
				'allday_flag'		=> $allday_flag,
		);

		$tInfomations	= new Class_Model_TInfomations();
		$tInfomations->getAdapter()->beginTransaction();
		try
		{
			$this->informationid = $tInfomations->updateFromId($informationid, $params);

			$tInfomations->getAdapter()->commit();
		}
		catch (Exception $e)
		{
			$tInfomations->getAdapter()->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			//die($e->getMessage());
			exit;
		}

		echo json_encode(array('success' => $informationid));	// 成功時はidを返す
		exit;
	}


	// お知らせ削除(ajax: 戻り値は JSONの配列)
	public function deleteinformationAction()
	{
		$informationid = $this->getRequest()->informationid;

		$translate = Zend_Registry::get('Zend_Translate');

		// パラメータチェック
		$result = array('error' => '');

		if (empty($informationid))
			$result['error'] .= $translate->_("削除対象を指定してください") . "\n";

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		$Infomations = new Class_Model_TInfomations();
		$Infomations->getAdapter()->beginTransaction();
		try
		{
			// メール通知とリマインダー(消した後だと実体を参照できなくなるためここで送信する)
			//Sendmail::noticeMail($this, Sendmail::NOTICE_CANCEL, $informationid);

			// お知らせ削除
			$Infomations->deleteFromId($informationid);

			$Infomations->getAdapter()->commit();
		}
		catch (Exception $e)
		{
			$Infomations->getAdapter()->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			//die($e->getMessage());
			exit;
		}

		echo json_encode(array('success' => "1"));
		exit;
	}

	//本日の予約状況画面
	public function reservestatusAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '予約状況');

		$templates = '';

		// それ以前、それ以降を選ばせないために、
		// 最小の日付と最大の日付を取得する
		$mTerms = new Class_Model_MTerms();
		$dates = $mTerms->getMinandMaxDate();

		$this->view->assign('mindate', $dates->mindate);
		$this->view->assign('maxdate', $dates->maxdate);


		/*
		 * 2014/10/15 ishikawa
		 * 予約ID指定での処理(メールURLからの遷移など)
		 */

		$ymd = $this->getRequest()->ymd;

		if (APPLICATION_TYPE != 'twc')
			$campusid = $this->getRequest()->campusid;
		else
			$campusid = $this->getRequest()->shiftclassid;

		$reserveid = $this->getRequest()->reserveid;
		$this->view->assign('reserveid', $reserveid);

		if(!empty($reserveid) && empty($ymd) && empty($campusid))
		{
			$tReserves = new Class_Model_TReserves();
			$reserved = $tReserves->GetSelectFromReserveId($reserveid);

			$ymd = date('Y-m-d', strtotime($reserved->reservationdate));
			$campusid = $reserved->m_places_m_campus_id;
		}


		//関大
		//キャンパス設定
		if (APPLICATION_TYPE != 'twc')
		{
			$mCampuses = new Class_Model_MCampuses();
			$campuses = $mCampuses->selectAllDisplay();
			$this->view->assign('campuses', $campuses);

			if (empty($campusid))
				$campusid = $campuses[0]->id;

			$campus = $mCampuses->selectFromId($campusid);
			$this->view->assign('campusname', $campus->campus_name);

			$this->view->assign('campusid', $campusid);
		}
		else
		{
			//津田塾、テンプレートを切り替える
			$templates = 'admin/reservestatus.twc.tpl';

			$mShiftclasses = new Class_Model_MShiftclasses();
			$shiftclasses = $mShiftclasses->selectAllDisplay();
			$this->view->assign('shiftclasses', $shiftclasses);

			if (empty($campusid))
				$campusid = $shiftclasses[0]->id;

			$campus = $mShiftclasses->selectFromId($campusid);
			$this->view->assign('campusname', $campus->class_name);

			$this->view->assign('campusid', $campusid);
		}

		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));

		//曜日取得
		$w = date('w', strtotime($ymd));
		if($w==0){
			//土、日の場合強制的に月に設定
			$ymd = date('Y-m-d', strtotime('+1 day', strtotime($ymd)));
		}elseif($w==6){
			$ymd = date('Y-m-d', strtotime('+2 day', strtotime($ymd)));
		}

		$wb='-1';
		$wn='+1';
		//月、金も設定される
		if($w==0){
			//日曜日の場合
			$wb='-3';
			$wn='+2';
		}elseif($w==1){
			//月曜日の場合
			$wb='-3';
		}elseif($w==5){
			//金曜日の場合
			$wn='+3';
		}elseif($w==6){
			//土曜日の場合
			$wb='-3';
			$wn='+3';
		}

		$beforeday = date('Y-m-d', strtotime("$wb day", strtotime($ymd)));
		$nextday = date('Y-m-d', strtotime("$wn day", strtotime($ymd)));
		$this->view->assign('beforeday', $beforeday);
		$this->view->assign('nextday', $nextday);
		$this->view->assign('ymd',$ymd);

		//期間（学期）を設定
		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->getTermFromDate($ymd);

		if (APPLICATION_TYPE != 'twc')
		{
			// シフト取得
			// ※ 同じキャンパス内の最初の場所の最初の文書タイプのみ取得する
			$mDockinds = new Class_Model_MDockinds();
			$dockinds	= $mDockinds->selectAllDisplay();

			//選択したキャンパスを取得
			$mPlaces = new Class_Model_MPlaces();
			$places = $mPlaces->selectFromCampusId($campusid);

			//テーブル時間表示設定
			$mShifts = new Class_Model_MShifts();
			$shifts = $mShifts->selectShiftGroup($term->id, $dockinds[0]->id, $places[0]->id);
			$this->view->assign('shifts', $shifts);

			//キャンパス
			$consul_places = array();
			for ($dow = 0; $dow < count($places); $dow++)
				$consul_places[] = $places[$dow]->consul_place;

			$this->view->assign('consul_places', $consul_places);
			$this->view->assign('places', $places);
		}
		else
		{
			$term = $mTerms->getTermFromDate($ymd);
			$termid = $term->id;

			// シフト取得
			$mShifts = new Class_Model_MShifts();
			$shifts = $mShifts->selectShiftGroupForTwc($termid, $campusid, 1);
			$this->view->assign('shifts', $shifts);

			//キャンパス
			$consul_places = array();
			for ($dow = 0; $dow < count($shiftclasses); $dow++)
				$consul_places[] = $shiftclasses[$dow]->class_name;

			$this->view->assign('consul_places', $consul_places);
			$this->view->assign('places', $shiftclasses);
		}

		$page = $this->getRequest()->page;
		if (!empty($page))
			$this->view->assign('page', $page);

		// テンプレート切り替え
		if ($templates != '')
		{
			$this->_helper->viewRenderer->setNoRender();
			$html = $this->view->render($templates);
			$this->getResponse()->setBody($html);
		}
	}

	//予約一覧入力取得(ajax: 戻り値は JSONの配列)
	public function getreserveinputAction()
	{
		// 期間取得
		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));

		//曜日取得
		$w = date('w', strtotime($ymd));

		//期間（学期）を設定
		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->getTermFromDate($ymd);

		$campusid	= $this->getRequest()->campusid;

		//予約設定取得
		$tReserves = new Class_Model_TReserves();
		$reserves = $tReserves->selectFromCampusIdAndRange($campusid, $ymd, $ymd, 1, 0, array('charge.name_kana asc'));
		$reserves = $reserves->toArray();

		//選択したキャンパスを取得
		$mPlaces = new Class_Model_MPlaces();
		$places = $mPlaces->selectFromCampusId($campusid);

		$cols = $places;

		// シフト取得
		// ※ 同じキャンパス内の最初の場所の最初の文書タイプのみ取得する
		$mDockinds = new Class_Model_MDockinds();
		$dockinds	= $mDockinds->selectAllDisplay();

		//テーブル時間表示設定
		$mShifts = new Class_Model_MShifts();
		$shifts = $mShifts->selectShiftGroup($term->id, $dockinds[0]->id, $places[0]->id);


		$reserver_list = array();
		$reserver_staff_list= array();
		//一覧の表用意
		//シフト時間帯　縦
		for($i = 0; $i < count($shifts); $i++)
		{
			$array = array();
			$array2 = array();
			//キャンバス 横
			foreach($cols as $col)
			{
				$reserver_list[$i][$col->id]=$array;
			}
			$reserver_staff_list[$i]=$array2;
		}

		//予約一覧表に学生を格納
		foreach ($reserves as $reserve)
		{
			$reserver_list[$reserve["m_shifts_dayno"]-1][$reserve["m_shifts_m_place_id"]][] = array("reserver_name_jp" => $reserve["reserver_name_jp"],"id" => $reserve["id"], "charge_name_jp" => $reserve["charge_name_jp"],"charge_id"=>$reserve["charge_id"]);

			//予約のスタッフ一覧(時間帯別)
			if($reserve["charge_id"]!=null)
			{
				$reserver_staff_list[$reserve["m_shifts_dayno"]-1][] = array("m_shifts_dayno" => $reserve["m_shifts_dayno"], "charge_name_jp" => $reserve["charge_name_jp"], "charge_id" => $reserve["charge_id"]);
			}
		}

		$reserver_cnt = array();
		//配列データカウント
		for($i=0;$i<count($reserver_list);$i++)
		{
			$reserver_cnt[$i]=0;
			foreach($cols as $col)
			{
				if(!empty($reserver_list[$i][$col->id]))
				{
					$reserver_cnt[$i]=$reserver_cnt[$i]<count($reserver_list[$i][$col->id])? count($reserver_list[$i][$col->id]) : $reserver_cnt[$i];
				}
				else
				{
					$reserver_cnt[$i]=$reserver_cnt[$i];
				}
			}
		}

		//行そろえる
		//予約データ一覧データが入っていない部分の隙間埋める必要あり
		for($i=0;$i<count($reserver_list); $i++)
		{
			foreach($cols as $col)
			{
				for($k=0;count($reserver_list[$i][$col->id])<$reserver_cnt[$i];$k++)
				{
					if(empty($reserver_list[$i][$col->id][$k]))
						$reserver_list[$i][$col->id][$k] = array("reserver_name_jp" => "","id" => "", "charge_name_jp" => "", "charge_id"=>$reserve["charge_id"]);
				}
			}
		}

		// スタッフシフト取得
		$dow=$w;//曜日
		$tStaffshifts = new Class_Model_TStaffshifts();

		$staffshifts = $tStaffshifts->selectFromTodayAllStaffExceptDaynoIncludingDetails($term->id, $campusid, $dow, $ymd);

		//同一時間帯 同一スタッフの重複を削除
		$staffs = array();
		$staffshifts = $staffshifts->toArray();
		foreach ($staffshifts as $staffshift)
			$staffs[$staffshift['m_shifts_dayno']][$staffshift['m_member_id']] = $staffshift;

		$staffs=array_values($staffs);
		for($i=0;$i<count($staffs);$i++)
		{
			$staffs[$i]=array_values($staffs[$i]);
		}

		//新しいスタッフリスト作成
		//時間帯事にスタッフarrayを作成する
		$new_array=array();
		$newstaffs=array();

		//同じ時間帯でスタッフかぶりあるもの排除
		//時間帯のスタッフシフトでループ
		for($x =0;$x<count($staffs);$x++)
		{
			//スタッフの数だけループ
			for($i =0;$i<count($staffs[$x]);$i++)
			{
				//予約フラグ
				$reserveflg=0;
				//その時間帯に相談予約済みのスタッフ数でループ
				for($k=0; $k < count($reserver_staff_list[$staffs[$x][$i]['m_shifts_dayno']-1]); $k++)
				{
					if($staffs[$x][$i]['m_members_id'] == $reserver_staff_list[$staffs[$x][$i]['m_shifts_dayno']-1][$k]['charge_id'])
					{
						$reserveflg=1;
					}
				}
				// 関大では同時間帯に予約のあるスタッフは完全に表示しない
				if($reserveflg != 1)
				{
					$newstaffs[$x][]=
						array('m_members_id'=>$staffs[$x][$i]['m_members_id'],
								'm_members_name_jp'=>$staffs[$x][$i]['m_members_name_jp'],
								'm_shifts_dayno'=>$staffs[$x][$i]['m_shifts_dayno'],
								'reserveflg' => $reserveflg,
					);
				}
			}
		}

		//一覧テーブルの同じ時間帯のすべての場所に名前に追加する
		//スタッフ追加ロジック
		//シフト　時間帯
		for($i = 0; $i < count($shifts); $i++)
		{
			//キャンバス 横
			foreach($cols as $col)
			{
				if(!empty($newstaffs[$i]))
				{
					for($k = 0; $k < count($newstaffs[$i]); $k++)
					{
						//予約フラグ->同時間帯・同シフト種別で既に担当済みのスタッフ重複削除
						$reserveflg=0;
						for($l = 0; $l < count($reserver_list[$newstaffs[$i][$k]['m_shifts_dayno']-1][$col->id]); $l++)
						{
							if($newstaffs[$i][$k]['m_members_name_jp'] == $reserver_list[$newstaffs[$i][$k]['m_shifts_dayno']-1][$col->id][$l]["charge_name_jp"])
								$reserveflg = 1;
						}

						if($reserveflg != 1)
						{
							$reserver_list[$newstaffs[$i][$k]['m_shifts_dayno']-1][$col->id][] =
								array("reserver_name_jp" => "",
										"id" => "",
										"charge_name_jp" =>  $newstaffs[$i][$k]['m_members_name_jp'],
										"reserveflg" => $newstaffs[$i][$k]['reserveflg'],
							);
						}
					}
				}
			}
		}

		//配列データカウント
		for($i=0;$i<count($reserver_list);$i++)
		{
			$reserver_cnt[$i]=0;
			foreach($cols as $col)
			{
				$reserver_cnt[$i]=$reserver_cnt[$i]<count($reserver_list[$i][$col->id])? count($reserver_list[$i][$col->id]) : $reserver_cnt[$i];
			}
			$reserver_cnt[$i]=$reserver_cnt[$i]<4?4:$reserver_cnt[$i];
		}

		echo json_encode(array('reserverlist' => $reserver_list,'staffshift' => $staffs,'reservercnt'=>$reserver_cnt));
		exit;
	}

	//相談予約　相談内容取得(ajax: 戻り値は JSONの配列)
	public function getreserveAction()
	{
		$reserveid = $this->getRequest()->reserveid;

		//予約設定取得
		$tReserves = new Class_Model_TReserves();
		$reserve = $tReserves->selectFromId($reserveid);


		//予定の空いているスタッフのみ
		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));

		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->getTermFromDate($ymd);

		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAllDisplay();

		$campusid = $this->getRequest()->campusid;

		//予約からdayno取得する
		$dayno = $reserve->m_shifts_dayno;

		//現在の予約取得
		if (APPLICATION_TYPE != 'twc')
		{
			$reservelists = $tReserves->selectFromCampusIdAndRange($campusid, $ymd, $ymd);
		}
		else
		{
			$reservelists = $tReserves->selectFromDateForTwc($ymd);
		}
		$reservelists = $reservelists->toArray();

		$reservestaffs=array();
		foreach ($reservelists as $reservelist){
			//時間帯
			if($reservelist['m_shifts_dayno']==$dayno){
				if(isset($reservelist['charge_id'])){
					$reservestaffs[$reservelist['charge_id']] = array('charge_id'=>$reservelist['charge_id'],'charge_name_jp'=>$reservelist['charge_name_jp']);
				}
			}
		}
		$reservestaffs = array_merge($reservestaffs);

		// スタッフシフト取得
		$tStaffshifts = new Class_Model_TStaffshifts();
		$dow = date('w', strtotime($ymd));

		if (APPLICATION_TYPE != 'twc')
		{
			$staffshifts = $tStaffshifts->selectFromTodayStaffIncludingDetailsYmd($term->id, $campusid, $dayno, $dow, $ymd);
		}
		else
		{
			$staffshifts = $tStaffshifts->selectFromTodayStaffIncludingDetailsForTwc($term->id, $campusid, $dayno, $dow, $ymd);

			// 本日の担当者毎の担当数取得
			$staffcounts = $tStaffshifts->getChargedReserveCountPerStaffAndDate($ymd);

			$count = array();
			foreach ($staffcounts as $staffcount)
				$count[$staffcount['m_member_id_charge']] = $staffcount['count'];
		}

		// 同一スタッフの重複を削除
		$staffs = array();
		$staffshifts = $staffshifts->toArray();
		if (APPLICATION_TYPE != 'twc')
		{
			foreach ($staffshifts as $staffshift)
				$staffs[$staffshift['m_member_id']] = array('m_member_id'=>$staffshift['m_member_id'],'m_members_name_jp'=>$staffshift['m_members_name_jp']);
		}
		else
		{
			foreach ($staffshifts as $staffshift)
				$staffs[$staffshift['m_member_id']] = array('m_member_id'=>$staffshift['m_member_id'],'m_members_name_jp'=>$staffshift['m_members_name_jp'],'m_members_shift_roles'=>$staffshift['m_members_shift_roles']);
		}

		$staffs=array_merge($staffs);

		$newstaffs=array();
// 		for($i=0;$i<count($staffs);$i++){
// 			$staffflg=0;
// 			for($j=0;$j<count($reservestaffs);$j++){
// 				if($staffs[$i]['m_member_id']==$reservestaffs[$j]['charge_id']){
// 					$staffflg=1;
// 				}
// 			}
// 			if($staffflg==0){
// 				$newstaffs[]=$staffs[$i];
// 			}
// 		}
		foreach($staffs as $iKey => $iVal){
			$staffflg=0;
			foreach($reservestaffs as $vKey => $vVal){
				if($staffs[$iKey]['m_member_id']==$reservestaffs[$vKey]['charge_id']){
					$staffflg=1;
				}
			}
			if($staffflg==0){
				$newstaffs[]=$staffs[$iKey];
			}
		}
		//ログ出力設定
		//$bootstrap = $this->getInvokeArg("bootstrap");
		//$log = $bootstrap->getResource("Log");
		//$log->setTimestampFormat("y/m/d-h:i:s");
		//$log->setEventItem("user", "masuda");

		// 添付ファイル
		$tReserveFile = new Class_Model_TReserveFiles();
		$reservefiles = $tReserveFile->selectFromReserveId($reserveid);
		$reservefilescnt = count($reservefiles);
		$reservefile=array();
		for($i =0;$i<$reservefilescnt;$i++){
			$reservefile[]=$reservefiles[$i]->t_files_name;
		}

		$weekday = array( "日", "月", "火", "水", "木", "金", "土" );
		$timeStamp = strtotime($reserve -> submitdate);
		$submitdate = date('Y/m/d', $timeStamp);
		$submitdate.='('.$weekday[date('w', $timeStamp)].')';

		if(!empty($this->progresssal[$reserve->progress]))
			$prog = $this->progresssal[$reserve->progress];
		else
			$prog = '';

		$translate = Zend_Registry::get('Zend_Translate');

		$reserverdetail = array(
				'reserver_name_jp'=>$reserve -> name_jp,
				'reserver_student_id'=>$reserve->student_id,
				'reserver_email'=>$reserve->email,
				//スタッフ
				'charge_name_jp'=>$reserve -> charge_name_jp?:$translate->_("スタッフを選択してください"),
				//文書の種類
				'm_dockinds_document_category'=>$reserve -> m_dockinds_document_category,
				//相談場所
				'm_places_consul_place'=>$reserve -> m_places_consul_place,
				//進行状況
				'progress'=>$prog,
				//提出日
				'submitdate'=>$submitdate,
				//授業科目
				'm_subjects_class_subject'=>$reserve ->class_subject?:'　',
				//担当教員
				'kyoin'=>$reserve->sekiji_top_kyoinmei?:'　',
				//添付ファイル　ファイル名
				'reservefiles'=>$reservefile?:'　',
				//相談したいこと
				'question'=>$reserve -> question?:'　',
				//曜日
				'day'=>$weekday[date('w', strtotime($ymd))],
		);

		if (APPLICATION_TYPE != 'twc')
		{
			echo json_encode(array('reserverdetail' => $reserverdetail,'staffselectlist'=>$newstaffs));
		}
		else
		{
			echo json_encode(array('reserverdetail' => $reserverdetail,'staffselectlist'=>$newstaffs, 'staffcount'=>$count));
		}
		exit;
	}

	// 予約キャンセル(ajax: 戻り値は JSONの配列)
	public function cancelreserveAction()
	{
		$reserveid = $this->getRequest()->reserveid;

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
			//Sendmail::noticeMail($this, Sendmail::NOTICE_CANCEL, $reserveid);

			// 予約変更履歴テーブル更新
			// キャンセルデータ挿入前に関連する行の削除フラグを更新
			$tReserveHistory = new Class_Model_TReserveHistory();
			$tReserveHistory->updateFromReserveId($reserveid, array('delete_flag' => 1));

			$reserved = $Reserves->selectFromId($reserveid);

			$params_history = array(
				't_reserve_id' 			=> $reserveid,
				'm_member_id_reserver'	=> $reserved->m_member_id_reserver,
				'student_id'			=> $reserved->student_id,
				'name_jp'				=> $reserved->name_jp,
				'email'					=> $reserved->email,
				'sex'					=> $reserved->sex,
				'setti_cd'				=> $reserved->setti_cd,
				'syozkcd1'				=> $reserved->syozkcd1,
				'syozkcd2'				=> $reserved->syozkcd2,
				'entrance_year'			=> $reserved->entrance_year,
				'gaknenkn'				=> $reserved->gaknenkn,
				'reservationdate'		=> $reserved->reservationdate,
				'nendo'					=> $reserved->nendo,
				'm_shift_id'			=> $reserved->m_shift_id,
				'jwaricd'				=> $reserved->jwaricd,
				'class_subject'			=> $reserved->class_subject,
				'submitdate'			=> $reserved->submitdate,
				'progress'				=> $reserved->progress,
				'question'				=> $reserved->question,
				'historyclass'			=> 3,
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
			//die($e->getMessage());
			exit;
		}

		echo json_encode(array('success' => "1"));
		exit;
	}

	//スタッフ選択処理(ajax: 戻り値は JSONの配列)
	public function changestaffAction()
	{
		$reserveid = $this->getRequest()->reserveid;
		$chargeid = $this->getRequest()->chargeid;

		if (!empty($reserveid) && !empty($chargeid))
		{
			$tLeadings = new Class_Model_TLeadings();
			$leadings = $tLeadings->selectFromReserveId($reserveid);

			$tLeadings->getAdapter()->beginTransaction();
			try
			{
				if($chargeid=='000')
				{
					// 解除
					$tLeadings->deleteFromReserveId($reserveid);
				}else{
					$mMembers = new Class_Model_MMembers();
					$member = $mMembers->selectFromId($chargeid);

					$params = array(
							't_reserve_id'			=> $reserveid,
							'submitdate'			=> Zend_Registry::get('nowdate'),
							'm_member_id_charge'	=> $chargeid,
							'staff_no'				=> $member->staff_no,
							'name_jp'				=> $member->name_jp,
							'counsel'				=> '',
							'teaching'				=> '',
							'remark'				=> '',
							'summary'				=> '',
							'leading_comment'		=> '',
					);
					if (empty($leadings))
					{
						// 新規登録
						$leadingid = $tLeadings->insert($params);
					}else{
						// 変更
						$leadingid = $tLeadings->updateFromId($leadings->id, $params);
					}
				}
				$tLeadings->getAdapter()->commit();
			}
			catch (Exception $e)
			{
				$tLeadings->getAdapter()->rollback();
				echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
				//die($e->getMessage());
				exit;
			}
		}
		echo json_encode(array('success' => $reserveid));	// 成功時は予約idを返す
		exit;
	}


	// (旧)全指導履歴画面
	public function allhistoryoldAction()
	{
		//ログ出力設定
		//$bootstrap = $this->getInvokeArg("bootstrap");
		//$log = $bootstrap->getResource("Log");
		//$log->setTimestampFormat("y/m/d-h:i:s");
		//$log->setEventItem("user", "masuda");
		//$log->debug(var_export("aaaaaaaa",true));

		$this->view->assign('subtitle', '全指導履歴');

		$templates = '';

		//相談場所
		$mPlaces = new Class_Model_MPlaces();
		$places = $mPlaces->selectAllDisplay();
		$this->view->assign('places', $places);

		$placeid = $this->getRequest()->placeid;
		if(empty($placeid))
			$placeid = $places[0]->id;

		$this->view->assign('placeid', $placeid);

		//日時取得設定
		$ymd = $this->getRequest()->ymd;
		if (empty($ymd))
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));


		//年度プルダウン設定
		$mTerms = new Class_Model_MTerms();
		$terms = $mTerms->selectAll("id");
		//$log->debug(var_export($terms,true));

		$termid = $this->getRequest()->termid;
		if(empty($termid))
			$termid = $terms[0]->id;

		$this->view->assign('termid', $termid);


		//学期プルダウン設定
		$termlists=array();
		foreach ($terms as $term)
		{
			$yearlists[] = array('year'=>$term['year'],);
			$termlists[] = array('name'=>$term['name'],);
		}
		// 20140805 ishikawa エラーが嫌なので一時コメントアウト
		//$yearslist = array_unique($yearlists);
		//$termlists = array_unique($termlists);

		$this->view->assign('terms', $terms);
		$this->view->assign('yearlists', $yearlists);
		$this->view->assign('termlists', $termlists);

		// シフト取得
		// ※ 同じキャンパス内の最初の場所の最初の文書タイプのみ取得する
		$mDockinds = new Class_Model_MDockinds();
		$dockinds	= $mDockinds->selectAllDisplay();
		$this->view->assign('dockinds', $dockinds);

		//シフト作成
		$mShifts = new Class_Model_MShifts();
		$shifts = $mShifts->selectShiftGroup($termid, $dockinds[0]->id, $places[0]->id);
		$this->view->assign('shifts', $shifts);


		$page = $this->getRequest()->page;
		if (!empty($page))
			$this->view->assign('page', $page);

	}

	// (旧)履歴一覧取得(ajax: 戻り値は JSONの配列)
	public function gethistorylistoldAction()
	{
		//ログ出力設定
		//$bootstrap = $this->getInvokeArg("bootstrap");
		//$log = $bootstrap->getResource("Log");
		//$log->setTimestampFormat("y/m/d-h:i:s");
		//$log->setEventItem("user", "masuda");
		//$log->debug(var_export("aaaaaaaa",true));

		$page = $this->getRequest()->page;

		$placeid = $this->getRequest()->placeid;
		$termid = $this->getRequest()->termid;

		$date_from		= $this->getRequest()->date_from;
		$date_to		= $this->getRequest()->date_to;
		$dayno			= $this->getRequest()->dayno;
		$studentname	= $this->getRequest()->studentname;
		$staffname		= $this->getRequest()->staffname;
		$dockindid		= $this->getRequest()->dockindid;
		$subjectname	= $this->getRequest()->subjectname;

		$tReserves = new Class_Model_TReserves();

		//履歴取得
		$select = $tReserves->GetSelectFromInput($placeid, $termid, $date_from, $date_to, $dayno, $studentname, $staffname, $dockindid, $subjectname, 0, array('reserves.reservationdate DESC', 'shifts.dayno ASC'));

		//$select = $tReserves->GetAll(0, array('reserves.reservationdate DESC', 'shifts.dayno ASC'));
		//		$select = $tReserves->GetHistory(0, array('reserves.reservationdate DESC', 'shifts.dayno ASC'));


		//model作成する必要あり、その前に必要なパラメータ洗い出し

		// ページネーターを取得
		$adapter   = new Zend_Paginator_Adapter_DbSelect($select);
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(20);			// 1ページあたりの表示件数
		$paginator->setCurrentPageNumber($page);	// 現在ページ
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

		//$log->debug(var_export($history,true));

		echo json_encode($data);
		exit;
	}




	// 学期前シフト管理画面
	public function shiftmanagementAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '学期前シフト管理');

		$templates = '';

		// メンバー
		$mMembers = new Class_Model_MMembers();
		$members = $mMembers->selectFromRoles('Staff');

		$this->view->assign('members', $members);

		$memberid = $this->getRequest()->memberid;
		$this->view->assign('memberid', $memberid);

		$dowarray = $this->getDowArray();

		$this->view->assign('dowarray', $dowarray);

		if (APPLICATION_TYPE != 'twc')
		{
// 			$mCampuses = new Class_Model_MCampuses();
// 			$campuses = $mCampuses->selectAllDisplay();
// 			$this->view->assign('campuses', $campuses);

// 			$campusid = $this->getRequest()->shiftclass;
// 			if (empty($campusid))
// 				$campusid = $campuses[0]->id;

// 			$this->view->assign('campusid', $campusid);

// 			$campus = $mCampuses->selectFromId($campusid);
// 			$this->view->assign('campusname', $campus->campus_name);

			// 2014/09/18 ishikawa
			// キャンパスではなく場所が選択できるように変更
			$mPlaces = new Class_Model_MPlaces();
			$places = $mPlaces->selectAllDisplay();

			$this->view->assign('campuses', $places);

			$placeid = $this->getRequest()->shiftclass;
			if (empty($placeid))
				$placeid = $places[0]->id;

			$this->view->assign('campusid', $placeid);

			// 2014/09/18 ishikawa
			// 場所に紐づくキャンパスのIDを割り当て
			$campusid = $mPlaces->selectFromId($placeid)->m_campus_id;
			$this->view->assign('lclass', $campusid);

			$place = $mPlaces->selectFromId($placeid);
			$this->view->assign('campusname', $place->consul_place);
		}
		else
		{
			$mShiftclasses = new Class_Model_MShiftclasses();
			$mLShiftclasses = new Class_Model_MLShiftclasses();

			$templates = 'admin/shiftmanagement.twc.tpl';

			$lshiftclasses = $mLShiftclasses->selectAllDisplay();
			$this->view->assign('campuses', $lshiftclasses);

			$campusid = $this->getRequest()->shiftclass;

			if (empty($campusid))
				$campusid = 9;

			// 2014/09/28 ishikawa
			// 右ペインからスタッフが選択された状態では、campusidをスタッフのロールで上書きする
			if(!empty($memberid))
			{
				$memberinfo = $mMembers->selectFromId($memberid);
				$campusid = $memberinfo->shift_roles;
			}

			if ($campusid != 9)
			{
				$sClass = $mLShiftclasses->selectFromShiftclassId($campusid);
				$this->view->assign('campusname', $sClass->l_class_name);
			}

			$this->view->assign('campusid', $campusid);
		}

		// 期間取得
		$mTerms = new Class_Model_MTerms();

		$termid = $this->getRequest()->termid;

		if (empty($termid))
		{
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
			// デフォルトは来期とする
			$term = $mTerms->getNextTermFromDate($ymd);

			$termid = $term->id;
		}
		else
		{
			$term = $mTerms->selectFromId($termid);
		}

		$this->view->assign('termid', $termid);

			// 2016.09.22 $termidが存在しない場合のエラー回避
		if($termid != null)
		{

			$allterm = $mTerms->getThisTermAndNextTermFromDate();
			$this->view->assign('allterm', $allterm);

			$mShifts = new Class_Model_MShifts();
			$countDayno = $mShifts->countDayno($termid);

			$this->view->assign('countDayno', $countDayno);

			$this->view->assign('term', $term);

			$mShifts = new Class_Model_MShifts();

			if (APPLICATION_TYPE != 'twc')
			{
				// シフト取得
				// ※ 同じキャンパス内の最初の場所の最初の文書タイプのみ取得する
				$mDockinds = new Class_Model_MDockinds();
				$dockinds	= $mDockinds->selectAllDisplay();

				//$mPlaces = new Class_Model_MPlaces();
				//$places = $mPlaces->selectFromCampusId($campusid);

				$shifts = $mShifts->selectShiftGroup($termid, $dockinds[0]->id, $placeid);
			}
			else
			{
				$shifts = $mShifts->selectShiftGroupForTwc($termid, $campusid, 1);
			}

			$this->view->assign('shifts', $shifts);


			$this->view->assign('dowmax', $this->dowmax);

		}

		// テンプレート切り替え
		if ($templates != '')
		{
			$this->_helper->viewRenderer->setNoRender();
			$html = $this->view->render($templates);
			$this->getResponse()->setBody($html);
		}
	}

	// シフト入力取得(ajax: 戻り値は JSONの配列)
	public function getshiftinputAction()
	{
		$actionName = $this->getRequest()->actionname;

		$weektop = $this->getRequest()->weektop;
		$weekend = $this->getRequest()->weekend;

		$mTerms = new Class_Model_MTerms();

		$termid = $this->getRequest()->termid;

		$terms = $mTerms->selectFromId($termid);

		$startdate = date('Y-m-d', strtotime($terms->startdate));
		$startdatetime = new DateTime($terms->startdate);
		$startweekday = (int)$startdatetime->format('w');

		$enddate = date('Y-m-d', strtotime($terms->enddate));
		$enddatetime = new DateTime($terms->enddate);
		$endweekday = (int)$enddatetime->format('w');

		$memberid = $this->getRequest()->memberid;

		if (APPLICATION_TYPE != 'twc')
		{
// 			$mCampuses = new Class_Model_MCampuses();
// 			$campuses = $mCampuses->selectAllDisplay();

// 			$campusid = $this->getRequest()->shiftclass;
// 			if (empty($campusid))
// 				$campusid = $campuses[0]->id;

// 			$mPlaces = new Class_Model_MPlaces();
// 			$places = $mPlaces->selectFromCampusId($campusid);

			// 2014/09/18 ishikawa
			// キャンパスではなく場所が選択できるように変更
			$mPlaces = new Class_Model_MPlaces();
			$places = $mPlaces->selectAllDisplay();

			$placeid = $this->getRequest()->shiftclass;
			if (empty($placeid))
				$placeid = $places[0]->id;

			$campusid = $mPlaces->selectFromId($placeid)->m_campus_id;

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

			$tStaffshifts = new Class_Model_TStaffshifts();

			if(!empty($memberid))
			{
				if($actionName == "shiftmanagement")
				{
					$membershifts = $tStaffshifts->selectFromMemberIdAndTermIdAndCampusId($memberid, $termid, $campusid);
				}
				else if($actionName == "workmanagement")
				{
					$membershifts = $tStaffshifts->selectFromMemberIdAndTermIdAndCampusIdIncludingDetails($memberid, $termid, $campusid, $weektop);
				}
			}
		}
		else
		{
			// シフト取得
			$mShiftclasses = new Class_Model_MShiftclasses();
			$shiftclasses = $mShiftclasses->selectAllDisplay();

			$shiftclass = $this->getRequest()->shiftclass;
			if (empty($shiftclass))
				$shiftclass = $shiftclasses[0]->shiftclass;

			$mShifts = new Class_Model_MShifts();
			$shifts = $mShifts->selectShiftGroupForTwc($termid, $shiftclass, 1);

			// シフトの連番の最初と最後を取得
			$startno = $shifts[0]->dayno;
			$endno = $shifts[count($shifts) - 1]->dayno;

			$tStaffshifts = new Class_Model_TStaffshifts();

			if(!empty($memberid))
			{
				if($actionName == "shiftmanagement")
				{
					$membershifts = $tStaffshifts->selectFromMemberIdAndTermIdAndShiftclass($memberid, $termid, $shiftclass);
				}
				else if($actionName == "workmanagement")
				{
					$membershifts = $tStaffshifts->selectFromMemberIdAndTermIdAndShiftclassIncludingDetails($memberid, $termid, $shiftclass, $weektop);
				}
			}
		}


		// 20140808 ishikawa
		// 勤務業務管理では日付/時間帯別シフト取得のため処理を分岐
		if($actionName == "shiftmanagement")
		{
			if (APPLICATION_TYPE != 'twc')
			{
				// 20140730 ishikawa
				// その学期とキャンパスのスタッフを取得
				$staffshifts = $tStaffshifts->selectFromTermIdAndCampusId($termid, $campusid);
			}
			else
			{
				// 20140829 ishikawa
				// その学期とシフト種別のスタッフを取得
				$staffshifts = $tStaffshifts->selectFromTermIdAndShiftclass($termid, $shiftclass);
			}
		}
		else if($actionName == "workmanagement")
		{
			if (APPLICATION_TYPE != 'twc')
			{
				// 日付・時間帯別のシフト情報を含め、その学期とキャンパスのスタッフ情報を取得
				$staffshifts = $tStaffshifts->selectFromTermIdAndCampusIdIncludingDetails($termid, $campusid, $weektop);
			}
			else
			{
				// 日付・時間帯別のシフト情報を含め、その学期とシフト種別のスタッフ情報を取得
				$staffshifts = $tStaffshifts->selectFromTermIdAndShiftclassIncludingDetails($termid, $shiftclass, $weektop);
			}
		}

		$shiftinput = array();

		// 重複をなくし曜日ベースで配列化する
		foreach ($staffshifts as $staffshift)
		{
			$dayno = $staffshift->m_shifts_dayno;
			$dow = $staffshift->dow;
			$memid = $staffshift->m_member_id;

			if(empty($shiftinput[$dow][$dayno]['staffs']))
				$shiftinput[$dow][$dayno]['staffs'] = array();

			if(empty($shiftinput[$dow][$dayno]['count']))
				$shiftinput[$dow][$dayno]['count'] = 0;

			if(empty($shiftinput[$dow][$dayno]['staffs'][$memid]))
			{
				$shiftinput[$dow][$dayno]['staffs'][$memid] = $staffshift->toArray();
				$shiftinput[$dow][$dayno]['count']++;
			}
		}

		$tShiftlimits = new Class_Model_TShiftlimits();

		if($actionName == "shiftmanagement")
		{
			if (APPLICATION_TYPE != 'twc')
			{
				// その学期と場所の受入数を取得
				$stafflimits = $tShiftlimits->selectLimitFromTermIdAndPlaceId($termid, $placeid);
			}
			else
			{
				// その学期とシフト種別の受入数を取得
				$stafflimits = $tShiftlimits->selectLimitFromTermIdAndShiftclass($termid, $shiftclass);
			}
		}
		else if($actionName == "workmanagement")
		{
			if (APPLICATION_TYPE != 'twc')
			{
				// その場所と日付の受入数を取得
				$stafflimits = $tShiftlimits->selectLimitFromPlaceIdAndWeektop($termid, $placeid, $weektop);
			}
			else
			{
				// そのシフト種別と日付の受入数を取得
				$stafflimits = $tShiftlimits->selectLimitFromShiftclassAndWeektop($termid, $shiftclass, $weektop);
			}
		}

		// 曜日・時間帯毎の受入数データを追加する
		foreach ($stafflimits as $stafflimit)
		{
			$dayno = $stafflimit->dayno;
			$dow = $stafflimit->dow;

			if (empty($shiftinput[$dow][$dayno]['limit']))
			{
				$shiftinput[$dow][$dayno]['limit'] = $stafflimit['reservelimit'];
			}
		}

		// 2014/09/23 ishikawa
		// 勤務業務管理では予約数を取得・表示する
		if($actionName == "workmanagement")
		{
			$tReserves = new Class_Model_TReserves();

			if (APPLICATION_TYPE != 'twc')
			{
				// その場所と日付の予約を取得
				$reserves = $tReserves->selectLimitFromPlaceIdAndWeektop($termid, $placeid, $weektop);
			}
			else
			{
				// そのシフト種別と日付の予約を取得
				$reserves = $tReserves->selectLimitFromShiftclassAndWeektop($termid, $shiftclass, $weektop);
			}

			// 曜日・時間帯毎の予約数データを追加する
			foreach ($reserves as $reserve)
			{
				$dayno = $reserve->dayno;
				$dow = $reserve->dow;

				if (empty($shiftinput[$dow][$dayno]['reservecount']))
				{
					$shiftinput[$dow][$dayno]['reservecount'] = 1;
				}
				else
				{
					$shiftinput[$dow][$dayno]['reservecount']++;
				}
			}
		}

		// 一日毎のシフト時間帯の数
		$countDayno = $mShifts->countDayno($termid);

		// 何もデータがない部分を埋める
		for ($dow = 1; $dow <= 5; $dow++)
		{
			for ($dayno = 1; $dayno <= $countDayno; $dayno++)
			{
				if($actionName == "workmanagement")
				{
					// 2014/09/18 ishikawa
					// 選択されている学期の範囲外であるデータは表示しない
					if($weektop < $startdate && $dow < $startweekday)
					{
						$shiftinput[$dow][$dayno]['outofrange'] = 1;
					}
					elseif($weekend > $enddate && $dow > $endweekday)
					{
						$shiftinput[$dow][$dayno]['outofrange'] = 1;
					}
				}

				if (empty($shiftinput[$dow][$dayno]))
				{
					$shiftinput[$dow][$dayno] = array();
				}
			}
		}

		$memberinput = array();

		// 20140804 ishikawa
		// memberidが空でなければ（右ペインからスタッフ個別のページに遷移していれば）シフト入力条件を設定する
		if(!empty($memberid))
		{
			foreach ($membershifts as $membershift)
			{
				$dayno = $membershift->m_shifts_dayno;
				$dow = $membershift->dow;

				if (empty($memberinput[$dow][$dayno]))
				{
					$memberinput[$dow][$dayno] = $membershift->toArray();
				}
			}

			// 各曜日ごとのコマの属性を設定する
			foreach ($memberinput as $dow => $dows)
			{
				if (count($dows) >= $this->dowmax)
				{	// 各曜日の設定最大数に達している
					foreach($dows as $dayno => $staffshift)
					{
						if ($dayno > $startno && $dayno < $endno && !empty($memberinput[$dow][$dayno - 1]) && !empty($memberinput[$dow][$dayno + 1]))
							$memberinput[$dow][$dayno]['class'] = 'attached inter';		// 削除できない
						else
							$memberinput[$dow][$dayno]['class'] = 'attached terminal';	// 通常
					}

					foreach($shifts as $shift)
					{
						if (empty($memberinput[$dow][$shift->dayno]))
							$memberinput[$dow][$shift->dayno]['class'] = 'limit';	// 空いている曜日に最大数オーバーの属性を設定
					}
				}
				else
				{
					foreach($dows as $dayno => $staffshift)
					{
						if ($dayno > $startno && $dayno < $endno && !empty($memberinput[$dow][$dayno - 1]) && !empty($memberinput[$dow][$dayno + 1]))
						{
							$memberinput[$dow][$dayno]['class'] = 'attached inter';		// 削除できない
						}
						else
						{
							$memberinput[$dow][$dayno]['class'] = 'attached terminal';	// 通常
						}

// 						if (APPLICATION_TYPE != 'twc')
// 						{
// 							//masuda 関大シフト入力に制限
// 							if ($dayno > $startno && empty($memberinput[$dow][$dayno - 1]))
// 							{	// 前コマが設定されていない
// 								for ($i = $dayno - 2; $i >= $startno; $i--)
// 									$memberinput[$dow][$i]['class'] = 'restricted';
// 							}

// 							if ($dayno < $endno && empty($memberinput[$dow][$dayno + 1]))
// 							{	// 次コマが設定されていない
// 								for ($i = $dayno + 2; $i <= $endno; $i++)
// 									$memberinput[$dow][$i]['class'] = 'restricted';
// 							}
// 						}
					}
				}
			}

			if($actionName == "workmanagement")
			{
				for ($dow = 1; $dow <= 5; $dow++)
				{
					for ($dayno = 1; $dayno <= $countDayno; $dayno++)
					{
						// 2014/09/18 ishikawa
						// 選択されている学期の範囲外であるデータは表示しない
						if($weektop < $startdate && $dow < $startweekday)
						{
							$memberinput[$dow][$dayno]['class'] = 'outofrange';
						}
						elseif($weekend > $enddate && $dow > $endweekday)
						{
							$memberinput[$dow][$dayno]['class'] = 'outofrange';
						}
					}
				}
			}
		}

		echo json_encode(array('shiftinput' => $shiftinput, 'memberinput' => $memberinput));
		exit;
	}


	// 本日のスタッフ取得(ajax: 戻り値は JSONの配列)
	public function gettodaystaffAction()
	{
		$actionName = $this->getRequest()->actionname;

		$campusid = $this->getRequest()->campusid;	// x実際にはplaceid
		$termid = $this->getRequest()->termid;

		$dow = $this->getRequest()->dow;

		$mShifts = new Class_Model_MShifts();
		$countDayno = $mShifts->countDayno($termid);

		$tStaffshifts = new Class_Model_TStaffshifts();

		$weektop = $this->getRequest()->weektop;

		$staffs_based_time = array();

		// 一日のシフトの数だけ回す
		for($i = 1; $i <= $countDayno; $i++)
		{
			if($actionName == "shiftmanagement")
			{
				// その時間帯のスタッフシフト取得
				$staffshifts = $tStaffshifts->selectFromTodayStaff($termid, $campusid, $i, $dow);
			}
			else if($actionName == "workmanagement")
			{
				// 日付・時間帯別のシフト情報を含め、その時間帯のスタッフシフト取得
				$staffshifts = $tStaffshifts->selectFromTodayStaffIncludingDetails($termid, $campusid, $i, $dow, $weektop);
			}

			// 同一スタッフの重複を削除
			$staffs = array();
			$staffshifts = $staffshifts->toArray();
			foreach ($staffshifts as $staffshift)
				$staffs[$staffshift['m_member_id']] = $staffshift;

			$staffs_based_time[$i] = $staffs;
		}


		if($actionName == "shiftmanagement")
		{
			// その曜日のスタッフシフト取得
			$staffshifts_cnt = $tStaffshifts->selectFromTodayStaffExceptDayno($termid, $campusid, $dow);
		}
		else if($actionName == "workmanagement")
		{
			// その曜日のスタッフシフト取得
			$staffshifts_cnt = $tStaffshifts->selectFromTodayStaffExceptDaynoIncludingDetails($termid, $campusid, $dow, $weektop);
		}

		// 同一スタッフの重複を削除
		$staffs_cnt = array();
		$staffshifts_cnt = $staffshifts_cnt->toArray();
		foreach ($staffshifts_cnt as $staffshift)
			$staffs_cnt[$staffshift['m_member_id']] = $staffshift;

		echo json_encode(array('staffs' => $staffs_based_time, 'count' => count($staffs_cnt)));
		exit;
	}

	// シフト入力設定(ajax: 戻り値は JSONの配列)
	// 20150325 シフトはキャンパス単位、受入数は場所単位での設定を行う
	public function setshiftinputAction()
	{
		$actionName = $this->getRequest()->actionname;

		$id			= $this->getRequest()->shiftclass;
		$placeid	= $this->getRequest()->placeid;

		$dayno		= $this->getRequest()->dayno;
		$dow		= $this->getRequest()->dow;

		$memberid	= $this->getRequest()->memberid;
		$weektop	= $this->getRequest()->weektop;

		if (!empty($id) && !empty($dow) && !empty($dayno) && !empty($memberid))
		{
			// 期間取得
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

			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			try
			{
				$staffshift = $tStaffshifts->selectFromShiftinput($termid, $id, $dayno, $memberid, $dow);

				foreach ($shifts as $shift)
				{
					if($actionName == "shiftmanagement")
					{
						$params = array(
								'm_member_id'	=> $memberid,
								'm_shift_id'	=> $shift->id,
								'dow'			=> $dow,
						);

						$staffshiftid = $tStaffshifts->insert($params);
					}
					else if($actionName == "workmanagement")
					{
						// 1)	t_staffshiftsを見る→データがある
						//			→t_shiftdetailsにはデータがあることが確定(type='0'のデータ)
						//			→t_shiftdetails削除(結果、t_staffshiftsのデータが浮き上がる)
						//
						// 2)	t_staffshiftsを見る→データがない
						//			→t_shiftdetailsにもデータがないことが確定
						//			→t_shiftdetails追加(type='1'のデータ)

						$tShiftdetails = new Class_Model_TShiftdetails();

						if(count($staffshift) > 0)
						{
							// t_shiftdetailsに属するシフトを削除する
							$tShiftdetails->deleteFromDetails($termid, $id, $dayno, $memberid, $weektop, $dow);
						}
						else
						{
							$params = array(
									'm_member_id'	=> $memberid,
									'shiftdate'		=> new Zend_Db_Expr("(date '$weektop' + $dow - 1)"),
									'm_shift_id'	=> $shift->id,
									'dow'			=> $dow,
									'type'			=> '1',
							);

							$tShiftdetails = new Class_Model_TShiftdetails();
							$staffshiftid = $tShiftdetails->insert($params);
						}
					}
				}

				// 20140806 ishikawa
				// シフト入力と同時に受入数を一つ増やす
				$tShiftlimits = new Class_Model_TShiftlimits();

				if($actionName == "shiftmanagement")
				{
					$shiftlimits = $tShiftlimits->selectLimitFromTodayAndPlaceId($termid, $placeid, $dayno, $dow);
				}
				else if($actionName == "workmanagement")
				{
					$shiftlimits = $tShiftlimits->selectLimitFromWeektopAndDow($termid, $placeid, $dayno, $weektop, $dow);
				}

				if (count($shiftlimits) > 0)
				{
					$params = array(
							'reservelimit' => new Zend_Db_Expr('(reservelimit + 1)'),
					);

					if($actionName == "shiftmanagement")
					{
						$shiftlimitid = $tShiftlimits->updateFromTermIdAndPlaceIdAndDaynoAndDow($termid, $placeid, $dayno, $dow, $params);
					}
					else if($actionName == "workmanagement")
					{
						$shiftlimitid = $tShiftlimits->updateFromTermIdAndPlaceIdAndDaynoAndWeektopAndDow($termid, $placeid, $dayno, $weektop, $dow, $params);
					}
				}
				else
				{
					// 20140828 ishikawa
					// 受入数データが存在しない場合

					$params = array(
							'reservelimit' => 1,
					);

					if($actionName == "shiftmanagement")
					{
						$shiftlimitid = $tShiftlimits->insertFromTermIdAndPlaceIdAndDaynoAndDow($termid, $placeid, $dayno, $dow, $params);
					}
					else if($actionName == "workmanagement")
					{
						$shiftlimitid = $tShiftlimits->insertFromTermIdAndPlaceIdAndDaynoAndWeektopAndDow($termid, $placeid, $dayno, $weektop, $dow, $params);
					}
				}

				$db->commit();
			}
			catch (Exception $e)
			{
				//$this->logWrite(print_r($e, true), Zend_Log::DEBUG);
				$db->rollback();
				echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
				//die($e->getMessage());
				exit;
			}

			echo json_encode('success');
		}
		else
			echo json_encode('failed');

		exit;
	}

	// シフト入力削除(ajax: 戻り値は JSONの配列)
	// 20150325 シフトはキャンパス単位、受入数は場所単位での設定を行う
	public function deleteshiftinputAction()
	{
		$actionName = $this->getRequest()->actionname;

		$id			= $this->getRequest()->shiftclass;
		$placeid	= $this->getRequest()->placeid;

		$dayno		= $this->getRequest()->dayno;
		$dow		= $this->getRequest()->dow;

		$memberid	= $this->getRequest()->memberid;
		$weektop	= $this->getRequest()->weektop;

		if (!empty($id) && !empty($dow) && !empty($dayno) && !empty($memberid))
		{
			// 期間取得
			$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));

			$mTerms = new Class_Model_MTerms();
			$term = $mTerms->getTermFromDate($ymd);

			$termid = $this->getRequest()->termid;
			if (empty($termid))
				$termid = $term->id;

			// 指定されたキャンパス、連番、曜日に対応するスタッフシフトをすべて削除
			$tStaffshifts	= new Class_Model_TStaffshifts();

			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			try
			{
				if($actionName == "shiftmanagement")
				{
					$term = $mTerms->selectFromId($termid);
					//$term_next = $mTerms->getNextTermFromDate($term->startdate);

					$tStaffshifts->deleteFromShiftinput($termid, $id, $dayno, $memberid, $dow);
				}
				else if($actionName == "workmanagement")
				{
					$staffshifts = $tStaffshifts->selectFromShiftinput($termid, $id, $dayno, $memberid, $dow);

					$tShiftdetails = new Class_Model_TShiftdetails();

					// 削除する場合、どちらか一方にのみデータが存在する

					// 1)	t_staffshiftsを見る→データがある
					// 			→t_shiftdetailsにはデータがない→t_shiftdetails追加(type='0')
					// 2)	t_staffshiftsを見る→データがない
					//			→t_shiftdetailsにはデータがある(type='1')→t_shiftdetails削除

					if(count($staffshifts) > 0)
					{
						/*
						 $shiftdetails = $tShiftdetails->selectFromDetails($termid, $id, $dayno, $memberid, $weektop, $dow);

						if(count($shiftdetails) > 0)
						{
						// t_shiftdetailsに属するシフトを削除する
						$tShiftdetails->deleteFromDetails($termid, $id, $dayno, $memberid, $weektop, $dow);
						}
						else
						{
						*/
						// t_shiftdetailsにtype='0'の行を追加する
						//$mShifts = new Class_Model_MShifts();
						//$shifts = $mShifts->selectFromTermIdAndCampusIdAndDayno($termid, $id, $dayno);

						foreach ($staffshifts as $staffshift)
						{
							$params = array(
									'm_member_id'	=> $memberid,
									'shiftdate'		=> new Zend_Db_Expr("(date '$weektop' + $dow - 1)"),
									'm_shift_id'	=> $staffshift->m_shift_id,
									'dow'			=> $dow,
									'type'			=> 0,
							);

							$staffshiftid = $tShiftdetails->insert($params);
						}
					}
					else
					{
						// t_shiftdetailsに属するシフトを削除する
						$tShiftdetails->deleteFromDetails($termid, $id, $dayno, $memberid, $weektop, $dow);
					}
				}

				// 20140806 ishikawa
				// シフト削除と同時に受入数を一つ減らす
				$tShiftlimits = new Class_Model_TShiftlimits();

				if($actionName == "shiftmanagement")
				{
					$shiftlimits = $tShiftlimits->selectLimitFromTodayAndPlaceId($termid, $placeid, $dayno, $dow);
				}
				else if($actionName == "workmanagement")
				{
					$shiftlimits = $tShiftlimits->selectLimitFromWeektopAndDow($termid, $placeid, $dayno, $weektop, $dow);
				}

				if (count($shiftlimits) > 0)
				{
					$params = array(
							'reservelimit' => new Zend_Db_Expr('(CASE WHEN reservelimit > 0 THEN (reservelimit - 1) ELSE 0 END)'),
					);

					if($actionName == "shiftmanagement")
					{
						$shiftlimitid = $tShiftlimits->updateFromTermIdAndPlaceIdAndDaynoAndDow($termid, $placeid, $dayno, $dow, $params);
					}
					else if($actionName == "workmanagement")
					{
						$shiftlimitid = $tShiftlimits->updateFromTermIdAndPlaceIdAndDaynoAndWeektopAndDow($termid, $placeid, $dayno, $weektop, $dow, $params);
					}
				}

				$db->commit();
			}
			catch (Exception $e)
			{
				$db->rollback();
				echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
				//die($e->getMessage());
				exit;
			}

			echo json_encode('success');
		}
		else
			echo json_encode('failed');

		exit;
	}

	// 受入数増減設定(ajax: 戻り値は JSONの配列)
	public function setlimitAction()
	{
		$actionName = $this->getRequest()->actionname;

		$termid		= $this->getRequest()->termid;
		// 2014/09/18 ishikawa
		// 関大仕様変更：キャンパスではなく場所毎に設定をする
		$campusid	= $this->getRequest()->campusid;

		$dow = $this->getRequest()->dow;
		$reservationdate = $this->getRequest()->reservationdate;

		if(empty($dow))
		{
			$datetime = new DateTime($reservationdate);
			$dow = (int)$datetime->format('w');
		}

		$mShifts = new Class_Model_MShifts();
		$countDayno = $mShifts->countDayno($termid);

		$mTerms = new Class_Model_MTerms();
		$terms = $mTerms->selectFromId($termid);

		for ($i = 1; $i <= $countDayno; $i++)
		{
			${'dayno'.$i}		= $this->getRequest()->{'limit'.$dow.'_'.$i};
		}

		$tShiftlimits = new Class_Model_TShiftlimits();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();

		try
		{
			for ($i = 1; $i <= $countDayno; $i++)
			{
				// 2014/08/28 ishikawa
				// すでに受入数データが存在するなら更新、ないなら新規
// 				if($actionName == "shiftmanagement")
// 				{
// 					$shiftlimits = $tShiftlimits->selectLimitFromTodayAndPlaceId($termid, $campusid, $i, $dow);
// 				}
// 				else if($actionName == "workmanagement")
// 				{
// 					$shiftlimits = $tShiftlimits->selectLimitFromReservationdate($termid, $campusid, $i, $reservationdate);
// 				}

				$params = array(
						'reservelimit' => ${'dayno'.$i},
				);

				if($actionName == "shiftmanagement")
				{
					$tShiftlimits->deleteFromTermIdAndPlaceIdAndDaynoAndDow($termid, $campusid, $i, $dow);
					$shiftlimitid = $tShiftlimits->insertFromTermIdAndPlaceIdAndDaynoAndDow($termid, $campusid, $i, $dow, $params);
				}
				else if($actionName == "workmanagement")
				{
					$tShiftlimits->deleteFromTermIdAndPlaceIdAndDaynoAndReservationdate($termid, $campusid, $i, $reservationdate);
					$shiftlimitid = $tShiftlimits->insertFromTermIdAndPlaceIdAndDaynoAndReservationdate($termid, $campusid, $i, $reservationdate, $params);
				}

// 				if (count($shiftlimits) > 0)
// 				{
// 					if($actionName == "shiftmanagement")
// 					{
// 						$shiftlimitid = $tShiftlimits->updateFromTermIdAndPlaceIdAndDaynoAndDow($termid, $campusid, $i, $dow, $params);
// 					}
// 					else if($actionName == "workmanagement")
// 					{
// 						$shiftlimitid = $tShiftlimits->updateFromTermIdAndPlaceIdAndDaynoAndReservationdate($termid, $campusid, $i, $reservationdate, $params);
// 					}
// 				}
// 				else
// 				{
// 					if($actionName == "shiftmanagement")
// 					{
// 						$shiftlimitid = $tShiftlimits->insertFromTermIdAndPlaceIdAndDaynoAndDow($termid, $campusid, $i, $dow, $params);
// 					}
// 					else if($actionName == "workmanagement")
// 					{
// 						$shiftlimitid = $tShiftlimits->insertFromTermIdAndPlaceIdAndDaynoAndReservationdate($termid, $campusid, $i, $reservationdate, $params);
// 					}
// 				}
			}

			$db->commit();

		}
		catch (Exception $e)
		{
			//$this->logWrite(print_r($e, true), Zend_Log::DEBUG);
			$tShiftlimits->getAdapter()->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			//die($e->getMessage());
			exit;
		}

		echo json_encode('success');
		exit;
	}



	// 受入数増減設定(ajax: 戻り値は JSONの配列)
	public function setlimittwcAction()
	{
		$actionName = $this->getRequest()->actionname;

		$termid		= $this->getRequest()->termid;
		$campusid	= $this->getRequest()->campusid;

		$mShiftclasses = new Class_Model_MShiftclasses();

		if($campusid == 9)
			$shiftclasses = $mShiftclasses->selectAll();
		else
			$shiftclasses = $mShiftclasses->selectFromMultiId($campusid);

		$dow = $this->getRequest()->dow;
		$reservationdate = $this->getRequest()->reservationdate;

		if(empty($dow))
		{
			$datetime = new DateTime($reservationdate);
			$dow = (int)$datetime->format('w');
		}

		$mShifts = new Class_Model_MShifts();
		$countDayno = $mShifts->countDayno($termid);

		$mTerms = new Class_Model_MTerms();
		$terms = $mTerms->selectFromId($termid);

		for ($i = 1; $i <= $countDayno; $i++)
		{
			foreach ($shiftclasses as $shiftclass)
			{
				${'dayno'.$i.'_'.$shiftclass->id}		= $this->getRequest()->{'limit'.$dow.'_'.$i.'_'.$shiftclass->id};
			}
		}

		$tShiftlimits = new Class_Model_TShiftlimits();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();

		try
		{
			$tMaxlimits = new Class_Model_tMaxlimits();

			foreach($shiftclasses as $shiftclass)
			{
				if(empty($this->getRequest()->{'maxlimit'.$dow.'_'.$shiftclass->id}))
					continue;

				$params	= array(
						'maxlimit'		=>	$this->getRequest()->{'maxlimit'.$dow.'_'.$shiftclass->id}
				);

				if($actionName == "shiftmanagement")
				{
					$maxlimits = $tMaxlimits->selectMaxlimitFromShiftclassAndTermIdAndDow($shiftclass->id, $termid, $dow);
				}
				else if($actionName == "workmanagement")
				{
					$maxlimits = $tMaxlimits->selectMaxlimitFromShiftclassAndDate($shiftclass->id, $reservationdate);
				}

				if (count($maxlimits) > 0)
				{
					if($actionName == "shiftmanagement")
					{
						$maxlimitid = $tMaxlimits->updateFromShiftclassAndTermIdAndDow($shiftclass->id, $termid, $dow, $params);
					}
					else if($actionName == "workmanagement")
					{
						$maxlimitid = $tMaxlimits->updateFromShiftclassAndDate($shiftclass->id, $reservationdate, $params);
					}
				}
				else
				{
					if($actionName == "shiftmanagement")
					{
						$maxlimitid = $tMaxlimits->insertFromShiftclassAndTermIdAndDow($shiftclass->id, $termid, $dow, $params);
					}
					else if($actionName == "workmanagement")
					{
						$params = array(
								'reservationdate'	=>	$reservationdate,
								'm_shiftclass_id'	=>	$shiftclass->id,
								'maxlimit'			=>	$this->getRequest()->{'maxlimit'.$dow.'_'.$shiftclass->id}
						);
						$maxlimitid = $tMaxlimits->insert($params);
					}
				}
			}

			for ($i = 1; $i <= $countDayno; $i++)
			{
				foreach ($shiftclasses as $shiftclass)
				{
					// 2014/08/28 ishikawa
					// すでに受入数データが存在するなら更新、ないなら新規
					if($actionName == "shiftmanagement")
					{
						$shiftlimits = $tShiftlimits->selectLimitFromTodayForTwc($termid, $shiftclass->id, $i, $dow);
					}
					else if($actionName == "workmanagement")
					{
						$shiftlimits = $tShiftlimits->selectLimitFromReservationdateForTwc($termid, $shiftclass->id, $i, $reservationdate);
					}

					// アカデミックの受入数は、リクエストではアカデミックの受入数＋就職の受入数
					if($shiftclass->id == 1)
					{
						$params = array(
								'reservelimit' => ${'dayno'.$i.'_1'} + ${'dayno'.$i.'_2'},
						);
					}
					else
					{
						$params = array(
								'reservelimit' => ${'dayno'.$i.'_'.$shiftclass->id},
						);
					}

					if (count($shiftlimits) > 0)
					{
						if($actionName == "shiftmanagement")
						{
							$shiftlimitid = $tShiftlimits->updateFromTermIdAndShiftclassAndDaynoAndDow($termid, $shiftclass->id, $i, $dow, $params);
						}
						else if($actionName == "workmanagement")
						{
							$shiftlimitid = $tShiftlimits->updateFromTermIdAndShiftclassAndDaynoAndReservationdate($termid, $shiftclass->id, $i, $reservationdate, $params);
						}
					}
					else
					{
						if($actionName == "shiftmanagement")
						{
							$shiftlimitid = $tShiftlimits->insertFromTermIdAndShiftclassAndDaynoAndDow($termid, $shiftclass->id, $i, $dow, $params);
						}
						else if($actionName == "workmanagement")
						{
							$shiftlimitid = $tShiftlimits->insertFromTermIdAndShiftclassAndDaynoAndReservationdate($termid, $shiftclass->id, $i, $reservationdate, $params);
						}
					}
				}
			}
			$db->commit();
		}
		catch (Exception $e)
		{
			//$this->logWrite(print_r($e, true), Zend_Log::DEBUG);
			$tShiftlimits->getAdapter()->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			//die($e->getMessage());
			exit;
		}

		echo json_encode('success');
		exit;
	}

	// 勤務業務管理画面
	public function workmanagementAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '勤務/業務管理');

		$templates = '';

		// メンバー
		$mMembers = new Class_Model_MMembers();
		$members = $mMembers->selectFromRoles('Staff');

		$this->view->assign('members', $members);

		$memberid = $this->getRequest()->memberid;
		$this->view->assign('memberid', $memberid);

		$dowarray = $this->getDowArray();

		$this->view->assign('dowarray', $dowarray);

		if (APPLICATION_TYPE != 'twc')
		{
// 			$mCampuses = new Class_Model_MCampuses();
// 			$campuses = $mCampuses->selectAllDisplay();
// 			$this->view->assign('campuses', $campuses);

// 			$campusid = $this->getRequest()->shiftclass;
// 			if (empty($campusid))
// 				$campusid = $campuses[0]->id;

// 			$this->view->assign('campusid', $campusid);

// 			$campus = $mCampuses->selectFromId($campusid);
// 			$this->view->assign('campusname', $campus->campus_name);

			// 2014/09/18 ishikawa
			// キャンパスではなく場所が選択できるように変更
			$mPlaces = new Class_Model_MPlaces();
			$places = $mPlaces->selectAllDisplay();

			$this->view->assign('campuses', $places);

			$placeid = $this->getRequest()->shiftclass;
			if (empty($placeid))
				$placeid = $places[0]->id;

			$this->view->assign('campusid', $placeid);

			// 2014/09/18 ishikawa
			// 場所に紐づくキャンパスのIDを割り当て
			$campusid = $mPlaces->selectFromId($placeid)->m_campus_id;
			$this->view->assign('lclass', $campusid);

			$place = $mPlaces->selectFromId($placeid);
			$this->view->assign('campusname', $place->consul_place);
		}
		else
		{
			$templates = 'admin/workmanagement.twc.tpl';

			$mShiftclasses = new Class_Model_MShiftclasses();

			$mLShiftclasses = new Class_Model_MLShiftclasses();
			$lshiftclasses = $mLShiftclasses->selectAllDisplay();
			$this->view->assign('campuses', $lshiftclasses);

			$campusid = $this->getRequest()->shiftclass;

			if (empty($campusid))
				$campusid = 9;

			// 2014/09/28 ishikawa
			// 右ペインからスタッフが選択された状態では、campusidをスタッフのロールで上書きする
			if(!empty($memberid))
			{
				$memberinfo = $mMembers->selectFromId($memberid);
				$campusid = $memberinfo->shift_roles;
			}

			if ($campusid != 9)
			{
				$sClass = $mLShiftclasses->selectFromShiftclassId($campusid);
				$this->view->assign('campusname', $sClass->l_class_name);
			}

			$this->view->assign('campusid', $campusid);
		}

		// 期間取得
		$mTerms = new Class_Model_MTerms();

		$ymd = $this->getRequest()->ymd;
		$termid = $this->getRequest()->termid;

		$startdate = $ymd;
		$enddate = date("Y-m-d", strtotime($ymd . " +4 day"));

		$originid = $this->getRequest()->originid;

		if(!empty($originid))
		{
			$origin = $mTerms->selectFromId($originid);

			$origin_start = date('Y-m-d', strtotime($origin->startdate));
			$origin_end = date('Y-m-d', strtotime($origin->enddate));

			// 2014/09/18 ishikawa
			// ※週が学期をまたぐ場合の処理
			// 遷移元のoriginidが表示する日付の範囲内であればtermidと置き換える
			if(($origin_start >= $startdate && $origin_start <= $enddate)
			|| ($origin_end >= $startdate && $origin_end <= $enddate))
			{
				$termid = $originid;
			}
		}

		// トップページ
		if (empty($ymd))
		{
			if (empty($termid))
			{
				$w = date('w', strtotime(Zend_Registry::get('nowdate')));

				if($w > 5)	// 2014/09/29 ishikawa 土日なら次の週表示
					$ymd = date('Y-m-d', strtotime('7day'));
				else
					$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));

				// デフォルトは今期とする
				$term = $mTerms->getTermFromDate($ymd);
			}
			else
			{
				$term = $mTerms->selectFromId($termid);
				$ymd = $term->startdate;
			}
		}

		// トップページ以外
		if (empty($termid))
		{
			$term = $mTerms->getTermFromDate($ymd);
			$termid = $term->id;
		}
		else
		{
			$term = $mTerms->selectFromId($termid);

			$term_start = date('Y-m-d', strtotime($term->startdate));
			$term_end = date('Y-m-d', strtotime($term->enddate));

			// 2014/09/25 ishikawa
			// ※週が学期をまたぐ場合の処理
			// 遷移元のtermidが表示する日付の範囲外であればymdで取得する
			// （週が学期にまたがらない場合の処理で、週が学期にまたがる場合は分岐しない）
			if(($term_start < $startdate || $term_start > $enddate)
			&& ($term_end < $startdate || $term_end > $enddate))
			{
				$term = $mTerms->getTermFromDate($ymd);
				$termid = $term->id;
			}
		}

		$this->view->assign('ymd', $ymd);

		$this->view->assign('termid', $termid);
		$this->view->assign('term', $term);


		$w = date('w', strtotime($ymd));

		// 週初め取得
		$weektop = date('Y-m-d', strtotime('-' . ($w - 1) . ' day', strtotime($ymd)));	// 月曜
		$weekend = date('Y-m-d', strtotime('+4 day', strtotime($weektop)));				// 金曜
		$lastweek = date('Y-m-d', strtotime('-7 day', strtotime($weektop)));
		$nextweek = date('Y-m-d', strtotime('+7 day', strtotime($weektop)));
		$weeks = array();
		for ($dow = 0; $dow < 5; $dow++)
		{
			$weeks[] = date('Y-m-d', strtotime('+' . $dow . ' day', strtotime($weektop)));
		}

		// 2014/09/24 ishikawa
		// 学期境目週の処理用に追加
		$term_weektop = $mTerms->getTermFromDate($weektop);
		$term_weekend = $mTerms->getTermFromDate($weekend);
		$this->view->assign('term_weektop', $term_weektop);
		$this->view->assign('term_weekend', $term_weekend);

		$this->view->assign('weektop', $weektop);
		$this->view->assign('weekend', $weekend);
		$this->view->assign('lastweek', $lastweek);
		$this->view->assign('nextweek', $nextweek);
		$this->view->assign('weeks', $weeks);


		$allterm = $mTerms->getThisTermAndNextTermFromDate();
		$this->view->assign('allterm', $allterm);

		$mShifts = new Class_Model_MShifts();
		$countDayno = $mShifts->countDayno($termid);

		$this->view->assign('countDayno', $countDayno);

		$mShifts = new Class_Model_MShifts();

		if (APPLICATION_TYPE != 'twc')
		{
			// シフト取得
			// ※ 同じキャンパス内の最初の場所の最初の文書タイプのみ取得する
			$mDockinds = new Class_Model_MDockinds();
			$dockinds	= $mDockinds->selectAllDisplay();

			$mPlaces = new Class_Model_MPlaces();
			$places = $mPlaces->selectFromCampusId($campusid);

			$shifts = $mShifts->selectShiftGroup($termid, $dockinds[0]->id, $places[0]->id);
		}
		else
		{
			$shifts = $mShifts->selectShiftGroupForTwc($termid, $campusid, 1);
		}

		$this->view->assign('shifts', $shifts);

		$this->view->assign('dowmax', $this->dowmax);

		// テンプレート切り替え
		if ($templates != '')
		{
			$this->_helper->viewRenderer->setNoRender();
			$html = $this->view->render($templates);
			$this->getResponse()->setBody($html);
		}
	}

	public function utilizationAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '稼働率');

		AdminController::getagreementAction();

		$mShifts = new Class_Model_MShifts();

		// 一日毎のシフト時間帯の数
		$countDayno = $mShifts->countDayno(1);
		$this->view->assign('countDayno', $countDayno);

		// それ以前、それ以降を選ばせないために、
		// 最小の日付と最大の日付を取得する
		$mTerms = new Class_Model_MTerms();
		$dates = $mTerms->getMinandMaxDate();

		$this->view->assign('mindate', $dates->mindate);
		$this->view->assign('maxdate', $dates->maxdate);

		//$mPlaces = new Class_Model_MPlaces();
		// 2014/09/28 ishikawa
		// 利用統計関連に限り、非表示設定のデータも取得する
		//$places = $mPlaces->selectAllDisplay();
		//$places = $mPlaces->selectAll('order_num');
		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAll();
		$this->view->assign('campuses', $campuses);

		// シフト取得
		$shifts = $mShifts->selectShiftGroup(1, 1, 1);

		$this->view->assign('shifts', $shifts);

		// 曜日配列
		$dowarray = $this->getDowArray();
		$this->view->assign('dowarray', $dowarray);
	}

	public function getagreementAction()
	{
		$mSettings = new Class_Model_MSettings();
		$agreement = $mSettings->selectFromName('agreement');

		$this->view->assign('agreement', $agreement);
	}

	// 稼働率取得(ajax: 戻り値は JSONの配列)
	public function getutilizationAction()
	{
		// 期間取得
		$startdate = $this->getRequest()->startdate;
		$enddate = $this->getRequest()->enddate;

		$mTerms = new Class_Model_MTerms();

		// FromとToが空の場合、
		// Fromには登録されている最初の学期の開始日、
		// Toには前日が入力されたものとして扱う

		if($startdate != 0)
		{
			$start_term = $mTerms->getTermFromDate($startdate);
			$start_termid = $start_term->id;
		}
		else
		{
			$start_term = $mTerms->selectAll("id asc");
			$start_termid = $start_term[0]->id;

			$startdate = $start_term[0]->startdate;
		}

		if($enddate != 0)
		{
			$end_term = $mTerms->getTermFromDate($enddate);
			$end_termid = $end_term->id;
		}
		else
		{
			$now = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
			$enddate = date('Y-m-d', strtotime('-1 day', strtotime($now)));
		}


		$campusid = $this->getRequest()->shiftclass;

		if($campusid != 0)
		{
			$shiftclass = $campusid;
		}
		else
		{
			// 種別"全て"が選ばれたときのcampusidは0とする
			$shiftclass = 0;
		}

		// シフト取得
		// ※ 同じキャンパス内の最初の場所の最初の文書タイプのみ取得する
		$mShifts = new Class_Model_MShifts();
		$shifts = $mShifts->selectShiftGroup($start_termid, 1, 1);
		// その学期とキャンパスのスタッフを取得
		$tStaffshifts = new Class_Model_TStaffshifts();
		$staffshifts = $tStaffshifts->selectFromYmdAndCampusId($startdate, $shiftclass, $enddate);


		/*
		 *	1.曜日ベースでスタッフ数をカウント
		 */
		$shiftinput = array();

		// 重複をなくし曜日ベースで配列化する
		foreach ($staffshifts as $staffshift)
		{
			$dayno = $staffshift->m_shifts_dayno;
			$dow = $staffshift->dow;
			$memid = $staffshift->m_member_id;

			if(empty($shiftinput[$dow][$dayno]['staffs']))
				$shiftinput[$dow][$dayno]['staffs'] = array();

			if(empty($shiftinput[$dow][$dayno]['count_staff']))
				$shiftinput[$dow][$dayno]['count_staff'] = 0;

			if(empty($shiftinput[$dow][$dayno]['staffs'][$memid]))
			{
				$shiftinput[$dow][$dayno]['staffs'][$memid] = $staffshift->toArray();
				$shiftinput[$dow][$dayno]['count_staff']++;
			}
		}

		/*
		 *	2.1でカウントした数に、期間中に存在する各曜日の数を掛ける
		 */
		// 指定期間の日数を求める
		$startdate_array 	= explode("-", $startdate);
		$enddate_array 		= explode("-", $enddate);

		// 日付をUnixのタイムスタンプに変換する(month, day, year)
		$start_ts = mktime(0, 0, 0, $startdate_array[1], $startdate_array[2], $startdate_array[0]);
		$end_ts = mktime(0, 0, 0, $enddate_array[1], $enddate_array[2], $enddate_array[0]);

		// タイムスタンプの差を計算
		$difTime = $end_ts - $start_ts;

		// 日数を計算 1日は86400秒
		$difDay = $difTime / 86400;

		$datetime = new DateTime($startdate);
		$w = (int)$datetime->format('w');
		$rest = $difDay % 7;
		$sum = $w + $rest;

		for($i = 1; $i <= 5; $i++)
		{
			$week_nums[$i] = floor($difDay / 7);

			// 開始日の曜日から終了日の曜日と同じ曜日を+1
			if($sum >= 7)
			{
				if($i >= $w || $i <= ($sum % 7))
				{
					$week_nums[$i]++;
				}
			}
			else
			{
				if($i >= $w && $i <= $sum)
				{
					$week_nums[$i]++;
				}
			}
		}

		// 一日毎のシフト時間帯の数
		$countDayno = $mShifts->countDayno($start_termid);

		// 何もデータがない部分は埋め、スタッフ数のデータがあれば曜日の数を掛ける
		for ($dow = 1; $dow <= 5; $dow++)
		{
			for ($dayno = 1; $dayno <= $countDayno; $dayno++)
			{
				if (empty($shiftinput[$dow][$dayno]))
				{
					$shiftinput[$dow][$dayno] = array();
				}

				if (!empty($shiftinput[$dow][$dayno]['count_staff']))
				{
					$shiftinput[$dow][$dayno]['count_staff'] = $shiftinput[$dow][$dayno]['count_staff'] * $week_nums[$dow];
				}
			}
		}

		/*	2014/09/19 ishikawa
		 *	3.既にデータがあればカウント数を追加、なければデータ・カウントを共に追加する
		*/

		// そのシフト種別と日付範囲のスタッフを取得
		$tShiftdetails = new Class_Model_TShiftdetails();

		$shiftdetails = $tShiftdetails->selectFromRange($shiftclass, $startdate, $enddate);


		// 重複をなくし曜日ベースで配列化する
		foreach ($shiftdetails as $shiftdetail)
		{
			$dayno = $shiftdetail->m_shifts_dayno;
			$dow = $shiftdetail->dow;
			$memid = $shiftdetail->m_member_id;
			$type = $shiftdetail->type;

			if(empty($shiftinput[$dow][$dayno]['staffs']))
				$shiftinput[$dow][$dayno]['staffs'] = array();

			if(empty($shiftinput[$dow][$dayno]['count_staff']))
				$shiftinput[$dow][$dayno]['count_staff'] = 0;

			if(empty($shiftinput[$dow][$dayno]['staffs'][$memid]))
			{
				$shiftinput[$dow][$dayno]['staffs'][$memid] = $shiftdetail->toArray();

				if($type == 1)
					$shiftinput[$dow][$dayno]['count_staff']++;
				else
					$shiftinput[$dow][$dayno]['count_staff']--;

				// 一度でも修正があればフラグを埋める
				$shiftinput[$dow][$dayno]['staffs'][$memid]['modified'] = 1;
			}
			else
			{
				if(empty($shiftinput[$dow][$dayno]['staffs'][$memid]['modified']))
				{
					if($type == 1)
						$shiftinput[$dow][$dayno]['count_staff']++;
					else
						$shiftinput[$dow][$dayno]['count_staff']--;

					// 一度でも修正があればフラグを埋める
					$shiftinput[$dow][$dayno]['staffs'][$memid]['modified'] = 1;
				}
			}
		}

		$tReserves = new Class_Model_TReserves();

		// そのキャンパスと期間の予約数を取得
		$reserves = $tReserves->selectFromCampusIdAndRange($campusid, $startdate, $enddate);


		// 曜日・時間帯毎に予約データ数をインクリメントする
		foreach ($reserves as $reserve)
		{
			$dayno = $reserve->m_shifts_dayno;
			$reservationdate = $reserve->reservationdate;
			$datetime = new DateTime($reservationdate);
			$dow = (int)$datetime->format('w');

			if (empty($shiftinput[$dow][$dayno]['count_reserve']))
			{
				$shiftinput[$dow][$dayno]['count_reserve'] = 0;
			}

			$shiftinput[$dow][$dayno]['count_reserve']++;
		}

		echo json_encode(array('shiftinput' => $shiftinput));
		exit;
	}

	public function byreserveformAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '予約形態別利用状況');

		AdminController::getagreementAction();

		$mShifts = new Class_Model_MShifts();

		// 一日毎のシフト時間帯の数
		$countDayno = $mShifts->countDayno(1);
		$this->view->assign('countDayno', $countDayno);

		// それ以前、それ以降を選ばせないために、
		// 最小の日付と最大の日付を取得する
		$mTerms = new Class_Model_MTerms();
		$dates = $mTerms->getMinandMaxDate();

		$this->view->assign('mindate', $dates->mindate);
		$this->view->assign('maxdate', $dates->maxdate);

		// 曜日配列
		$dowarray = $this->getDowArray();
		$this->view->assign('dowarray', $dowarray);

		if (APPLICATION_TYPE != 'twc')
		{
			// シフト取得
			$shifts = $mShifts->selectShiftGroup(1, 1, 1);

			$this->view->assign('shifts', $shifts);
		}
		else
		{
			// シフト取得
			$shifts = $mShifts->selectShiftGroupForTwc(1, 1, 1);

			$this->view->assign('shifts', $shifts);
		}
	}

	// 予約形態別利用状況取得(ajax: 戻り値は JSONの配列)
	public function getbyreserveformAction()
	{
		// 期間取得
		$startdate = $this->getRequest()->startdate;
		$enddate = $this->getRequest()->enddate;

		AdminController::getagreementAction();

		$mTerms = new Class_Model_MTerms();

		// 20140905 ※暫定的仕様※
		// FromとToが空の場合、
		// Fromには登録されている最初の学期の開始日、
		// Toには本日の日付が入力されたものとして扱う

		if($startdate != 0)
		{
			$start_term = $mTerms->getTermFromDate($startdate);
			$start_termid = $start_term->id;
		}
		else
		{
			$start_term = $mTerms->selectAll("id asc");
			$start_termid = $start_term[0]->id;

			//$startdate = $start_term[0]->startdate;
		}

// 		if($enddate != 0)
// 		{
// 			$end_term = $mTerms->getTermFromDate($enddate);
// 			$end_termid = $end_term->id;
// 		}
// 		else
// 		{
// 			$enddate = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
// 		}

		$tReserves = new Class_Model_TReserves();

		// その期間の予約を取得
		$reserves = $tReserves->selectFromTermRangeForCount($startdate, $enddate);

		$shiftinput = array();

		// 曜日ベースで配列化し、同時に各件数をカウントする
		foreach ($reserves as $reserve)
		{
			$dayno = $reserve->m_shifts_dayno;
			$datetime = new DateTime($reserve->reservationdate);
			$dow = (int)$datetime->format('w');

			if(empty($shiftinput[$dow][$dayno]['count_total']))
				$shiftinput[$dow][$dayno]['count_total'] = 0;

			$shiftinput[$dow][$dayno]['count_total']++;
		}

		$mShifts = new Class_Model_MShifts();

		// 一日毎のシフト時間帯の数
		$countDayno = $mShifts->countDayno($start_termid);
		// 予約形態
		$form = $this->getRequest()->form;

		// 何もデータがない部分は埋める
		for ($dow = 1; $dow <= 5; $dow++)
		{
			for ($dayno = 1; $dayno <= $countDayno; $dayno++)
			{
				if (empty($shiftinput[$dow][$dayno]))
				{
					$shiftinput[$dow][$dayno] = array();
				}

				if($form == 0)
				{
					if(!empty($shiftinput[$dow][$dayno]['count_total']))
					{
						// 事前予約
						$shiftinput[$dow][$dayno]['count_run'] = $shiftinput[$dow][$dayno]['count_total'];
					}
					else
					{
						$shiftinput[$dow][$dayno]['count_run'] = 0;
					}
				}
				else
				{
					// 駆け込み予約
					$shiftinput[$dow][$dayno]['count_run'] = 0;
				}
			}
		}

		// その期間の駆け込み予約を取得
		$runreserves = $tReserves->selectRunReserveFromTermRangeForCount($startdate, $enddate);

		// 曜日・時間帯毎に予約データ数を(駆け込み予約：インクリメント/事前予約：デクリメント)する
		foreach ($runreserves as $runreserve)
		{
			$dayno = $runreserve->m_shifts_dayno;
			$datetime = new DateTime($runreserve->reservationdate);
			$dow = (int)$datetime->format('w');

			if($form == 0)
			{
				$shiftinput[$dow][$dayno]['count_run']--;
			}
			else
			{
				$shiftinput[$dow][$dayno]['count_run']++;
			}
		}

		echo json_encode(array('shiftinput' => $shiftinput));
		exit;
	}

	public function byfacultyandclassAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '学部・学年別利用状況');

		AdminController::getagreementAction();

		$tSyozoku1 = new Class_Model_TSyozoku1();

		// (表示する)学部数
		// 2014/09/28 ishikawa
		// 利用統計関連に限り、非表示設定のデータも取得する
		//$faculties = $mFaculties->selectAllDisplay();
		$faculties = $tSyozoku1->selectAll('z008szsrt_no asc');
		$this->view->assign('faculties', $faculties);
		$this->view->assign('countFaculty', count($faculties));

		// 年度取得
// 		$year = date('Y', strtotime(Zend_Registry::get('nowdate')));
// 		$this->view->assign('year', $year);

		// 2016/01/18 システム上の年度を取得する(以前は西暦を取得していた)
		$mNendo = new Class_Model_MNendo();
		$nendo_row = $mNendo->selectRow();
		$year = $nendo_row->current_nendo;
		$this->view->assign('year', $year);

		$mPlaces = new Class_Model_MPlaces();
		// 場所取得
		// 2014/09/28 ishikawa
		// 利用統計関連に限り、非表示設定のデータも取得する
		//$places = $mPlaces->selectAllDisplay();
		$places = $mPlaces->selectAll('order_num');
		$this->view->assign('places', $places);

		// それ以前、それ以降を選ばせないために、
		// 最小の日付と最大の日付を取得する
		$mTerms = new Class_Model_MTerms();
		$dates = $mTerms->getMinandMaxDate();

		$this->view->assign('mindate', $dates->mindate);
		$this->view->assign('maxdate', $dates->maxdate);

		$mDockinds = new Class_Model_MDockinds();
		// 文書種別取得
		// 2014/09/28 ishikawa
		// 利用統計関連に限り、非表示設定のデータも取得する
		//$shiftclasses = $mDockinds->selectAllDisplay();
		$shiftclasses = $mDockinds->selectAll('order_num');
		$this->view->assign('shiftclasses', $shiftclasses);
	}

	// 学部・年度別利用状況取得(ajax: 戻り値は JSONの配列)
	public function getfacultyandclassAction()
	{
		// 場所
		$placeid = $this->getRequest()->placeid;
		// 種別
		$shiftclass = $this->getRequest()->shiftclass;

		// 期間
		$startdate = $this->getRequest()->startdate;
		$enddate = $this->getRequest()->enddate;

		$mTerms = new Class_Model_MTerms();

		// 20140905 ※暫定的仕様※
		// FromとToが空の場合、
		// Fromには登録されている最初の学期の開始日、
		// Toには本日の日付が入力されたものとして扱う

		if($startdate != 0)
		{
			$start_term = $mTerms->getTermFromDate($startdate);
			$start_termid = $start_term->id;
		}
		else
		{
			$start_term = $mTerms->selectAll("id asc");
			$start_termid = $start_term[0]->id;

			//$startdate = $start_term[0]->startdate;
		}

// 		if($enddate != 0)
// 		{
// 			$end_term = $mTerms->getTermFromDate($enddate);
// 			$end_termid = $end_term->id;
// 		}
// 		else
// 		{
// 			$enddate = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
// 		}

		$tReserves = new Class_Model_TReserves();

		// その期間の予約を取得
		if (APPLICATION_TYPE != 'twc')
		{
			$reserves = $tReserves->selectFromTermRangeAndPlaceIdAndDockindIdForCount($startdate, $enddate, $placeid, $shiftclass);
		}
		else
		{
			$reserves = $tReserves->selectFromTermRangeAndPlaceIdAndShiftclassForCount($startdate, $enddate, $placeid, $shiftclass);
		}

		// (表示する)年度
		$entranceYears = array();

// 		$tmpYear = date('Y', strtotime(Zend_Registry::get('nowdate')));

		// 2016/01/18 システム上の年度を取得する(以前は西暦を取得していた)
		$mNendo = new Class_Model_MNendo();
		$nendo_row = $mNendo->selectRow();
		$tmpYear = $nendo_row->current_nendo;

		for($i = 0; $i < 4; $i++)
		{
			$entranceYears[$i] = $tmpYear - $i;
		}
		$entranceYears[] = 'others';

		$shiftinput = array();

		// 曜日ベースで配列化し、同時に各件数をカウントする
		foreach ($reserves as $reserve)
		{
			if(empty($reserve->entrance_year) || empty($reserve->syozkcd1))
				continue;

			$x		= $reserve->entrance_year;

			// 空、数値ではない、4年以上昔に入学の場合はその他として扱う
			if(empty($x) || !is_numeric($x) || $x < $entranceYears[3])
				$x = 'others';
			$y		= $reserve->setti_cd . '_' . $reserve->syozkcd1;
			$sex	= $reserve->sex;


			if(empty($shiftinput[$x][$y]['count_male']))
				$shiftinput[$x][$y]['count_male'] = 0;

			if(empty($shiftinput[$x][$y]['count_female']))
				$shiftinput[$x][$y]['count_female'] = 0;

			if($sex == 1)
				$shiftinput[$x][$y]['count_male']++;
			else
				$shiftinput[$x][$y]['count_female']++;
		}

		$mMembers = new Class_Model_MMembers();


		$tSyozoku1 = new Class_Model_TSyozoku1();
		// (表示する)学部数
		$faculties = $tSyozoku1->selectAll();

		// 予約形態
		$form = $this->getRequest()->form;

		// 何もデータがない部分は埋める
		foreach ($entranceYears as $entranceYear)
		{
			foreach ($faculties as $faculty)
			{
				if (empty($shiftinput[$entranceYear][$faculty->setti_cd . '_' . $faculty->syozkcd1]))
				{
					$shiftinput[$entranceYear][$faculty->setti_cd . '_' . $faculty->syozkcd1] = array();
				}
			}
		}

		echo json_encode(array('inputdata' => $shiftinput));
		exit;
	}

	public function obj2arr($obj)
	{
		if ( !is_object($obj) ) return $obj;

		$arr = (array) $obj;

		foreach ( $arr as &$a )
		{
			$a = $this->obj2arr($a);
		}

		return $arr;
	}

	// 2014/09/26 ishikawa
	// 利用データダウンロード
	public function exportdataAction()
	{
		$tReserves = new Class_Model_TReserves();

		$reserves = $tReserves->selectAllForDownload();

		$tmp = array();

		$csv = array();
		$cnt = 1;
		$weekday = array( "日", "月", "火", "水", "木", "金", "土" );

		// 見出し
		$csv[0] = array();
		$csv[0]['count']				= 'No.';
		$csv[0]['id']					= 'ID';
		$csv[0]['reservationdate'] 		= '相談年月日';
		$csv[0]['dow'] 					= '相談曜日';
		$csv[0]['starttime'] 			= '相談時刻(シフト)';
		$csv[0]['m_faculties_name']		= '相談者所属学部';
		$csv[0]['academic_year'] 		= '学年';
		$csv[0]['document_category']	= '文書の種類';
		$csv[0]['consul_place'] 		= '相談場所';
		$csv[0]['class_subject'] 		= '授業科目';
		$csv[0]['submitdate'] 			= '提出日';
		$csv[0]['progress'] 			= '進行状況';
		$csv[0]['question'] 			= '相談したいこと';
		$csv[0]['charge_name_jp'] 		= '担当者';
		$csv[0]['f_cancel'] 			= 'ドタキャン';
		$csv[0]['counsel'] 				= '相談内容';
		$csv[0]['teaching'] 			= '指導内容';
		$csv[0]['remark'] 				= '所感';
		$csv[0]['summary'] 				= '備考';
		$csv[0]['leading_comment'] 		= 'スタッフからのコメント';
		$csv[0]['reservecomment'] 		= '学生からのコメント';

		// 出力データの整形
		foreach($reserves as $t)
		{
			$csv[$cnt] = array();
			$csv[$cnt]['count']				= $cnt;											// No.
			$csv[$cnt]['id']				= $t['m_member_id_reserver'];					// ID
			$csv[$cnt]['reservationdate'] 	= $t['reservationdate'];						// 相談年月日
			$csv[$cnt]['dow'] 				= $weekday[date('w', strtotime($t['reservationdate']))];	// 相談曜日
			$csv[$cnt]['starttime'] 		= substr($t['m_timetables_starttime'], 0, -3) . '-' . substr($t['m_timetables_endtime'], 0, -3);	// 相談時刻(シフト)
			$csv[$cnt]['m_faculties_name'] 	= $t['m_faculties_name'];						// 相談者所属学部
			$csv[$cnt]['academic_year'] 	= $t['reserver_academic_year'];					// 学年
			$csv[$cnt]['document_category'] = $t['m_dockinds_document_category'];			// 文書の種類
			$csv[$cnt]['consul_place'] 		= $t['m_places_consul_place'];					// 相談場所
			$csv[$cnt]['class_subject'] 	= $t['m_subjects_class_subject'];				// 授業科目
			$csv[$cnt]['submitdate'] 		= $t['submitdate'];								// 提出日
			if(!empty($this->progresssal[$t['progress']]))									// 進行状況
				$csv[$cnt]['progress'] 		= $this->progresssal[$t['progress']];
			else
				$csv[$cnt]['progress'] 		= '';
			$csv[$cnt]['question'] 			= $t['question'];								// 相談したいこと
			$csv[$cnt]['charge_name_jp'] 	= $t['charge_name_jp'];							// 担当者
			$csv[$cnt]['f_cancel'] 			= $t['t_leadings_f_cancel'];					// ドタキャン
			$csv[$cnt]['counsel'] 			= $t['t_leadings_counsel'];						// 相談内容
			$csv[$cnt]['teaching'] 			= $t['t_leadings_teaching'];					// 指導内容
			$csv[$cnt]['remark'] 			= $t['t_leadings_remark'];						// 所感
			$csv[$cnt]['summary'] 			= $t['t_leadings_summary'];						// 備考
			$csv[$cnt]['leading_comment'] 	= $t['t_leadings_leading_comment'];				// スタッフからのコメント
			$csv[$cnt]['reservecomment'] 	= $t['t_reserve_comments_reservecomment'];		// 学生からのコメント
			$cnt++;
		}

		AdminController::openCsv($csv);
	}

	public function openCsv($csv)
	{
		// オブジェクトから配列に変換(未使用)
		// fetchAllの返り値(オブジェクトZend_Db_Table_Rowset型)を直でfputcsv(fp, array)に流すなら有用
		function object_to_array($data)
		{
			if (is_array($data) || is_object($data))
			{
				$result = array();
				foreach ($data as $key => $value)
				{
					$result[$key] = object_to_array($value);
				}
				return $result;
			}
			return $data;
		}

		// エンコードをExcelに対応するSJIS-winへ変換
		mb_convert_variables("SJIS-win", "UTF-8", $csv);

		$filename = '利用データ(' . date('Ymd_hms', strtotime(Zend_Registry::get('nowdatetime'))) . ').csv';

		// LayoutとViewの無効化
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		// 保存ダイアログを開く
		header("Content-Disposition: attachment; filename=\"$filename\"");
		// Content-Type
		header("Content-Type: text/csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		// 標準出力へ出力する準備
		$fp= fopen('php://output', 'w');
		// 配列をCSVへ書き込み
		foreach ($csv as $key => $value)
		{
			fputcsv($fp, $value);
		}
		fclose($fp);
	}

	// 2014/09/29 ishikawa
	// 予約変更履歴テーブルのデータダウンロード
	public function exporthistoryAction()
	{
		$tHistories = new Class_Model_TReserveHistory();
		$reserves = $tHistories->selectAllForDownload();

		$translate = Zend_Registry::get('Zend_Translate');

		$tmp = array();

		$csv = array();
		$cnt = 1;

		if(!Zend_Registry::isRegistered('Zend_Locale') || Zend_Registry::get('Zend_Locale') == 'ja')
			$weekday = array( "日", "月", "火", "水", "木", "金", "土" );
		else
			$weekday = array( "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" );

		$hclass = array("", $translate->_("予約"), $translate->_("変更"), $translate->_("削除"), $translate->_("コメント"));
		$dflag = array("", $translate->_("削除済み"));

		// 見出し
		$csv[0] = array();
		$csv[0]['count']				= $translate->_("No.");
		$csv[0]['id']					= $translate->_("ID");
		$csv[0]['reservationdate'] 		= $translate->_("相談年月日");
		$csv[0]['dow'] 					= $translate->_("相談曜日");
		$csv[0]['starttime'] 			= $translate->_("相談時刻(シフト)");
		$csv[0]['szknam_c']				= $translate->_("相談者所属学部");
		$csv[0]['gaknenkn']		 		= $translate->_("学年");
		$csv[0]['historyclass'] 		= $translate->_("登録種別");
		$csv[0]['delete_flag'] 			= $translate->_("相談状態");
		$csv[0]['run_reserve'] 			= $translate->_("駆け込み予約");
		$csv[0]['document_category']	= $translate->_("文書の種類");
		$csv[0]['consul_place'] 		= $translate->_("相談場所");
		$csv[0]['class_subject'] 		= $translate->_("授業科目");
		$csv[0]['submitdate'] 			= $translate->_("提出日");
		$csv[0]['progress'] 			= $translate->_("進行状況");
		$csv[0]['question'] 			= $translate->_("相談したいこと");
		$csv[0]['charge_name_jp'] 		= $translate->_("担当者");
		$csv[0]['f_cancel'] 			= $translate->_("ドタキャン");
		$csv[0]['counsel'] 				= $translate->_("相談内容");
		$csv[0]['teaching'] 			= $translate->_("指導内容");
		$csv[0]['remark'] 				= $translate->_("所感");
		$csv[0]['summary'] 				= $translate->_("備考");
		$csv[0]['leading_comment'] 		= $translate->_("スタッフからのコメント");
		$csv[0]['reservecomment'] 		= $translate->_("学生からのコメント");

		// 出力データの整形
		foreach($reserves as $t)
		{
			$csv[$cnt] = array();
			$csv[$cnt]['count']					= $cnt;											// No.
			$csv[$cnt]['id']					= $t['m_member_id_reserver'];					// ID
			$csv[$cnt]['reservationdate'] 		= $t['reservationdate'];						// 相談年月日
			$csv[$cnt]['dow'] 					= $weekday[date('w', strtotime($t['reservationdate']))];	// 相談曜日
			$csv[$cnt]['starttime'] 			= substr($t['m_timetables_starttime'], 0, -3) . '-' . substr($t['m_timetables_endtime'], 0, -3);	// 相談時刻(シフト)
			$csv[$cnt]['szknam_c'] 				= $t['t_syozoku1_szknam_c'];					// 相談者所属学部
			$csv[$cnt]['gaknenkn']		 		= $t['gaknenkn'];								// 学年
			$csv[$cnt]['historyclass'] 			= $hclass[$t['historyclass']];					// 登録種別
			$csv[$cnt]['delete_flag']	 		= $dflag[$t['delete_flag']];					// 相談状態
			$csv[$cnt]['run_reserve']	 		= $t['run_reserve'] == 1 ? '○' : '';			// 駆け込み予約
			$csv[$cnt]['document_category'] 	= $t['m_dockinds_document_category'];			// 文書の種類
			$csv[$cnt]['consul_place'] 			= $t['m_places_consul_place'];					// 相談場所
			$csv[$cnt]['class_subject'] 		= $t['class_subject'];							// 授業科目
			$csv[$cnt]['submitdate'] 			= $t['submitdate'];								// 提出日
			if(!empty($this->progresssal[$t['progress']]))										// 進行状況
				$csv[$cnt]['progress'] 			= $this->progresssal[$t['progress']];
			else
				$csv[$cnt]['progress'] 			= '';
			$csv[$cnt]['question'] 				= $t['question'];								// 相談したいこと
			$csv[$cnt]['charge_name_jp'] 		= $t['charge_name_jp'];							// 担当者
			$csv[$cnt]['cancel_flag'] 			= $t['t_leadings_cancel_flag'] == 1 ? '○' : '';	// ドタキャン
			$csv[$cnt]['counsel'] 				= $t['t_leadings_counsel'];						// 相談内容
			$csv[$cnt]['teaching'] 				= $t['t_leadings_teaching'];					// 指導内容
			$csv[$cnt]['remark'] 				= $t['t_leadings_remark'];						// 所感
			$csv[$cnt]['summary'] 				= $t['t_leadings_summary'];						// 備考
			$csv[$cnt]['leading_comment'] 		= $t['t_leadings_leading_comment'];				// スタッフからのコメント
			$csv[$cnt]['reservecomment'] 		= $t['t_reserve_comments_reservecomment'];		// 学生からのコメント
			$cnt++;
		}

		AdminController::openCsv($csv);
	}

	// 未使用、エクセルの場合
	// 別途PHPExcelが必要
	public function testAction()
	{
		require_once '/plugins/PHPExcel.php';

		$excel = new PHPExcel();
		// シートの設定
		$excel->setActiveSheetIndex(0);
		$sheet = $excel->getActiveSheet();
		$sheet->setTitle('sheet name');

		// セルに値を入れる
		$tReserves = new Class_Model_TReserves();

		$reserves = $tReserves->selectAllForDownload();

		// 見出し
		$sheet->setCellValue('A1', 'No.');
		$sheet->setCellValue('B1', '相談年月日');
		$sheet->setCellValue('C1', '相談曜日');
		$sheet->setCellValue('D1', '相談時刻(シフト)');
		$sheet->setCellValue('E1', '相談者所属学部');
		$sheet->setCellValue('F1', '学年');
		$sheet->setCellValue('G1', '登録種別');
		$sheet->setCellValue('H1', '相談状態');
		$sheet->setCellValue('I1', '文書の種類');
		$sheet->setCellValue('J1', '相談場所');
		$sheet->setCellValue('K1', '授業科目');
		$sheet->setCellValue('L1', '提出日');
		$sheet->setCellValue('M1', '進行状況');
		$sheet->setCellValue('N1', '相談したいこと');
		$sheet->setCellValue('O1', '担当者');
		$sheet->setCellValue('P1', 'ドタキャン');
		$sheet->setCellValue('Q1', '相談内容');
		$sheet->setCellValue('R1', '指導内容');
		$sheet->setCellValue('S1', '所感');
		$sheet->setCellValue('T1', '備考');
		$sheet->setCellValue('U1', 'スタッフからのコメント');
		$sheet->setCellValue('V1', '学生からのコメント');

		$alpha = array();
		for ($i = 'A'; $i <= 'V'; $i++) {
			$sheet->getStyle($i . '1')->applyFromArray(
			array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => 'FF0000')
					)
				)
			);
		}

		$tmp = array();

		$csv = array();
		$cnt = 2;
		$weekday = array( "日", "月", "火", "水", "木", "金", "土" );

		// 出力データの整形
		foreach($reserves as $t)
		{
			$sheet->setCellValue('A' . $cnt, $cnt-1);										// No.
			$sheet->setCellValue('B' . $cnt, $t['reservationdate']);						// 相談年月日
			$sheet->setCellValue('C' . $cnt, $weekday[date('w', strtotime($t['reservationdate']))]);	// 相談曜日
			$sheet->setCellValue('D' . $cnt, substr($t['m_timetables_starttime'], 0, -3) . '-' . substr($t['m_timetables_endtime'], 0, -3));	// 相談時刻(シフト)
			$sheet->setCellValue('E' . $cnt, $t['m_faculties_name']);						// 相談者所属学部
			$sheet->setCellValue('F' . $cnt, $t['reserver_academic_year']);					// 学年
			$sheet->setCellValue('G' . $cnt, $t['m_dockinds_document_category']);			// 文書の種類
			$sheet->setCellValue('H' . $cnt, $t['m_places_consul_place']);					// 相談場所
			$sheet->setCellValue('I' . $cnt, $t['m_subjects_class_subject']);				// 授業科目
			$sheet->setCellValue('J' . $cnt, $t['submitdate']);								// 提出日
			if(!empty($this->progresssal[$t['progress']]))									// 進行状況
				$sheet->setCellValue('K' . $cnt, $this->progresssal[$t['progress']]);
			else
				$sheet->setCellValue('K' . $cnt, '');
			$sheet->setCellValue('L' . $cnt, $t['question']);								// 相談したいこと
			$sheet->setCellValue('M' . $cnt, $t['charge_name_jp']);							// 担当者
			$sheet->setCellValue('N' . $cnt, $t['t_leadings_f_cancel']);					// ドタキャン
			$sheet->setCellValue('O' . $cnt, $t['t_leadings_counsel']);						// 相談内容
			$sheet->setCellValue('P' . $cnt, $t['t_leadings_teaching']);					// 指導内容
			$sheet->setCellValue('Q' . $cnt, $t['t_leadings_remark']);						// 所感
			$sheet->setCellValue('R' . $cnt, $t['t_leadings_summary']);						// 備考
			$sheet->setCellValue('S' . $cnt, $t['t_leadings_leading_comment']);				// スタッフからのコメント
			$sheet->setCellValue('T' . $cnt, $t['t_reserve_comments_reservecomment']);		// 学生からのコメント
			$cnt++;
		}
		$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$writer->save('php://output');

		// ダウンロード
		$this->getResponse()
		->setHeader('Content-Type', 'application/vnd.ms-excel')
		->setHeader('Content-Disposition', 'attachment; filename="test.xlsx"')
		->setHeader('Cache-Control', 'no-cache')
		->setHeader('Pragma', 'no-cache')
		->sendResponse();
		exit;
	}



	// 学期/シフト入力許可設定画面
	public function termandshiftAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '学期/シフト入力許可設定');

		/* 全年度取得 */
		$mTerms = new Class_Model_MTerms();
		$years = $mTerms->getYears();
		$this->view->assign('years', $years);

		$latest_year = $mTerms->getLatestYear();
		$this->view->assign('latest_year', $latest_year->year);

		$y = date('Y', strtotime(Zend_Registry::get('nowdate')));

		// 現在の年度を取得
		$mNendo = new Class_Model_MNendo();
		$nendo_row = $mNendo->selectRow();
		$this_year = $nendo_row->current_nendo;
		$this->view->assign('this_year', $this_year);

		/* 該当年度の学期情報取得 */
		$year_selected = $this->getRequest()->year;

		if(empty($year_selected))
		{
			// デフォルトは次年度
			$year_selected = $this_year;
		}

		$terms = $mTerms->selectFromYear($year_selected);

		$this->view->assign('year_selected', $year_selected);
		$this->view->assign('terms', $terms);

		// 削除可能である旨を示すフラグを場所毎に設定する
		// ※該当場所IDでの予約が存在しなければ削除可能
		$delete_flg = array();
		$term_all = $mTerms->selectAll('id');
		foreach($term_all as $v)
		{
			$delete_flg[$v->id] = $mTerms->getReservedTermId($v->id);
		}
		$this->view->assign('flg', $delete_flg);

		$max_term = $mTerms->selectAll('id desc');
		$this->view->assign('nextid', $max_term[0]->id + 1);
	}

	// 学期/シフト入力許可設定処理
	public function updatetermAction()
	{
		$year				= $this->getRequest()->year;
		$termid				= $this->getRequest()->termid;
		$term_name			= $this->getRequest()->term_name;
		$term_startdate		= $this->getRequest()->term_startdate;
		$term_enddate		= $this->getRequest()->term_enddate;
		$shift_startdate	= $this->getRequest()->shift_startdate;
		$shift_enddate		= $this->getRequest()->shift_enddate;

		$translate = Zend_Registry::get('Zend_Translate');

		$mTerms = new Class_Model_MTerms();
		$mShifts = new Class_Model_MShifts();
		$tStaffshifts = new Class_Model_TStaffshifts();
		$tShiftlimits = new Class_Model_TShiftlimits();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// 登録・更新処理
			foreach($termid as $key => $value)
			{
				// パラメータチェック
				$result = array('error' => '');
				$flg = 0;

				// いずれかの項目が空ならエラー
				if (!(empty($term_name[$value]) && (empty($term_startdate[$value]) || empty($term_enddate[$value])) && (empty($shift_startdate[$value]) || empty($shift_enddate[$value]))))
				{
					if(empty($term_name[$value]))
						$result['error'] .= $translate->_("学期名称を入力してください") . "\n";

					if(empty($term_startdate[$value]) || empty($term_enddate[$value]))
						$result['error'] .= $translate->_("学期期間を入力してください") . "\n";

					if(empty($shift_startdate[$value]) || empty($shift_enddate[$value]))
						$result['error'] .= $translate->_("シフト入力許可期間を入力してください") . "\n";
				}
				else
				{
					// 全ての項目が空なら削除のみを行う(通常の更新処理をスキップする)
					$flg = 1;
				}

				if ($result['error'] != '')
				{
					$db->rollback();
					echo json_encode($result);
					exit;
				}

				$params = array(
						'name'				=> $term_name[$value],
						'startdate'			=> $term_startdate[$value],
						'enddate'			=> $term_enddate[$value],
						'year'				=> $year,
						'shift_startdate'	=> $shift_startdate[$value],
						'shift_enddate'		=> $shift_enddate[$value],
				);

				if($flg != 1)
				{
					if(count($mTerms->selectFromId($value)) > 0)
					{
						$mTerms->updateFromId($value, $params);
					}
					else
					{
						$id = $mTerms->insert($params);

						$mShifts->insertShiftFromTerm($id);
						// 学期挿入時に受入数・シフト挿入は必要なし
						//$tStaffshifts->insertStaffshiftFromTerm($id);
						//$tShiftlimits->insertShiftlimitsFromTerm($id);
					}
				}
			}

			$deleteid	= $this->getRequest()->deleteid;

			if(!empty($deleteid))
			{
				foreach($deleteid as $d)
				{
					// 2014/10/11 ishikawa
					// 場所マスタとシフトマスタから該当場所IDのデータを削除
					// 同様にスタッフシフトと受入数も削除する
					$tStaffshifts->deleteFromTermId($d);
					$tShiftlimits->deleteFromTermId($d);

					// シフトマスタ表のデータはスタッフシフト/受入数より後に削除すること
					$mShifts->deleteFromTermId($d);
					$mTerms->deleteFromId($d);
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

		echo json_encode(array('success' => 1));
		exit;
	}

	// 年度追加処理
	public function addyearAction()
	{
		$mTerms = new Class_Model_MTerms();
		$mShifts = new Class_Model_MShifts();
		$tStaffshifts = new Class_Model_TStaffshifts();
		$tShiftlimits = new Class_Model_TShiftlimits();

		$latest_year = $mTerms->getLatestYear();
		$latest_terms = $mTerms->selectFromYear($latest_year->year);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			foreach($latest_terms as $term)
			{
				$params = array(
						'name'				=> $term->name,
						'startdate'			=> date('Y-m-d', strtotime($term->startdate . " +1 year")),
						'enddate'			=> date('Y-m-d', strtotime($term->enddate . " +1 year")),
						'year'				=> $term->year + 1,
						'shift_startdate'	=> date('Y-m-d', strtotime($term->shift_startdate . " +1 year")),
						'shift_enddate'		=> date('Y-m-d', strtotime($term->shift_enddate . " +1 year")),
				);

				$id = $mTerms->insert($params);

				$mShifts->insertShiftFromTerm($id);
				// 学期挿入時に受入数・シフト挿入は必要なし
				//$tStaffshifts->insertStaffshiftFromTerm($id);
				//$tShiftlimits->insertShiftlimitsFromTerm($id);
			}

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('success' => 1));
		exit;
	}

	// 相談場所設定画面
	public function placeAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '相談場所設定');

		/* 全キャンパス取得 */
		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAll("order_num asc");

		$this->view->assign('campuses', $campuses);

		/* 全場所取得：非表示の場所も含む */
		$mPlaces = new Class_Model_MPlaces();

		// 場所を格納したオブジェクトをキャンパス毎の配列に割り当てる
		$places = array();
		for($i = 1; $i <= count($campuses); $i++)
		{
			$places['data'][$i] = $mPlaces->selectAllFromCampusId($i);
		}
		$this->view->assign('places', $places);

		// 削除可能である旨を示すフラグを場所毎に設定する
		// ※該当場所IDでの予約が存在しなければ削除可能
		$delete_flg = array();
		$place_all = $mPlaces->selectAll('id');
		foreach($place_all as $v)
		{
			$delete_flg[$v->id] = $mPlaces->getReservedPlaceId($v->id);
		}
		$this->view->assign('flg', $delete_flg);

		$max_place = $mPlaces->selectAll('id desc');
		$this->view->assign('nextid', $max_place[0]->id + 1);
	}

	// 相談場所設定処理
	public function updateplaceAction()
	{
		/* 全キャンパス取得 */
		$mCampuses = new Class_Model_MCampuses();
		$campuses = $mCampuses->selectAll("order_num asc");

		$mPlaces = new Class_Model_MPlaces();
		$mShifts = new Class_Model_MShifts();
		$tStaffshifts = new Class_Model_TStaffshifts();
		$tShiftlimits = new Class_Model_TShiftlimits();

		$translate = Zend_Registry::get('Zend_Translate');

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			foreach($campuses as $i)
			{
				$display_flg		= $this->getRequest()->{'checkbox' . $i->id};
				$consul_places		= $this->getRequest()->{'place' . $i->id};
				$order_num			= $this->getRequest()->{'order_num' . $i->id};

				// 登録・更新処理
				foreach($consul_places as $key => $value)
				{
					// パラメータチェック
					$result = array('error' => '');

					if (empty($value))
						$result['error'] .= $translate->_("相談場所の名称を入力してください") . "\n";

					if ($result['error'] != '')
					{
						$db->rollback();
						echo json_encode($result);
						exit;
					}

					if(!empty($display_flg[$key]))
						$d_flg = 0;
					else
						$d_flg = 1;

					if(count($mPlaces->selectFromId($key)) > 0)
					{
						$params = array(
							'consul_place'	=> $value,
							'display_flg'	=> $d_flg,
							'order_num'		=> $order_num[$key],
						);

						$mPlaces->updateFromId($key, $params);
					}
					else
					{
						$params = array(
							'consul_place'	=> $value,
							'm_campus_id'	=> $i->id,
							'display_flg'	=> $d_flg,
							'order_num'		=> $order_num[$key],
						);

						$id = $mPlaces->insert($params);

						// 2014/10/11 ishikawa
						// 場所新規挿入の場合はシフトマスタを更新する
						// 関大：スタッフのシフトと受入数は、挿入された場所IDでも入力されていたと見なす
						// 津大：まだ
						$mShifts->insertShiftFromPlace($id);
						$tStaffshifts->insertStaffshiftFromPlace($id);
						$tShiftlimits->insertShiftlimitsFromPlace($id);
					}
				}
			}

			$deleteid	= $this->getRequest()->deleteid;

			if(!empty($deleteid))
			{
				foreach($deleteid as $d)
				{
					// 2014/10/11 ishikawa
					// 場所マスタとシフトマスタから該当場所IDのデータを削除
					// 同様にスタッフシフトと受入数も削除する
					$tStaffshifts->deleteFromPlaceId($d);
					$tShiftlimits->deleteFromPlaceId($d);

					// シフトマスタ表のデータはスタッフシフト/受入数より後に削除すること
					$mShifts->deleteFromPlaceId($d);
					$mPlaces->deleteFromId($d);
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

		echo json_encode(array('success' => 1));
		exit;
	}

	// 文書種類設定画面
	public function dockindAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '文書種類設定');

		$mDockinds = new Class_Model_MDockinds();

		$max_id = $mDockinds->selectAll('id desc');
		$this->view->assign('nextid', $max_id[0]->id + 1);

		if (APPLICATION_TYPE != 'twc')
		{
			/* 全文書取得：非表示の場所も含む */
			$dockinds = $mDockinds->selectAll('order_num');
			$this->view->assign('dockinds', $dockinds);

			// 削除可能である旨を示すフラグを場所毎に設定する
			// ※該当場所IDでの予約が存在しなければ削除可能
			$delete_flg = array();
			foreach($dockinds as $v)
			{
				$delete_flg[$v->id] = $mDockinds->getReservedDockindId($v->id);
			}
			$this->view->assign('flg', $delete_flg);
		}
		else
		{
			/* 津田塾大では文書にシフト種別が紐づく */
			$mShiftclasses = new Class_Model_MShiftclasses();
			$shiftclasses = $mShiftclasses->selectAll('order_num');
			$this->view->assign('shiftclasses', $shiftclasses);

			// 文書を格納したオブジェクトをシフト種別毎の配列に割り当てる
			$places = array();
			for($i = 1; $i <= count($shiftclasses); $i++)
			{
				$dockinds[$i] = $mDockinds->selectAllFromShiftclassId($i);
			}
			$this->view->assign('dockinds', $dockinds);

			// 削除可能である旨を示すフラグを場所毎に設定する
			// ※該当場所IDでの予約が存在しなければ削除可能
			$delete_flg = array();
			$dockind_all = $mDockinds->selectAll();
			foreach($dockind_all as $v)
			{
				$delete_flg[$v->id] = $mDockinds->getReservedDockindId($v->id);
			}
			$this->view->assign('flg', $delete_flg);

			$this->_helper->viewRenderer->setNoRender();
			$html = $this->view->render('admin/dockind.twc.tpl');
			$this->getResponse()->setBody($html);
		}
	}

	// 文書種類設定処理
	public function updatedockindAction()
	{
		$mDockinds = new Class_Model_MDockinds();
		$mShifts = new Class_Model_MShifts();
		$tStaffshifts = new Class_Model_TStaffshifts();
		$tShiftlimits = new Class_Model_TShiftlimits();

		$translate = Zend_Registry::get('Zend_Translate');

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$display_flg		= $this->getRequest()->checkbox;
			$document_category	= $this->getRequest()->document_category;
			$clipped_form		= $this->getRequest()->clipped_form;
			$order_num			= $this->getRequest()->order_num;

			// 登録・更新処理
			foreach($document_category as $key => $value)
			{
				// パラメータチェック
				$result = array('error' => '');

				if (empty($value) || (APPLICATION_TYPE == 'twc' && empty($clipped_form[$key])))
					$result['error'] .= $translate->_("文書種類の名称を入力してください") . "\n";

				if ($result['error'] != '')
				{
					$db->rollback();
					echo json_encode($result);
					exit;
				}

				if(!empty($display_flg[$key]))
					$d_flg = 0;
				else
					$d_flg = 1;


				if (APPLICATION_TYPE != 'twc')
				{
					$params = array(
						'document_category'	=> $value,
						'display_flg'		=> $d_flg,
						'order_num'			=> $order_num[$key],
					);
				}
				else
				{
					$params = array(
						'document_category'	=> $value,
						'clipped_form'		=> $clipped_form[$key],
						'display_flg'		=> $d_flg,
						'order_num'			=> $order_num[$key],
					);
				}

				if(count($mDockinds->selectFromId($key)) > 0)
				{
					$mDockinds->updateFromId($key, $params);
				}
				else
				{
					$id = $mDockinds->insert($params);

					// 2014/10/11 ishikawa
					// 場所新規挿入の場合はシフトマスタを更新する
					// 関大：スタッフのシフトと受入数は、挿入された場所IDでも入力されていたと見なす
					// 津大：まだ
					$mShifts->insertShiftFromDockind($id);
					$tStaffshifts->insertStaffshiftFromDockind($id);
					$tShiftlimits->insertShiftlimitsFromDockind($id);
				}
			}

			$deleteid	= $this->getRequest()->deleteid;

			if(!empty($deleteid))
			{
				foreach($deleteid as $d)
				{
					// 2014/10/11 ishikawa
					// 文書マスタとシフトマスタから該当場所IDのデータを削除
					// 同様にスタッフシフトと受入数も削除する
					$tStaffshifts->deleteFromDockindId($d);
					$tShiftlimits->deleteFromDockindId($d);

					// シフトマスタ表のデータはスタッフシフト/受入数より後に削除すること
					$mShifts->deleteFromDockindId($d);
					$mDockinds->deleteFromId($d);
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

		echo json_encode(array('success' => 1));
		exit;
	}

	// 利用規約設定画面
	public function agreementAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '利用規約設定');

		AdminController::getagreementAction();
	}

	// 利用規約設定処理
	public function updateagreementAction()
	{
		$mSettings = new Class_Model_MSettings();

		$translate = Zend_Registry::get('Zend_Translate');

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$id = $this->getRequest()->id;
			$content = $this->getRequest()->content;

			// パラメータチェック
			$result = array('error' => '');

			if (empty($content))
				$result['error'] .= $translate->_("利用規約を入力してください") . "\n";

			if ($result['error'] != '')
			{
				$db->rollback();
				echo json_encode($result);
				exit;
			}

			$params = array(
					'name'		=> 'agreement',
					'content'	=> $content
			);

			if(!empty($id))
				$mSettings->updateFromId($id, $params);
			else
				$mSettings->insert($params);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('success' => 1));
		exit;
	}

	// ユーザー登録画面
	public function makeuseridAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', 'ユーザー登録');

		$mPermissions = new Class_Model_MPermissions();
		$permissions = $mPermissions->selectAllForSearch();
		$this->view->assign('permissions', $permissions);

	}

	// ユーザーID DB登録処理
	public function insertuseridAction()
	{
		$mMembers = new Class_Model_MMembers();
		$tMemAttr = new Class_Model_TMemberAttribute();

		$translate = Zend_Registry::get('Zend_Translate');

		$result = array('error' => '');

		$roles = $this->getRequest()->roles;

		if(empty($roles))
		{
			$result['error'] .= $translate->_("権限は最低一つのチェックが必要です") . "\n";

			echo json_encode($result);		// 権限にチェックがなければ相談種別を判断する必要はない
			exit;
		}

		$param_roles = join(",", $roles);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{

			// 登録・更新処理 m_members
			$params = array(
				'id'					=> $this->getRequest()->member_id,
				'insrtflg'				=> 1,
				'deletflg'				=> 0,
				'name_kana'				=> $this->getRequest()->member_name_kana,
				'sex'					=> $this->getRequest()->sex,
				'email'					=> $this->getRequest()->mail_add,
				'student_id'			=> $this->getRequest()->student_id,
				'staff_no'				=> $this->getRequest()->staff_no,
				'name_jp'				=> $this->getRequest()->member_name_jp,
				'original_user_flg'		=> 0,
			);
			$mMembers->insert_lastupdate($params);

			// 登録・更新処理 t_member_attribute
			$params_attr= array(
				'id'					=> $this->getRequest()->member_id,
				'password'				=> md5($this->getRequest()->member_pw),
				'roles'					=> $param_roles,
				'display_flg'			=> 1,
			);
			$tMemAttr->insert_lastupdate($params_attr);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('success' => 1));
		exit;
	}

	// CSVファイル読み込み
	public function uploadcsvAction()
	{
// student_id student_id_jpは同じものを入れる
		$mMembers = new Class_Model_MMembers();
		$tMemAttr = new Class_Model_TMemberAttribute();

		$translate = Zend_Registry::get('Zend_Translate');

		$result = array('error' => '');

		// csvファイル読み込み
		if ($_FILES['file']['size'] == 0) {
			echo 'Error! - 指定したファイルが見あたりません';
			exit(1);
		}
		$source_file = $_FILES['file']['tmp_name'];       //アップされたCSVファイル

		$file = new SplFileObject($source_file);
		$file->setFlags(SplFileObject::READ_CSV);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// いったんすべて削除
			$mMembers->deleteAll();
			$tMemAttr->deleteAll();

			$format_err=0;
			$line_v=0;
			foreach ($file as $line) {
				$line_v++;

				// 	１行目は項目なので無視
				if ( $line_v==1 ) continue;

				//ファイルフォーマットチェック
				$count_v=count($line);

				if ( !(is_null($line[0])) && count($line) < 9 ) {
					$format_err=1;
					break;
				}

				if ( !(is_null($line[0])) ) {
// 					echo '<hr>';
// 					echo 'user-id :' ;echo "$line[0]";echo '<br>';
// 					echo 'passwd  :' ;echo "$line[1]";echo '<br>';
// 					$str = mb_convert_encoding($line[2], "utf8", "SJIS");
// 					echo '名前         : ' ;echo "$str";echo '<br>';
// 					$str = mb_convert_encoding($line[3], "utf8", "SJIS");
// 					echo 'ナマエ         :' ;echo "$str";echo '<br>';
// 					echo '性別          :' ;echo "$line[4]";echo '<br>';
// 					echo '権限          :' ;echo "$line[5]";echo '<br>';
// 					echo 'email   :' ;echo "$line[6]";echo '<br>';
// 					$str = mb_convert_encoding($line[7], "utf8", "SJIS");
// 					echo '学籍番号  :' ;echo "$str";echo '<br>';
// 					$str = mb_convert_encoding($line[8], "utf8", "SJIS");
// 					echo '職員番号  :' ;echo "$str";echo '<br>';

					// 登録・更新処理 m_members
					$params = array(
							'id'					=> $line[0],
							'insrtflg'				=> 1,
							'deletflg'				=> 0,
							'name_kana'				=> mb_convert_encoding($line[3], "utf8", "SJIS"),
							'sex'					=> $line[4],
							'email'					=> $line[6],
							'student_id'			=> mb_convert_encoding($line[7], "utf8", "SJIS"),
							'staff_no'				=> mb_convert_encoding($line[8], "utf8", "SJIS"),
							'name_jp'				=> mb_convert_encoding($line[2], "utf8", "SJIS"),
							'student_id_jp'			=> mb_convert_encoding($line[7], "utf8", "SJIS"),
							'original_user_flg'		=> 0,
					);
					$mMembers->insert_lastupdate($params);

					// 登録・更新処理 t_member_attribute
					$params_attr= array(
							'id'					=> $line[0],
							'password'				=> md5($line[1]),
							'roles'					=> $line[5],
							'display_flg'			=> 1,
					);
					$tMemAttr->insert_lastupdate($params_attr);


				}	// is_null

			} //foreach

			if ( $format_err!=0 ) {
				echo json_encode(array('error' => 'CSV format error! '. "line($line_v)"));
				exit;
			}

			//デフォルト管理者登録
			// 登録・更新処理 m_members
			$params = array(
					'id'					=> 'admin',
					'insrtflg'				=> 1,
					'deletflg'				=> 0,
					'name_kana'				=> 'カンリシャ',
					'sex'					=> 1,
					'email'					=> '',
					'student_id'			=> '',
					'staff_no'				=> '',
					'name_jp'				=> '管理者',
					'student_id_jp'			=> '',
					'original_user_flg'		=> 0,
			);
			$mMembers->insert_lastupdate($params);

			// 登録・更新処理 t_member_attribute
			$params_attr= array(
					'id'					=> 'admin',
					'password'				=> md5('admin'),
					'roles'					=> 'Administrator',
					'display_flg'			=> 1,
			);
			$tMemAttr->insert_lastupdate($params_attr);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
//			echo json_encode(array('error' => 'Line 1:データが重複しています。'));
			exit;
		}

 		echo json_encode(array('success' => 1));

		exit;

// 		$this->view->assign('title', 'ライティングラボ');
// 		$this->view->assign('subtitle', 'ユーザー登録');

// 		$mPermissions = new Class_Model_MPermissions();
// 		$permissions = $mPermissions->selectAllForSearch();
// 		$this->view->assign('permissions', $permissions);

// 		$this->_helper->viewRenderer->setNoRender();
// 		$html = $this->view->render('admin/makeuserid.tpl');
// 		$this->getResponse()->setBody($html);

	}

	// CSVファイル読み込み
	public function downloadcsvAction()
	{
// 		$mMembers = new Class_Model_MMembers();
// 		$tMemAttr = new Class_Model_TMemberAttribute();

// 		$data_member = $mMembers->selectAllUser();
// 		$data_attribute = $tMemAttr->selectAllUser();

		$csv_data ='ユーザーID,パスワード,名前,名前(カタカナ),性別,権限,メールアドレス,学生番号,職員番号'."\n";
		$csv_data.='Sample001,passwd1,名前サンプル１,サンプルナマエ１,1,Student,sample1@mail,Student001,'."\n";
		$csv_data.='Sample002,passwd2,名前サンプル２,サンプルナマエ２,1,Administrator,sample2@mail,,Staff001'."\n";

// 		for ( $i = 0 ; $i < count ( $data_member ) ; $i ++ ) {
// 			if ( $data_member[$i]['id'] === 'admin') continue;
// //			$csv_data.= $data_member[$i]['id'].','.$data_attribute[$i]['password'].','.$data_member[$i]['name_jp'].','.$data_member[$i]['name_kana']
// 			$csv_data.= $data_member[$i]['id'].','."passwd".','.$data_member[$i]['name_jp'].','.$data_member[$i]['name_kana']
// 			.','.$data_member[$i]['sex'].','.'"'.$data_attribute[$i]['roles'].'"'.','.$data_member[$i]['email']
// 			.','.$data_member[$i]['student_id'].','.$data_member[$i]['staff_no']."\n";
// 		}

		//文字化けを防ぐ
		$csv_data = mb_convert_encoding ( $csv_data , "sjis-win" , 'utf-8' );

		//出力ファイル名の作成
//		$csv_file = "csv_". date ( "Ymd" ) .'.csv';
//		$csv_file = "UserID_". date ( "Ymd" ) .'.csv';
		$csv_file = "Sample_csv".'.csv';

		//MIMEタイプの設定
		header("Content-Type: application/octet-stream");
		//名前を付けて保存のダイアログボックスのファイル名の初期値
		header("Content-Disposition: attachment; filename={$csv_file}");

		// データの出力
		echo($csv_data);

		exit;
	}

	/********************************
	 *								*
	 *		旧システム管理画面		*
	 *						 		*
	 ********************************/

	public function userAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', 'ユーザー権限設定');

		$mPermissions = new Class_Model_MPermissions();
		$permissions = $mPermissions->selectAllForSearch();
		$this->view->assign('permissions', $permissions);

		if (APPLICATION_TYPE == 'twc')
		{
			$mLShiftclasses = new Class_Model_MLShiftclasses();
			$shiftclasses = $mLShiftclasses->selectAll();
			$this->view->assign('shiftclasses', $shiftclasses);

			$this->_helper->viewRenderer->setNoRender();
			$html = $this->view->render('admin/user.twc.tpl');
			$this->getResponse()->setBody($html);
		}
	}

	public function searchAction()
	{
		$roles = $this->getRequest()->roles;
		$userid = $this->getRequest()->userid;
		$name = $this->getRequest()->name;
		$faculty = $this->getRequest()->faculty;
		$department = $this->getRequest()->department;
		$shiftclass = $this->getRequest()->shiftclass;

		$curpage = $this->getRequest()->curpage;
		$limit = $this->getRequest()->limit;

		$mMembers = new Class_Model_MMembers();

		$result = $mMembers->getSearchedUser($roles, $userid, $name, $faculty, $department, $curpage, $limit);
		$count = $mMembers->getSearchedUserCount($roles, $userid, $name, $faculty, $department);

		$result_array = $result->toArray();
		$count = $count->toArray();

		echo json_encode(array('result' => $result_array, 'count' => $count[0]['count']));
		exit;
	}

	public function updateroleAction()
	{
		$userid			= $this->getRequest()->userid;
		$roles			= $this->getRequest()->update_roles;

		$translate = Zend_Registry::get('Zend_Translate');

		$result = array('error' => '');

		if(empty($roles))
		{
			$result['error'] .= $translate->_("権限は最低一つのチェックが必要です") . "\n";

			echo json_encode($result);		// 権限にチェックがなければ相談種別を判断する必要はない
			exit;
		}

		$param_roles = join(",", $roles);

		$param = array('roles' => $param_roles);

		$tMemAttr = new Class_Model_TMemberAttribute();

		$tMemAttr->getAdapter()->beginTransaction();
		try
		{
			$tMemAttr->updateFromId($userid, $param);

			$tMemAttr->getAdapter()->commit();
		}
		catch (Exception $e)
		{
			$tMemAttr->getAdapter()->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('success' => 1));
		exit;
	}

	// 2014/09/30 ishikawa
	// CSVファイルからデータベースを更新する
	// メッセージ表示処理などはまだ
	public function updatecsvAction()
	{
		/* HTML特殊文字をエスケープする関数 */
		function h($str)
		{
			return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
		}
		$isUpload = !empty($_FILES['csvfile']);

		// ファイルアップロード
		if ($isUpload)
		{
			try
			{
				$tmp_name = $_FILES['csvfile']['tmp_name'];
				$detect_order = 'ASCII,JIS,UTF-8,CP51932,SJIS-win';
				setlocale(LC_ALL, 'ja_JP.UTF-8');

				// 文字コードを変換してファイルを置換
				$buffer = file_get_contents($tmp_name);
				if (!$encoding = mb_detect_encoding($buffer, $detect_order, true))
				{
					// 文字コードの自動判定に失敗
					unset($buffer);
					throw new RuntimeException('Character set detection failed');
				}
				file_put_contents($tmp_name, mb_convert_encoding($buffer, 'UTF-8', $encoding));
				unset($buffer);

				/* データベースに接続 */
				$mMembers = new Class_Model_MMembers();

				/* トランザクション処理 */
				$mMembers->getAdapter()->beginTransaction();
				try
				{
					$fp = fopen($tmp_name, 'rb');
					$index = 0;
					while ($row = fgetcsv($fp))
					{
						if ($row === array(null) || $index === 0)
						{
							$index++;
							// 空行と先頭はスキップ
							continue;
						}
						// 						if (count($row) !== 4)
						// 						{
						// 							// カラム数が異なる無効なフォーマット
						// 							throw new RuntimeException('Invalid column detected');
						// 						}

						$param = array(
								'id'				=> $row[0],
								'account'			=> $row[1],
								'password'			=> $row[2],
								'roles'				=> $row[3],
								'name'				=> $row[4],
								'name_jp'			=> $row[5],
								'name_kana'			=> $row[6],
								'm_faculty_id'		=> $row[7],
								'm_department_id'	=> $row[8],
								'usr_kbn'			=> $row[9],
								'age'				=> $row[10],
								'sex'				=> $row[11],
								'tenure_kbn'		=> $row[12],
								'enrollment_kbn'	=> $row[13],
								'student_id'		=> $row[14],
								'languages'			=> $row[15],
								'original_user_flg'	=> $row[16],
								'display_flg'		=> $row[17],
								'createdate'		=> $row[18],
								'creator'			=> $row[19],
								'lastupdate'		=> $row[20],
								'lastupdater'		=> $row[21],
								'email'				=> $row[22],
								'employee_id'		=> $row[23],
								'entrance_year'		=> $row[24],
								'academic_year'		=> $row[25],
						);

						foreach($param as $key => $value)
						if(empty($value)) $param[$key] = null;

						$executed = $mMembers->insert($param);
						$index++;
					}

					if (!feof($fp))
					{
						// ファイルポインタが終端に達していなければエラー
						throw new RuntimeException('CSV parsing error');
					}
					fclose($fp);
					$mMembers->getAdapter()->commit();

				}
				catch (Exception $e)
				{
					fclose($fp);
					$mMembers->getAdapter()->rollBack();
					throw $e;
				}

				/* 結果メッセージをセット */
				if (isset($executed)) {
					// 1回以上実行された
					$msg = array('green', 'succeeded');
				} else {
					// 1回も実行されなかった
					$msg = array('black', 'failed');
				}

			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => $e->getMessage()));
				exit;
			}

		}

		echo json_encode(array('success' => 1));
		exit;
	}


	// 閉室日設定
	public function closuredateAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '閉室日設定');

		//年度プルダウン設定
		$mTerms = new Class_Model_MTerms();
		$terms = $mTerms->selectAll('startdate asc');

		$ymd = date('Y-m-d', strtotime(Zend_Registry::get('nowdate')));
		$term = $mTerms->getTermFromDate($ymd);
		$termid = $term->id;

		$this->view->assign('terms', $terms);
		$this->view->assign('termid', $termid);

		$mPlaces = new Class_Model_MPlaces();
		$places = $mPlaces->selectAllDisplay();
		$this->view->assign('places', $places);
		$this->view->assign('placeid', $places[0]->id);
	}

	// 閉室設定取得(ajax: 戻り値は JSONの配列)
	public function getclosuredateAction()
	{
		// 学期
		$termid = $this->getRequest()->termid;

		// 場所
		$placeid = $this->getRequest()->placeid;

		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->selectFromId($termid);

		$tClosuredates = new Class_Model_TClosuredates();
		$close = $tClosuredates->selectFromTermIdAndPlaceId($termid, $placeid);

		echo json_encode(array('term' => $term->toArray(), 'close' => $close->toArray()));
		exit;
	}

	// 閉室設定取得(ajax: 戻り値は JSONの配列)
	public function setclosuredatesAction()
	{
		// 学期
		$termid = $this->getRequest()->vterm;

		// 場所
		$placeid = $this->getRequest()->vplace;

		// 日付
		$closuredates = $this->getRequest()->closuredates;

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$tClosuredates = new Class_Model_TClosuredates();
			$tClosuredates->deleteFromTermIdAndPlaceId($termid, $placeid);

			// 全データが空の場合は挿入処理をスキップする
			if(!empty($closuredates))
				$tClosuredates->insert($closuredates, $placeid, $this->member->id);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => $e->getMessage()));
			exit;
		}

		echo json_encode(array('success' => 1));
		exit;
	}
}
