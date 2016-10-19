<?php
require_once(dirname(__FILE__) . '/SharedController.class.php');

class Tecfolio_StudentController extends Tecfolio_SharedController
{
	public function preDispatch()
	{
		parent::preDispatch();
		
		$this->_helper->AclCheck('Reserve', 'View');
		$this->id = $this->getRequest()->id;
		
		// XMLHttpRequestの場合は処理しない
		$headers = apache_request_headers();
		if(in_array('XMLHttpRequest', $headers)) return;
		
		$mMytheme	= new Class_Model_Tecfolio_MMythemes();
		$check = $mMytheme->selectFromIdAndMemberId(parent::PREFIX_LABO . '_' . $this->member->id, Zend_Auth::getInstance()->getIdentity()->id);
		// ライティングラボ用のデータが存在しなければ作成する
		if(empty($check))
			Tecfolio_SharedController::insertlaboAction();
		
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
			$subjects		= $mSubjectsReg->selectFromStudentIdAndNendoAndGakki($this->member->student_id, $nendo, $gakki);
		else
			$subjects		= $mSubjectsReg->selectFromStudentIdAndNendoAndGakki($this->member->student_id, $nendo_row->current_nendo, null);
		
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
				case parent::PREFIX_LABO:
					$selected = $this->processLabo($this->id);
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
	}
	
	// 授業科目が選択されている場合
	public function processSubject($id)
	{
		// アクセス許可のある科目であるかをチェックする
		$mSubjectsReg	= new Class_Model_Tecfolio_MSubjectsRegistered();
		$res = $mSubjectsReg->selectFromIdAndStudentId($id, $this->member->student_id);
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
		
		$subjects	= $mSubjectsReg->selectFromStudentIdAndNendoAndGakki($this->member->student_id, $res->jyu_nendo, null);
		$this->view->assign('subjects', $subjects);
	
		return $id;
	}
}