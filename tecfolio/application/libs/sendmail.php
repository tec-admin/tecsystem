<?php

/////////////////////////////
//
// メール送信ライブラリ
//

require_once('qdsmtp.php');

class Sendmail extends QdSmtp
{
	const NOTICE_NEW = 1;
	const NOTICE_UPDATE = 2;
	const NOTICE_STAFFCOMMENT = 3;
	const NOTICE_STUDENTCOMMENT = 4;
	const NOTICE_CANCEL = 5;


	// SMTP送信オブジェクト作成
	public $_param = array(
		'host'		=>'example.ac.jp',
		'port'		=> 25 ,
		'from'		=>'tecsystem@example.ac.jp',
		'protocol'	=>'SMTP',
	);

	public function __construct()
	{
		parent::__construct($this->_param);
	}

	// 送信者設定
	public function from($from)
	{
		$this->_param['from'] = $from;
		$this->server($this->_param);
	}

	// 簡易メール送信
	public function easyMail($to, $subject, $body)
	{
		$subject	= mb_encode_mimeheader($subject, 'ISO-2022-JP-MS');
		$body		= mb_convert_encoding($body, 'ISO-2022-JP-MS');

		mb_internal_encoding("UTF-8");
		return $this->mail(
			$to,
			$subject,
			$body,
			"Content-Type: text/plain; charset=\"ISO-2022-JP\";\n"     //ヘッダはISO-2022-JPを指定する！
		);
	}

	// 通知メール
	public static function noticeMail($obj, $type, $reserveid)
	{
		$tReserves = new Class_Model_TReserves();
		$reserve = $tReserves->selectFromId($reserveid);

		$mShifts = new Class_Model_MShifts();
		$shift = $mShifts->selectFromId($reserve->m_shift_id);
		
		$joined_shift = $mShifts->selectJoinedRows($reserve->id);

		$last_day = date("Y-m-d H:i:s", strtotime($reserve->reservationdate . ' -1 day'));	// 予約日の一日前

		// 当日出勤予定のスタッフをシフトから取得
		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->getTermFromDate($reserve->reservationdate);

		$mPlaces = new Class_Model_MPlaces();
		$place = $mPlaces->selectFromId($shift->m_place_id);

		$dow = date("w", strtotime($reserve->reservationdate));
		$tStaffshifts = new Class_Model_TStaffshifts();
		$staffshifts = $tStaffshifts->selectFromTodayStaff($term->id, $place->m_campus_id, $shift->dayno, $dow);

		// 同一スタッフの重複を削除
		$staffs = array();
		$staffshifts = $staffshifts->toArray();
		foreach ($staffshifts as $staffshift)
			$staffs[$staffshift['m_member_id']] = $staffshift;

		// 運営管理者
		$mMembers = new Class_Model_MMembers();
		$admins = $mMembers->selectFromRoles('Administrator');
		
		// スタッフからのコメントを取得
		$tLeadings = new Class_Model_TLeadings();
		$leading_comment = $tLeadings->selectFromReserveId($reserveid);
		
		// 学生からのコメントを取得
		$tReserveComments = new Class_Model_TReserveComments();
		$reserve_comment = $tReserveComments->selectFromReserveId($reserveid);

		$smtp = new Sendmail();

		// メール通知
		switch ($type)
		{
		case	Sendmail::NOTICE_NEW:		// 新規
			
			$subject = Sendmail::addSubject($obj, $reserve->reservationdate, $shift);
			$body	= Sendmail::addBody($obj, $reserveid, $joined_shift);

			// 予約者（学生）
			if (!empty($reserve->reserver_email))
				$smtp->easyMail($reserve->reserver_email, $subject, $body);

			// リマインダー設定
			Sendmail::setReminder($obj, $reserveid, 0, $reserve->reserver_id, $last_day, $subject, $body);
			break;

		case	Sendmail::NOTICE_STAFFCOMMENT:		// スタッフコメント
			$subject = Sendmail::staffcommentSubject($obj, $reserve->reserver_name_jp, $reserve->reservationdate, $shift);
			$body	= Sendmail::staffcommentBody($obj, $reserveid);
			
			if (APPLICATION_TYPE == 'kwl')
			{
				// 予約者（学生）
				if (!empty($reserve->reserver_email))
					$smtp->easyMail($reserve->reserver_email, $subject, $body);
			}
			break;

		case	Sendmail::NOTICE_STUDENTCOMMENT:	// 学生コメント
			$subject = Sendmail::studentcommentSubject($obj, $reserve->reserver_name_jp, $reserve->reservationdate, $shift);
			$body	= Sendmail::studentcommentBody($obj, $reserveid);
			
			if (APPLICATION_TYPE == 'kwl')
			{
				// 担当者（スタッフ）
				if (!empty($reserve->charge_email))
					$smtp->easyMail($reserve->charge_email, $subject, $body);
			}
			
			break;


		case	Sendmail::NOTICE_CANCEL:	// キャンセル
			$subject = Sendmail::cancelSubject($obj, $reserve->reservationdate, $shift);
			$body	= Sendmail::cancelBody($obj, $joined_shift);

			// 予約者（学生）
			if (!empty($reserve->reserver_email))
				$smtp->easyMail($reserve->reserver_email, $subject, $body);
			
			break;
		}
		return true;
	}
	
	
	// 通知メール
	public static function noticeUpdateMail($obj, $type, $reserveid, $reserved)
	{
		$tReserves = new Class_Model_TReserves();
		$reserve = $tReserves->selectFromId($reserveid);

		$mShifts = new Class_Model_MShifts();
		$shift = $mShifts->selectFromId($reserve->m_shift_id);
		
		$joined_shift = $mShifts->selectJoinedRows($reserve->id);

		$last_day = date("Y-m-d H:i:s", strtotime($reserve->reservationdate . ' -1 day'));	// 予約日の一日前

		// 当日出勤予定のスタッフをシフトから取得
		$mTerms = new Class_Model_MTerms();
		$term = $mTerms->getTermFromDate($reserve->reservationdate);

		$mPlaces = new Class_Model_MPlaces();
		$place = $mPlaces->selectFromId($shift->m_place_id);

		$dow = date("w", strtotime($reserve->reservationdate));
		$tStaffshifts = new Class_Model_TStaffshifts();
		$staffshifts = $tStaffshifts->selectFromTodayStaff($term->id, $place->m_campus_id, $shift->dayno, $dow);

		// 同一スタッフの重複を削除
		$staffs = array();
		$staffshifts = $staffshifts->toArray();
		foreach ($staffshifts as $staffshift)
			$staffs[$staffshift['m_member_id']] = $staffshift;

		// 運営管理者
		$mMembers = new Class_Model_MMembers();
		$admins = $mMembers->selectFromRoles('Administrator');

		$smtp = new Sendmail();
		
		$subject = Sendmail::updateSubject($obj, $reserved, $shift);
		$body	= Sendmail::updateBody($obj, $reserveid, $joined_shift, $reserved);

		// 予約者（学生）
		if (!empty($reserve->reserver_email))
			$smtp->easyMail($reserve->reserver_email, $subject, $body);
		
		// リマインダー設定
		Sendmail::setReminder($obj, $reserveid, 0, $reserve->reserver_id, $last_day, $subject, $body);
	}

