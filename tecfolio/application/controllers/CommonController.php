<?php
require_once('BaseController.class.php');

// カレンダーライブラリ
require_once('HOLIDAY/holiday.php');
require_once('Calendar/Month/Weekdays.php');
require_once('Calendar/Week.php');

// メール送信ライブラリ
require_once('sendmail.php');

class CommonController extends BaseController
{
	public function init()
	{
		$translate = Zend_Registry::get('Zend_Translate');
		
		$this->progresssal_edit = array(
				50 => $translate->_("★★★★★　ほぼ完成した"),
				40 => $translate->_("★★★★　　ひと通り書いた"),
				30 => $translate->_("★★★　　　半分くらい書いた"),
				20 => $translate->_("★★　　　　ちょっと書いた"),
				10 => $translate->_("★　　　　　まだ書いていない"),
		);
	
		$this->progresssal = array(
				50 => $translate->_("★★★★★　ほぼ完成した"),
				40 => $translate->_("★★★★　　ひと通り書いた"),
				30 => $translate->_("★★★　　　半分くらい書いた"),
				20 => $translate->_("★★　　　　ちょっと書いた"),
				10 => $translate->_("★　　　　　まだ書いていない"),
				0  => "",
		);
		
		parent::init();
	}
	
	public function errorAction()
	{
		$translate = Zend_Registry::get('Zend_Translate');
		$this->view->assign('subtitle', 'エラー');
		$this->view->message = $translate->_("予約が存在しません。既に削除された可能性があります");
	}
	
	// おしらせ（詳細）
	public function informationAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', 'お知らせ詳細');
	
		$tInfomations = new Class_Model_TInfomations();
	
		$infomationid = $this->getRequest()->informationid;
	
		if (empty($infomationid))
		{	// 空だった場合、最新のおしらせがあればそのIDを使う
			$infos = $tInfomations->selectLimit(1, array('createdate DESC'));
			if (count($infos) != 0)
				$infomationid = $infos[0]->id;
		}
	
		// 個別表示
		if (!empty($infomationid))
		{
			$infomation = $tInfomations->selectFromId($infomationid);
			$this->view->assign('infomation', $infomation);
			$this->view->assign('infomationid', $infomationid);
		}
	
		$page = $this->getRequest()->page;
		if (!empty($page))
			$this->view->assign('page', $page);
		
		// それ以前、それ以降を選ばせないために、
		// 最小の日付と最大の日付を取得する
		$mTerms = new Class_Model_MTerms();
		$dates = $mTerms->getMinandMaxDate();
		
