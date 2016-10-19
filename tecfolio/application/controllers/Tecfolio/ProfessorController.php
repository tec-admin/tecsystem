<?php
require_once(dirname(__FILE__) . '/SharedController.class.php');

class Tecfolio_ProfessorController extends Tecfolio_SharedController
{
	public function preDispatch()
	{
		parent::preDispatch();
		
		$this->_helper->AclCheck('Class', 'View');
		$this->id = $this->getRequest()->id;
		
		// XMLHttpRequestの場合は処理しない
		$headers = apache_request_headers();
		$isXMLHttp = in_array('XMLHttpRequest', $headers);
		if($isXMLHttp) return;
		
		$sess = new Zend_Session_Namespace('Nfwa4HZGbsK8K45e');
		$nendo	= $sess->currentNendo;
		$gakki	= $sess->currentGakki;
		
		$this->view->assign('currentNendo', $nendo);
		$this->view->assign('currentGakki', $gakki);
		
		$mNendo = new Class_Model_MNendo();
		$nendo_row = $mNendo->selectRow();
		$this->view->assign('nendo', $nendo_row->current_nendo);
		
		// 選択された年度・学期の授業科目を取得する
		$mSubjectsReg	= new Class_Model_Tecfolio_MSubjectsRegistered();
		if(!empty($nendo))
			$subjects	= $mSubjectsReg->selectFromStaffnoAndNendoAndGakki($this->member->staff_no, $nendo, $gakki);
		else
			$subjects	= $mSubjectsReg->selectFromStaffnoAndNendoAndGakki($this->member->staff_no, $nendo_row->current_nendo, null);
		
		$this->view->assign('subjects', $subjects);
	
		// どの種別のIDが選択されているか(Myテーマ/メンター/学内施設/授業科目)
		if(!empty($this->id))
		{
			$prefix = $this->getPrefix($this->id);
	
			switch($prefix)
			{
				case parent::PREFIX_MYTHEME:
					$selected = $this->processMytheme($this->id);
					break;
				case parent::PREFIX_MENTOR:
					$selected = $this->processMentor($this->id);
					break;
				case parent::PREFIX_SUBJECT:
					$selected = $this->processSubject($this->id);
					break;
			}
	
			if(empty($selected))
			{
				$this->view->assign('error', 'error');
			}
		}
		
		// 新着情報
		$tMentors = new Class_Model_Tecfolio_TMentors();
		$mentors = $tMentors->selectFromStudentNews(Zend_Auth::getInstance()->getIdentity()->id, 100, 0);
		
		$new_info_flg = 0;
		foreach($mentors as $mentor)
		{
			if(!empty($mentor->t_mentors_lastupdate) && $mentor->MyID == $mentor->t_mentors_m_member_id && $mentor->t_mentors_agreement_flag == 0)
				$new_info_flg++;
		}
		
		$this->view->assign('news', $mentors);
		$this->view->assign('new_info_flg', $new_info_flg);
	}
	
	// 授業科目が選択されている場合
	public function processSubject($id)
	{
		// アクセス許可のある科目であるかをチェックする
		$mSubjectsReg	= new Class_Model_Tecfolio_MSubjectsRegistered();
		$res = $mSubjectsReg->selectFromIdAndStaffno($id, $this->member->staff_no);
		if(empty($res))	return;
		
		$this->subjectid	= $id;
		$this->view->assign('subjectid', $id);
		$this->view->assign('selected', $res);
		
		$members = $mSubjectsReg->selectGakseFromId($id);
		$this->view->assign('class_members', $members);
		
		// 授業科目の一覧を、選択された授業科目の年度に応じて上書きする
		$sess = new Zend_Session_Namespace('Nfwa4HZGbsK8K45e');
		$sess->currentNendo	= null;
		$sess->currentGakki	= null;
		$this->view->assign('currentNendo', $res->jyu_nendo);
		$this->view->assign('currentGakki', 0);
		
		$subjects	= $mSubjectsReg->selectFromStaffnoAndNendoAndGakki($this->member->staff_no, $res->jyu_nendo, null);
		$this->view->assign('subjects', $subjects);
		
		$subj = $mSubjectsReg->selectFromId($id);
		$this->view->assign('publicity', $subj->publicity);
	
		return $id;
	}
	