	// リマインダー追加／更新 処理
	public static function setReminder($obj, $t_reserve_id, $m_member_id_from, $m_member_id_to, $senddatetime, $subject, $body)
	{
		$params = array(
			't_reserve_id'		=> $t_reserve_id,
			'm_member_id_from'	=> $m_member_id_from,
			'm_member_id_to'	=> $m_member_id_to,
			'senddatetime'		=> $senddatetime,
			'subject'			=> $subject,
			'body'				=> $body,
			'status'			=> 1,	// 未送信
		);

		$tReminders	= new Class_Model_TReminders();
		$reminder = $tReminders->selectFromReserveId($t_reserve_id);

		$tReminders->getAdapter()->beginTransaction();
		try
		{
			if (empty($reminder))
				$reminderid = $tReminders->insert($params);
			else
				$reminderid = $tReminders->updateFromId($reminder->id, $params);

			$tReminders->getAdapter()->commit();
		}
		catch (Exception $e)
		{
			$tReminders->getAdapter()->rollback();
			die($e->getMessage()); 
		}

		return $reminderid;
	}

	// 曜日 日本語変換
	public static function dowjp($date)
	{
		$w = date('w', strtotime($date));	// 曜日取得
		$dow = array(0 => '日', 1 => '月', 2 => '火', 3 => '水', 4 => '木', 5 => '金', 6 => '土');
		return $dow[$w];
	}
	// 日本語書式へ変換
	public static function datejp($date)
	{
		$ds = date('Y年m月d日', strtotime($date));
		return sprintf("%s(%s)", $ds, Sendmail::dowjp($date));
	}

	// 時間を表示用へ変換
	public static function timejp($time)
	{
		return date('H:i', strtotime($time));	// 秒は削る
	}

	// 予約詳細URL
	public static function reserveUrl($obj, $reserveid)
	{
		return $obj->serverurl . $obj->baseurl . '/labo/editreserve/reserveid/' . $reserveid;
	}
	