		$this->view->assign('mindate', $dates->mindate);
		$this->view->assign('maxdate', $dates->maxdate);
	}
	
	// おしらせ一覧(ajax: 戻り値は JSONの配列)
	public function getinfomationlistAction()
	{
		$page = $this->getRequest()->page;
		if (empty($page))
			$page = 1;
	
		$tInfomations = new Class_Model_TInfomations();
		$select = $tInfomations->GetSelectFromAll(0, array('createdate DESC'));
	
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
	
	// 全指導履歴画面
	public function allhistoryAction()
	{
		$this->view->assign('title', 'ライティングラボ');
		$this->view->assign('subtitle', '全指導履歴');
	
		$templates = '';
	
		//相談場所
		$mPlaces = new Class_Model_MPlaces();
		$places = $mPlaces->selectAll('order_num asc');
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
		$terms = $mTerms->selectAll('startdate asc');
	
		$termid = $this->getRequest()->termid;
		if(empty($termid))
		{
			//$termid = $terms[0]->id;
			$term = $mTerms->getTermFromDate($ymd);
			$termid = $term->id;
		}
	
		$this->view->assign('termid', $termid);
		$this->view->assign('terms', $terms);
	
		// シフト取得
		// ※ 同じキャンパス内の最初の場所の最初の文書タイプのみ取得する
		$mDockinds = new Class_Model_MDockinds();
		$dockinds	= $mDockinds->selectAll();
		$this->view->assign('dockinds', $dockinds);
	
		//シフト作成
		$mShifts = new Class_Model_MShifts();
		$shifts = $mShifts->selectShiftGroup($termid, $dockinds[0]->id, $places[0]->id);
		$this->view->assign('shifts', $shifts);
	}
	
	// 履歴一覧(ajax: 戻り値は JSONの配列)
	public function getallhistorylistAction()
	{
		$placeid = $this->getRequest()->placeid;
		$termid = $this->getRequest()->termid;
	
		$tReserves = new Class_Model_TReserves();
	
		//履歴取得
		$selects = $tReserves->GetSelectFromInputJQ($placeid, $termid, 0, array('reserves.reservationdate DESC', 'shifts.dayno ASC'));
	
		// 配列へ変換し、同時に予定と履歴を分ける
		// ※ 既に予約日時でソートされているので、現在時刻を境に分割されるだけ
		$schedule = array();
		$history = array();
	
		foreach ($selects as $select)
		{
			if (strtotime($select['reservationdate'] . ' ' . $select['m_timetables_starttime']) < strtotime(Zend_Registry::get('nowdatetime')))
				$history[] = $select->toArray();
			else
				$schedule[] = $select->toArray();
		}
	
		$data = array(
				'history' => $history,
		);
	
		echo json_encode($data);
		exit;
	}
	
	// 履歴1件取得(ajax: 戻り値は JSONの配列)
	public function getallhistoryAction()
	{
		$reserveid = $this->getRequest()->reserveid;
		$tReserves = new Class_Model_TReserves();
		$reserve = $tReserves->selectFromId($reserveid);
		
		// 添付ファイルは複数項目の可能性があるため別途取得する
		$tReserveFile = new Class_Model_TReserveFiles();
		$reservefiles = $tReserveFile->selectFromReserveId($reserveid);
		
		$files = array();
		foreach($reservefiles as $reservefile)
			$files[$reservefile->id] = $reservefile->t_files_name;
		
		if(empty($files))
			$files[] = '';
		
		$history = array(
				't_reserve_id'			=> $reserveid,//予約番号
				'reservationdate'=> $reserve -> reservationdate,//年月日
				'shifts_dayno' => $reserve->m_shifts_dayno,//シフトNo.
				'timetables_starttime' => $reserve->m_timetables_starttime,//開始時間
				'timetables_endtime' => $reserve->m_timetables_endtime,//終了時間
				'reserver_student_id'=> $reserve->student_id,//学籍番号
				'reserver_name_jp'=> $reserve -> name_jp,//氏名
				'm_dockinds_document_category'=> $reserve -> m_dockinds_document_category,//文書の種類
				'm_places_consul_place'=> $reserve -> m_places_consul_place,//相談場所
				'progress'=> $reserve -> progress,//進行状況
				'submitdate'=> $reserve -> submitdate,//提出日
				'm_subjects_class_subject'=> $reserve -> class_subject,//授業科目
				'charge_name_jp'=> $reserve -> t_leadings_name_jp,//相談担当スタッフ
				'kyoin'=> $reserve->sekiji_top_kyoinmei,//科目担当教員
				't_files_name'=> $files,//$reserve -> t_files_name,//添付ファイル
				//''=> $reserve -> ,//提出ファイル
				'question'=> $reserve -> question,//相談したいこと
				't_leadings_counsel'	=> $reserve -> t_leadings_counsel,			//相談内容
				't_leadings_teaching'	=> $reserve -> t_leadings_teaching,			//指導内容
				't_leadings_remark'		=> $reserve -> t_leadings_remark,			//所感
				't_leadings_summary'	=> $reserve -> t_leadings_summary,			//備考
				't_leadings_comment'	=> $reserve -> t_leadings_leading_comment,	//指導コメント
		);
	
		echo json_encode($history);
		exit;
	}
}