	// 授業科目設定
	public function settingAction()
	{
		$this->view->assign('subtitle', '授業科目設定');
		
		$mSubjects = new Class_Model_Tecfolio_MSubjects();
		
		$available = $mSubjects->selectAvailableFromStaffno($this->member->staff_no);
		$this->view->assign('available', $available);
		
		$selected_all = $mSubjects->selectSelectedAllFromStaffno($this->member->staff_no);
		$this->view->assign('selected_all', $selected_all);
		
		$selected_year = $mSubjects->selectSelectedThisYearFromStaffno($this->member->staff_no);
		$this->view->assign('selected_year', $selected_year);
		
		$html = $this->view->render('tecfolio/professor/setting.tpl');
		$this->getResponse()->setBody($html);
	}
	
	// 授業科目登録
	public function registersubjectAction()
	{
		$staff_no		= $this->member->staff_no;
	
		$jyu_knr_no		= $this->getRequest()->jyu_knr_no;
		$jwaricd		= $this->getRequest()->jwaricd;
	
		$mSubjectsReg	= new Class_Model_Tecfolio_MSubjectsRegistered();
		$mapped_cd		= $mSubjectsReg->selectKyoincdFromStaffno($staff_no);
		
		$tRisyuReg	= new Class_Model_Tecfolio_TCourseRosterRegistered();
		$tJikanReg	= new Class_Model_Tecfolio_TJyugyoJikanwariRegistered();
		
		$tMentors = new Class_Model_Tecfolio_TMentors();
		$mentor_id = $this->getRandomNum();
		$prefix = self::PREFIX_MENTOR . '_ID';
		
		// 追加：サンプルルーブリックの配置(マッピングデータのみ)
		$tRubricMap = new Class_Model_Tecfolio_TRubricMap();
	
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// 授業科目登録
			$mSubjectsReg->insertFromMSubjects($mapped_cd['ky_kyoincd'], $staff_no, $jyu_knr_no, $jwaricd);
			
			// 登録者=教師をその科目のメンターとして登録
			$tMentors->insertFromMSubjects($mapped_cd['ky_kyoincd'], $jyu_knr_no, $jwaricd, $prefix, $mentor_id, $this->member->id, $this->member->name_jp, $this->member->syzkcd_c);
			
			// 履修者と時間割登録
			$tRisyuReg->insertFromTCourseRoster($jyu_knr_no, $jwaricd);
			$tJikanReg->insertFromTJyugyoJikanwari($jyu_knr_no);
			
			// ルーブリック登録
			$tRubricMap->insertFromMSubjects($mapped_cd['ky_kyoincd'], $jyu_knr_no, $jwaricd);
			
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
	
	// 授業科目に応じて、履修者一覧を取得
	public function getuserAction()
	{
		$staff_no		= $this->member->staff_no;
	
		$nendo			= $this->getRequest()->nendo;
		$jwaricd		= $this->getRequest()->jwaricd;
	
		$tRisyuReg		= new Class_Model_Tecfolio_TCourseRosterRegistered();
	
		$unregistered	= $tRisyuReg->selectUnregisteredUsers($nendo, $jwaricd);
		$registered		= $tRisyuReg->selectRegisteredUsers($nendo, $jwaricd);
	
		echo json_encode(array('unregistered' => $unregistered, 'registered' => $registered));
		exit;
	}
	
	// 履修者登録
	public function registeruserAction()
	{
		$nendo			= $this->getRequest()->nendo;
		$jwaricd		= $this->getRequest()->jwaricd;
	
		$join_gakse_id	= $this->getRequest()->join_gakse_id;
		$leave_gakse_id	= $this->getRequest()->leave_gakse_id;
	
		$tRisyuReg	= new Class_Model_Tecfolio_TCourseRosterRegistered();
	
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			if(!empty($join_gakse_id))
				$tRisyuReg->insertSelectedUsers($join_gakse_id, $nendo, $jwaricd);
			if(!empty($leave_gakse_id))
				$tRisyuReg->deleteSelectedUsers($leave_gakse_id, $nendo, $jwaricd);
	
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
	
	// ポートフォリオ画面：追加処理
	public function portfolioAction()
	{
		if(!empty($this->subjectid))
		{
			$mSubjectsReg	= new Class_Model_Tecfolio_MSubjectsRegistered();
			$members = $mSubjectsReg->selectGaksePortfolioFromId($this->subjectid);
			$this->view->assign('portfolio_members', $members);
		}
		
		parent::portfolioAction();
	}
	
}