	// 予約詳細URL(スタッフ)
	public static function reserveUrlStaff($obj, $reserveid)
	{
		return $obj->serverurl . $obj->baseurl . '/staff/advice/reserveid/' . $reserveid . '/';
	}
	
	// 予約詳細URL(管理者)
	public static function reserveUrlAdmin($obj, $reserveid)
	{
		return $obj->serverurl . $obj->baseurl . '/admin/reservestatus/reserveid/' . $reserveid . '/';
	}

	// 履歴URL
	public static function historyUrl($obj, $reserveid)
	{
		return $obj->serverurl . $obj->baseurl . '/labo/history/reserveid/' . $reserveid;
	}

	// 指導履歴URL
	public static function adviceUrl($obj, $reserveid)
	{
		return $obj->serverurl . $obj->baseurl . '/staff/advice/reserveid/' . $reserveid;
	}
	
	// 本文（共通）
	public static function getCommonBody($obj, $joined_shift)
	{
		$msg_body  = '学生名：' . Zend_Auth::getInstance()->getIdentity()->name_jp . 'さん' . "\r\n\r\n";
		
		$msg_body .= '相談日時：' . Sendmail::datejp($joined_shift->t_reserves_reservationdate) . ' ' . Sendmail::timejp($joined_shift->m_timetables_starttime) . '-' . Sendmail::timejp($joined_shift->m_timetables_endtime) . "\r\n";
		$msg_body .= '文書の種類：' . $joined_shift->m_dockinds_document_category . "\r\n";
		$msg_body .= '相談場所：' . $joined_shift->m_places_consul_place . "\r\n";
		
		if($joined_shift->m_subjects_class_subject != "")
			$msg_body .= "\r\n" . '授業科目：' . $joined_shift->m_subjects_class_subject . "\r\n";
			
		if(Sendmail::datejp($joined_shift->t_reserves_submitdate) != "1970年01月01日(木)"){
			$msg_body .= '提出日：' . Sendmail::datejp($joined_shift->t_reserves_submitdate) . "\r\n";
		}
		else if(Sendmail::datejp($joined_shift->t_reserves_submitdate) == "1970年01月01日(木)")
		{
			$msg_body .= '提出日：登録なし' . "\r\n";
		}
		
		$i = $joined_shift->t_reserves_progress;
		
		switch($i){
			case 10:
				$msg_body .= '進行状況：★　　　　　まだ書いていない' . "\r\n";
				break;
			case 20:
				$msg_body .= '進行状況：★★　　　　ちょっと書いた' . "\r\n";
				break;
			case 30:
				$msg_body .= '進行状況：★★★　　　半分くらい書いた' . "\r\n";
				break;
			case 40:
				$msg_body .= '進行状況：★★★★　　ひと通り書いた' . "\r\n";
				break;
			case 50:
				$msg_body .= '進行状況：★★★★★　ほぼ完成した' . "\r\n";
				break;
			deafult:
		}
		
		return $msg_body;
	}
	
	// 本文（変更点追加）
	public static function getUpdateBody($obj, $joined_shift, $reserved)
	{
		$msg_body  = '学生名：' . Zend_Auth::getInstance()->getIdentity()->name_jp . 'さん' . "\r\n\r\n";
		
		
		
		$msg_body .= '相談日時：';
		if( ($joined_shift->m_timetables_starttime != $reserved->m_timetables_starttime) ||
			( ($joined_shift->m_timetables_starttime == $reserved->m_timetables_starttime) && ($joined_shift->t_reserves_reservationdate != $reserved->reservationdate) ))
		{
		
			$msg_body .= "\r\n";
			$msg_body .= '　【変更前】：' . Sendmail::datejp($reserved->reservationdate) . ' ' . Sendmail::timejp($reserved->m_timetables_starttime) . '-' . Sendmail::timejp($reserved->m_timetables_endtime) . "\r\n";
			$msg_body .= '　【変更後】：';
			
		}
		$msg_body .= Sendmail::datejp($joined_shift->t_reserves_reservationdate) . ' ' . Sendmail::timejp($joined_shift->m_timetables_starttime) . '-' . Sendmail::timejp($joined_shift->m_timetables_endtime) . "\r\n";
		
		
		
		$msg_body .= '文書の種類：';
		if($joined_shift->m_dockinds_document_category != $reserved->m_dockinds_document_category)
		{
		
			$msg_body .= "\r\n";
			$msg_body .= '　【変更前】：' . $reserved->m_dockinds_document_category . "\r\n";
			$msg_body .= '　【変更後】：';
			
		}
		$msg_body .= $joined_shift->m_dockinds_document_category . "\r\n";
		
		
		
		$msg_body .= '相談場所：';
		if($joined_shift->m_places_consul_place != $reserved->m_places_consul_place)
		{
		
			$msg_body .= "\r\n";
			$msg_body .= '　【変更前】：' . $reserved->m_places_consul_place . "\r\n";
			$msg_body .= '　【変更後】：';
			
		}
		$msg_body .= $joined_shift->m_places_consul_place . "\r\n";
		
		
		
		if($joined_shift->m_subjects_class_subject != "")
		{
		
			$msg_body .= "\r\n" . '授業科目：';
			
			if($joined_shift->m_subjects_class_subject != $reserved->m_subjects_class_subject){
				$msg_body .= "\r\n";
				$msg_body .= '　【変更前】：' . $reserved->m_subjects_class_subject . "\r\n";
				$msg_body .= '　【変更後】：';
			}
			
			$msg_body .= $joined_shift->m_subjects_class_subject . "\r\n";
		}
		
		
		
		if(Sendmail::datejp($joined_shift->t_reserves_submitdate) != "1970年01月01日(木)")
		{
		
			$msg_body .= '提出日：';
			
			if($joined_shift->t_reserves_submitdate != $reserved->submitdate){
				$msg_body .= "\r\n";
				// 2014/09/17 ishikawa
				// 変更前日付がNULLの場合は空とする
				if(Sendmail::datejp($reserved->submitdate) != "1970年01月01日(木)")
					$msg_body .= '　【変更前】：' . Sendmail::datejp($reserved->submitdate) . "\r\n";
				else
					$msg_body .= '　【変更前】：' . "\r\n";
				$msg_body .= '　【変更後】：';
			}
			
			$msg_body .= Sendmail::datejp($joined_shift->t_reserves_submitdate) . "\r\n";
		}
		
		
		
		if($joined_shift->t_reserves_progress != "")
		{
			$msg_body .= '進行状況：';
			if($joined_shift->t_reserves_progress != $reserved->progress)
			{
				$msg_body .= "\r\n";
				$msg_body .= '　【変更前】：' . Sendmail::getProgressString($reserved->progress) . "\r\n";
				$msg_body .= '　【変更後】：';
			}
			
			$msg_body .= Sendmail::getProgressString($joined_shift->t_reserves_progress) . "\r\n";
		}
		
		
		return $msg_body;
	}
	
	// 進行状況用文字列を返す
	public static function getProgressString($progress)
	{
		$str = '';
		
		switch($progress){
			case 10:
				$str = '★　　　　　まだ書いていない';
				break;
			case 20:
				$str = '★★　　　　ちょっと書いた';
				break;
			case 30:
				$str = '★★★　　　半分くらい書いた';
				break;
			case 40:
				$str = '★★★★　　ひと通り書いた';
				break;
			case 50:
				$str = '★★★★★　ほぼ完成した';
				break;
			deafult:
		}
		
		return $str;
	}
	
	// 追加 件名
	public static function addSubject($obj, $reservationdate, $shift)
	{
		return Zend_Auth::getInstance()->getIdentity()->name_jp . 'さんの予約が' . Sendmail::datejp($reservationdate) . ' ' . Sendmail::timejp($shift->m_timetables_starttime) . '-' . Sendmail::timejp($shift->m_timetables_endtime) . 'に登録されました';
	}

	// 追加 本文
	public static function addBody($obj, $reserveid, $joined_shift)
	{
		$msg_body = Sendmail::getCommonBody($obj, $joined_shift);
		
		$msg_body .= "\r\n";
		
		$msg_body .= '詳細URL：' . Sendmail::reserveUrl($obj, $reserveid) . "\r\n";
		
		return $msg_body;
	}
	
	// 追加 本文(スタッフ)
	public static function addBodyStaff($obj, $reserveid, $joined_shift)
	{
		$msg_body = Sendmail::getCommonBody($obj, $joined_shift);
		
		$msg_body .= "\r\n";
		
		$msg_body .= '詳細URL：' . Sendmail::reserveUrlStaff($obj, $reserveid) . "\r\n";
		
		return $msg_body;
	}

	// 追加 本文(管理者)
	public static function addBodyAdmin($obj, $reserveid, $joined_shift)
	{
		$msg_body = Sendmail::getCommonBody($obj, $joined_shift);
		
		$msg_body .= "\r\n";
		
		$msg_body .= '詳細URL：' . Sendmail::reserveUrlAdmin($obj, $reserveid) . "\r\n";
		
		return $msg_body;
	}

	/// 更新 件名
	public static function updateSubject($obj, $reserved)
	{
		return Zend_Auth::getInstance()->getIdentity()->name_jp . 'さんの' . Sendmail::datejp($reserved->reservationdate) . ' ' . Sendmail::timejp($reserved->m_timetables_starttime) . '-' . Sendmail::timejp($reserved->m_timetables_endtime) . 'の予約が変更されました';
	}

	// 更新 本文
	public static function updateBody($obj, $reserveid, $joined_shift, $reserved)
	{
		$msg_body = Sendmail::getUpdateBody($obj, $joined_shift, $reserved);
		
		$msg_body .= "\r\n";
		
		$msg_body .= '詳細URL：' . Sendmail::reserveUrl($obj, $reserveid) . "\r\n";
		
		return $msg_body;
	}
	
	// 更新 本文
	public static function updateBodyStaff($obj, $reserveid, $joined_shift, $reserved)
	{
		$msg_body = Sendmail::getUpdateBody($obj, $joined_shift, $reserved);
	
		$msg_body .= "\r\n";
	
		$msg_body .= '詳細URL：' . Sendmail::reserveUrlStaff($obj, $reserveid) . "\r\n";
	
		return $msg_body;
	}
	
	// 更新 本文
	public static function updateBodyAdmin($obj, $reserveid, $joined_shift, $reserved)
	{
		$msg_body = Sendmail::getUpdateBody($obj, $joined_shift, $reserved);
	
		$msg_body .= "\r\n";
	
		$msg_body .= '詳細URL：' . Sendmail::reserveUrlAdmin($obj, $reserveid) . "\r\n";
	
		return $msg_body;
	}

	// キャンセル 件名
	public static function cancelSubject($obj, $reservationdate, $shift)
	{
		return Zend_Auth::getInstance()->getIdentity()->name_jp . 'さんの' . Sendmail::datejp($reservationdate) . ' ' . Sendmail::timejp($shift->m_timetables_starttime) . '-' . Sendmail::timejp($shift->m_timetables_endtime) . 'の予約がキャンセルされました';
	}

	// キャンセル 本文
	public static function cancelBody($obj, $joined_shift)
	{
		return Sendmail::getCommonBody($obj, $joined_shift);
	}
	
	// スタッフコメント 件名
	public static function staffcommentSubject($obj, $reservername, $reservationdate, $shift)
	{
		return $reservername . 'さんの' . Sendmail::datejp($reservationdate) . ' ' . Sendmail::timejp($shift->m_timetables_starttime) . '-' . Sendmail::timejp($shift->m_timetables_endtime) . 'の相談にスタッフからコメントが届きました';
	}

	// スタッフコメント 本文
	public static function staffcommentBody($obj, $reserveid)
	{
		$msg_body = 'コメントの確認は以下のURLをクリックしてください' . "\r\n";
		
		$msg_body .= "\r\n";
		
		$msg_body .= '詳細URL：' . Sendmail::historyUrl($obj, $reserveid) . "\r\n";
		
		return $msg_body;	// 履歴詳細
	}
	
	// スタッフコメント 本文（管理者）
	public static function staffcommentBodyAdmin($obj, $reserveid, $leading_comment)
	{
		$msg_body = 'スタッフからのコメント：' . "\r\n";
		$msg_body .= $leading_comment->leading_comment . "\r\n";
	
		$msg_body .= "\r\n";
	
		return $msg_body;
	}

	// 学生コメント 件名
	public static function studentcommentSubject($obj, $reservername, $reservationdate, $shift)
	{
		return $reservername . 'さんからコメントの返信が届きました';
	}

	// 学生コメント 本文
	public static function studentcommentBody($obj, $reserveid)
	{
		$msg_body = 'コメントの確認は以下のURLをクリックしてください' . "\r\n";
		
		$msg_body .= "\r\n";
		
		$msg_body .= '詳細URL：' . Sendmail::adviceUrl($obj, $reserveid) . "\r\n";
		
		return $msg_body;	// 指導履歴
	}
	
	// 学生コメント 本文（管理者）
	public static function studentcommentBodyAdmin($obj, $reserveid, $leading_comment, $reserve_comment)
	{
		$msg_body = 'スタッフからのコメント：' . "\r\n";
		$msg_body .= $leading_comment->leading_comment . "\r\n";
		
		$msg_body .= "\r\n";
		
		$msg_body .= '学生からのコメント：' . "\r\n";
		$msg_body .= $reserve_comment->reservecomment . "\r\n";
	
		$msg_body .= "\r\n";
	
		return $msg_body;
	}
}

?>
