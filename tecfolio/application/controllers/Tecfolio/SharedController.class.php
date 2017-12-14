<?php
require_once(dirname(__FILE__) . '/../BaseController.class.php');

class Tecfolio_SharedController extends BaseController
{
	const PREFIX_MYTHEME		= 'THEME';
	const PREFIX_MENTOR			= 'MNT';
	const PREFIX_LABO			= 'LABO';
	const PREFIX_CONTENTS		= 'CONT';
	const PREFIX_PORTFOLIO		= 'PF';
	const PREFIX_PF_CONTENTS	= 'PFC';
	const PREFIX_RUBRIC			= 'RUB';
	const PREFIX_SUBJECT		= 'SUBJ';
	const PREFIX_CHAT_SUBJ_CONT	= 'CSC';

	public function init()
	{
		$this->license_list = array(
				1 => 'すべての権利を放棄',
				2 => '帰属表示',
				3 => '帰属表示－継承',
				4 => '帰属表示－改変禁止',
		);

		$this->license_convert = array(
				'すべての権利を放棄'	=> 1,
				'帰属表示' 				=> 2,
				'帰属表示－継承' 		=> 3,
				'帰属表示－改変禁止' 	=> 4,
		);

		parent::init();
	}

	public function preDispatch()
	{
		parent::preDispatch();

		// v2では関数名に対応した.tplを自動で読み込むことはしない
		$this->_helper->viewRenderer->setNoRender();

		$this->controllerName = str_replace('_', '/', $this->controllerName);

		// XMLHttpRequestの場合は処理しない
		$headers = apache_request_headers();
		if(in_array('XMLHttpRequest', $headers)) return;

		$this->view->assign('controllerName', $this->controllerName);

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();
		// Myテーマはページ読み込み後、初回のみこれをソースとする
		$mythemes = $mMytheme->selectFromMemberId(Zend_Auth::getInstance()->getIdentity()->id);
		$this->view->assign('mythemes', $mythemes);

		$disabled_mythemes = $mMytheme->selectDisabledFromMemberId(Zend_Auth::getInstance()->getIdentity()->id);
		$this->view->assign('disabled_mythemes', $disabled_mythemes);
	}

	// IDの接頭辞を取得
	public function getPrefix($id)
	{
		return substr($id, 0, strpos($this->id, '_'));
	}

	// Myテーマが選択されている場合(学生・教員共通)
	public function processMytheme($id)
	{
		$selected = array();

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();
		$check = $mMytheme->selectFromIdAndMemberId($id, Zend_Auth::getInstance()->getIdentity()->id);

		if(!empty($check))
		{
			$this->view->assign('mythemeid', $id);
			$selected = $mMytheme->selectFromId($id);
			$this->view->assign('selected', $selected);
		}

		return $selected;
	}

	// メンターが選択されている場合(学生・教員共通)
	public function processMentor($id)
	{
		$selected = array();

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();
		$tMentor	= new Class_Model_Tecfolio_TMentors();
		$check = $tMentor->selectFromIdAndMemberId($id, Zend_Auth::getInstance()->getIdentity()->id);

		if(!empty($check) && $this->actionName == 'portfolio')
		{
			$this->view->assign('mentorid', $id);
			$selected = $mMytheme->selectMentorFromMentorId($id);
			$this->view->assign('selected', $selected);
		}

		return $selected;
	}

	// 学内施設が選択されている場合
	// 20150917時点では教員にこの画面はない
	public function processLabo($id)
	{
		$selected = array();

		$m_member_id = substr($id, strpos($id, '_') + 1);	// LABO_以降は自身のm_member_idである必要がある
		if(Zend_Auth::getInstance()->getIdentity()->id != $m_member_id)	return;

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();
		$this->view->assign('laboid', $id);
		$selected = $mMytheme->selectFromId($id);
		$this->view->assign('selected', $selected);

		return $selected;
	}

	// ライティングラボを新規Myテーマとして挿入
	public function insertlaboAction()
	{
		$m_member_id = Zend_Auth::getInstance()->getIdentity()->id;

		$name = 'ライティングラボ';

		$mMembers	= new Class_Model_Tecfolio_MMembers();
		$member	 	= $mMembers->selectFromId($m_member_id);

		$id = self::PREFIX_LABO . '_' . $m_member_id;

		$params = array(
				'id'				=> $id,
				'm_member_id'		=> $m_member_id,
				'name_jp'			=> $member->name_jp,
				'syzkcd_c'			=> $member->syzkcd_c,
				'name'				=> $name,
				'order_num'			=> 1
		);

		// 追加：サンプルルーブリックの配置(マッピングデータのみ)
		$params_rubric = array(
				'parent_id'		=> $id,
				'm_rubric_id'	=> '1',
				'original_flag'	=> '0'
		);

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();
		$tRubricMap	= new Class_Model_Tecfolio_TRubricMap();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$mMytheme->insert($params);
			$tRubricMap->insert($params_rubric);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			return false;
		}

		return true;
	}

	// 新規Myテーマ(ajax: 戻り値は JSONの配列)
	public function insertmythemeAction()
	{
		$member = Zend_Auth::getInstance()->getIdentity();
		$m_member_id = $member->id;

		$name = $this->getRequest()->newtheme;

		// パラメータチェック
		$result = array('error' => '');

		$translate = Zend_Registry::get('Zend_Translate');

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();
		$duplicate = $mMytheme->selectFromMemberIdAndName($m_member_id, $name);

		if(count($duplicate) > 0)
			$result['error'] .= $translate->_("同名のテーマが既に登録されています") . "\n";

		if(empty($name))
			$result['error'] .= $translate->_("テーマ名称を入力してください") . "\n";

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		$mMembers	= new Class_Model_Tecfolio_MMembers();
		$member	 	= $mMembers->selectFromId($m_member_id);
		$row		= $mMytheme->selectMaxOrderFromMemberId($m_member_id);
		if(count($row) > 0)
			$order_num	= $row->order_num + 1;
		else
			$order_num = 1;

		$id = $this->getRandomId(self::PREFIX_MYTHEME);

		$params = array(
				'id'				=> $id,
				'm_member_id'		=> $m_member_id,
				'name_jp'			=> $member->name_jp,
				'syzkcd_c'			=> $member->syzkcd_c,
				'name'				=> $name,
				'order_num'			=> $order_num
		);

		// 追加：サンプルルーブリックの配置(マッピングデータのみ)
		$params_rubric = array(
				'parent_id'		=> $id,
				'm_rubric_id'	=> '1',
				'original_flag'	=> '0'
		);

		$tRubricMap	= new Class_Model_Tecfolio_TRubricMap();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$mMytheme->insert($params);
			$tRubricMap->insert($params_rubric);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id, 'name' => $name));	// 成功時はidとnameを返す
		exit;
	}

	// Myテーマ
	public function getmythemeAction()
	{
		$mMytheme	= new Class_Model_Tecfolio_MMythemes();
		$mythemes = $mMytheme->selectFromMemberId(Zend_Auth::getInstance()->getIdentity()->id);
		$arr = array();
		foreach($mythemes as $mytheme)
			$arr[] = $mytheme->toArray();

		$dis_mythemes = $mMytheme->selectDisabledFromMemberId(Zend_Auth::getInstance()->getIdentity()->id);
		$dis_arr = array();
		foreach($dis_mythemes as $mytheme)
			$dis_arr[] = $mytheme->toArray();

		echo json_encode(array('mytheme' => $arr, 'disabled' => $dis_arr));
		exit;
	}

	// 授業科目
	public function getsubjectAction()
	{
		$nendo = $this->getRequest()->selectYear;
		$gakki = $this->getRequest()->selectTerm;

		$sess = new Zend_Session_Namespace('Nfwa4HZGbsK8K45e');
		$sess->setExpirationSeconds(600, 'currentNendo');
		$sess->setExpirationSeconds(600, 'currentGakki');
		$sess->currentNendo		= $nendo;
		$sess->currentGakki		= $gakki;

		$mSubjectsReg	= new Class_Model_Tecfolio_MSubjectsRegistered();
		if($this->controllerName == 'tecfolio/professor')
			$subjects	= $mSubjectsReg->selectFromStaffnoAndNendoAndGakki($this->member->staff_no, $nendo, $gakki);
		else
			$subjects	= $mSubjectsReg->selectFromStudentIdAndNendoAndGakki($this->member->student_id, $nendo, $gakki);

		$subj_arr = array();
		foreach($subjects as $subject)
			$subj_arr[] = $subject->toArray();

		echo json_encode(array('subject' => $subj_arr));
		exit;
	}

	// トップ
	public function indexAction()
	{
		$this->view->assign('subtitle', 'Test');

		$html = $this->view->render('tecfolio/common/index.tpl');
		$this->getResponse()->setBody($html);
	}

	// ファイル置場
	public function fileAction()
	{
		$this->view->assign('subtitle', 'ファイル置場');

		// 授業科目選択時は別テンプレート
		if(empty($this->subjectid))
			$html = $this->view->render('tecfolio/common/file.tpl');
		else
			$html = $this->view->render('tecfolio/common/file.subject.tpl');
		$this->getResponse()->setBody($html);
	}

	// ファイル置場：メインペイン描画用データの取得(ajax: 戻り値は JSONの配列)
	public function getcontentsAction()
	{
		$id 	= $this->getRequest()->id;
		$order 	= $this->getRequest()->order;
		$asc 	= $this->getRequest()->asc;
		
		$act_name 	= $this->getRequest()->action_name;

		$tContents	= new Class_Model_Tecfolio_TContents();
		$tFiles		= new Class_Model_TFiles();

		// アクティブなコンテンツ
		$prefix = $this->getPrefix($id);

		// Myテーマ/ライティングラボ/授業科目では、それぞれデータ取得元が異なる
		// 2016.03.31 公開範囲設定を考慮した条件を追加
		if($prefix == self::PREFIX_LABO)
		{
			$contents	= $tFiles->selectFromLastupdaterId($this->member->id);
		}
		elseif($prefix == self::PREFIX_SUBJECT)
		{
			// $act_nameにより、ファイル置場では全データ、ポートフォリオでは参照可能なデータのみを返す
			$contents	= $tContents->selectQuotedSubjectFromMythemeId($id, $order, $asc, $this->member->id, $this->member->staff_no, $act_name);
		}
		else
		{
			$contents	= $tContents->selectQuotedFromMythemeId($id, $order, $asc);
		}

		$contents_arr = array();
		foreach($contents as $content)
		{
			$contents_arr[] = $content->toArray();
		}

		// ゴミ箱のコンテンツ
		$trashes	= $tContents->selectRemovedFromMythemeId($id);

		$trashes_arr = array();
		foreach($trashes as $trashe)
		{
			$trashes_arr[] = $trashe->toArray();
		}

		echo json_encode(array('contents' => $contents_arr, 'trashes' => $trashes_arr, 'id' => $id));
		exit;
	}

	// コンテンツ追加時、利用可能データの取得(ajax: 戻り値は JSONの配列)
	public function getavailablecontentsAction()
	{
		$id				= $this->getRequest()->id;
		$portfolio_id	= $this->getRequest()->t_portfolio_id;

		$tContent	= new Class_Model_Tecfolio_TContents();
		$tFiles		= new Class_Model_TFiles();

		// アクティブなコンテンツ
		$prefix = $this->getPrefix($id);

		// Myテーマ/ライティングラボ/授業科目では、それぞれデータ取得元が異なる
		if($prefix == self::PREFIX_LABO)
		{
			$contents	= $tFiles->selectAvailableFromMythemeId($id, $portfolio_id, $this->member->id);
		}
		elseif($prefix == self::PREFIX_SUBJECT)
		{
			$contents	= $tContent->selectAvailableFromMythemeIdForSubject($id, $portfolio_id, $this->member->id, $this->member->staff_no);
		}
		else
		{
			$contents	= $tContent->selectAvailableFromMythemeId($id, $portfolio_id);
		}

		$contents_arr = array();
		foreach($contents as $content)
		{
			$contents_arr[] = $content->toArray();
		}

		// ゴミ箱のコンテンツ(ダミーを返す)
		$trashes_arr = array();

		echo json_encode(array('contents' => $contents_arr, 'trashes' => $trashes_arr, 'id' => $id));
		exit;
	}

	// ファイルダウンロード
	public function downloadcontentAction()
	{
		if(empty($_SERVER["HTTP_REFERER"])) exit();
		
		$id = $this->getRequest()->id;
		if (isset($id) && $id > 0)
		{
			$db = Zend_Db_Table::getDefaultAdapter();	// DBアダプタ取得
			$stt = $db->query('SELECT data,type,name,filesize FROM t_content_files WHERE id= ?', array($id));

			//$stt->bindColumn('data', $data, Zend_Db::PARAM_STR);
			$stt->bindColumn('data', $data, Zend_Db::PARAM_LOB);
			$stt->bindColumn('type', $type, Zend_Db::PARAM_STR);
			$stt->bindColumn('name', $name, Zend_Db::PARAM_STR);
			$stt->bindColumn('filesize', $filesize, Zend_Db::PARAM_INT);

			if ($stt->fetch(Zend_Db::FETCH_BOUND))
			{
				header('Content-Type: ' . $type);
				header('Content-Disposition: attachment; filename="' . mb_convert_encoding($name, 'SJIS') . '"');
				header('Content-Length: ' . $filesize);

				fgets($data,2);
				$convdata = fgets($data);
				print(pack('H*', $convdata));

				// AWSのPHPver違いによるエラー？
				// 本番あるいは同verの検証環境に乗せて不具合があれば元の処理に戻す
				// ここから
// 				$contents = '';
// 				while (!feof($data))
// 				{
// 					$contents .= fread($data, 8192);
// 				}

// 				$bin = unpack("H*", $contents);
// 				foreach($bin as $b)
// 					print(pack('H*', $b));
				// ここまで

				//print($data);
			}
		}
		exit();
	}

	// Myテーマ更新(ajax: 戻り値は JSONの配列)
	public function updatemythemeAction()
	{
		$id		= $this->getRequest()->mytheme_edit_id;
		$name	= $this->getRequest()->edittheme;

		// パラメータチェック
		$result = array('error' => '');

		$translate = Zend_Registry::get('Zend_Translate');

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();
		$duplicate = $mMytheme->selectFromIdAndNameAndMemberId($id, $name, $this->member->id);

		if(count($duplicate) > 0)
			$result['error'] .= $translate->_("同名のテーマが既に登録されています") . "\n";

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		$params = array(
				'name'				=> $name
		);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$mMytheme->updateFromId($id, $params);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id, 'name' => $name));	// 成功時はidとnameを返す
		exit;
	}

	// Myテーマ削除(ajax: 戻り値は JSONの配列)
	public function deletemythemeAction()
	{
		$id		= $this->getRequest()->mytheme_delete_id;
		$name	= $this->getRequest()->mytheme_delete_name;

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$mMytheme->deleteFromId($id);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id, 'name' => $name));	// 成功時はidとnameを返す
		exit;
	}


	// Myテーマ入れ替え(ajax: 戻り値は JSONの配列)
	public function switchmythemeAction()
	{
		$id			= $this->getRequest()->id;
		$direction	= $this->getRequest()->direction;

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();
		$origin = $mMytheme->selectFromId($id);						// アクションが起こった行
		$target = $mMytheme->selectTargetFromIdAndMemberId($id, $this->member->id, $direction);	// 交換対象の行

		if(empty($origin) || empty($target))
		{
			echo json_encode(array('error' => 'error'));
			exit;
		}

		$origin_alt_param = array(
				'order_num' => $target->order_num
		);
		$target_alt_param = array(
				'order_num' => $origin->order_num
		);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$mMytheme->updateFromId($origin->id, $origin_alt_param);
			$mMytheme->updateFromId($target->id, $target_alt_param);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}


	// Myテーマ利用しない(ajax: 戻り値は JSONの配列)
	public function disablemythemeAction()
	{
		$id		= $this->getRequest()->id;

		$param = array(
			'disabled_flag' => '1'
		);

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$mMytheme->updateFromId($id, $param);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}


	// Myテーマ利用する(ajax: 戻り値は JSONの配列)
	public function enablemythemeAction()
	{
		$id		= $this->getRequest()->id;

		$param = array(
				'disabled_flag' => '0'
		);

		$mMytheme	= new Class_Model_Tecfolio_MMythemes();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$mMytheme->updateFromId($id, $param);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}
	
	// コンテンツアップロード(ローカルPC/Amazon/Cinii)共通関数
	// 授業科目選択時はpublicityを変更する
	public function insertSubjectContents($id, $call_insert)
	{
		if($this->getPrefix($id) == self::PREFIX_SUBJECT)
		{
			$mSubjects = new Class_Model_Tecfolio_MSubjectsRegistered();
			$subj = $mSubjects->selectFromId($id);
			
			return call_user_func($call_insert, $subj->publicity);
		}
		else
		{
			if(is_callable($call_insert))
			{
				return call_user_func($call_insert, 1);
			}
		}
	
		return false;
	}
	
	// 新規コンテンツ(ajax: 戻り値は JSONの配列)
	// ※複数ファイル選択
	public function insertcontentsAction()
	{
		$id = $this->getRequest()->id;
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$result = $this->insertSubjectContents($id, array($this, 'insertcontents'));

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => $e->getMessage()));
			return;
		}

		echo json_encode(array('id' => $result['m_mytheme_id']));	// 成功時はidを返す
		return;
	}

	public function insertcontents($publicity)
	{
		$m_mytheme_id		= $this->getRequest()->id;

		$t_content_id 		= array();		// 授業科目のみ利用

		$tFiles = new Class_Model_Tecfolio_TContentFiles();
		$tContents = new Class_Model_Tecfolio_TContents();

		foreach($_FILES['addbypc']["error"] as $key => $value)
		{
			if ($value != UPLOAD_ERR_OK)
			{
// 				BaseController::logWrite('ファイルのアップロードに失敗しました:' . $this->member->name_jp . ':' . $value);
				throw new Exception('ファイルのアップロードに失敗しました');
			}
// 			else
// 			{
// 				BaseController::logWrite($this->member->name_jp . ':' . $_FILES['addbypc']['name'][$key] . ':' . $_FILES['addbypc']['size'][$key]);
// 			}

			$t_file_id = $tFiles->insertFile(
				$_FILES['addbypc']['tmp_name'][$key],
				$_FILES['addbypc']['name'][$key],
				$_FILES['addbypc']['type'][$key],
				$_FILES['addbypc']['size'][$key]
			);

			$id = $this->getRandomId(self::PREFIX_CONTENTS);
			$t_content_id[] = $id;

			$params = array(
					'id' 					=> $id,
					'm_mytheme_id'			=> $m_mytheme_id,
					't_content_file_id'		=> $t_file_id,
					'poster_name'			=> $this->member->name_jp,
					'publicity'				=> $publicity
			);

			$tContents->insert($params);
		}

		return array('m_mytheme_id' => $m_mytheme_id, 't_content_id' => $t_content_id);
	}

	// 拡張子付きファイル名の後ろに文字列を付加する
	public function addStrToFileName($filename, $str)
	{
		// 最後に現れる拡張子ドット位置
		$pos = strrpos($filename, '.');

		// 指定位置に日時ベースの乱数追加
		$s = substr_replace($filename, $str, $pos) . substr($filename, $pos);	// ファイル名.拡張子→(ファイル名 + 文字列) + .拡張子

		return $s;
	}

	// ファイル名に使用できない文字を全角に置換する
	public function convertFileName($value)
	{
		$value = str_replace("\\", "￥", $value);
		$value = str_replace("/", "／", $value);
		$value = str_replace(":", "：", $value);
		$value = str_replace("*", "＊", $value);
		$value = str_replace("?", "？", $value);
		$value = str_replace("\"", "”", $value);
		$value = str_replace("<", "＜", $value);
		$value = str_replace(">", "＞", $value);
		$value = str_replace("|", "｜", $value);

		return $value;
	}

	// $flg = 0		選択されたコンテンツをごみ箱へ移動(ajax: 戻り値は JSONの配列)
	// $flg = 1		.zipダウンロード
	public function checkcontentsAction()
	{
		$t_content_id		= $this->getRequest()->removecheck;
		$flg				= $this->getRequest()->switch_flg;

		if($flg == 0)
		{
			$tContents = new Class_Model_Tecfolio_TContents();

			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			try
			{
				$params = array(
						'delete_flag'	=> '1'
				);

				foreach($t_content_id as $id)
				{
					$count = $tContents->updateNotPortfolioFromId($id, $params);

					if (empty($count))
					{
						$db->rollback();
						$translate = Zend_Registry::get('Zend_Translate');
						echo json_encode(array('error' => $translate->_("ポートフォリオで参照されているコンテンツをゴミ箱に移動することはできません")));
						exit;
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

			echo json_encode(array('id' => $t_content_id));	// 成功時はidを返す
			exit;
		}
		else
		{
			$id				= $this->getRequest()->id;				// MyテーマID
			$name			= $this->getRequest()->name;			// Myテーマ名

			$t_content_id	= $this->getRequest()->removecheck;		// t_contents/t_filesのID

			$titles			= $this->getRequest()->ref_title;
			$urls			= $this->getRequest()->ref_url;

			// Zipクラスロード
			$zip = new ZipArchive();

			// Zipファイル名
			$zipFileName = 'TECfolio_' . $name .'(資料)_' . Zend_Registry::get('nowdate') . '.zip';

			// Zipファイル一時保存ディレクトリ取得
			$zipFilePath = APPLICATION_PATH . '/log/';

			// Zipファイルオープン
			$result = $zip->open($zipFilePath.$zipFileName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
			if ($result !== true) {
				return false;
			}

			// blobで格納されたファイル情報をSQLで取得
			$tContentFiles	= new Class_Model_Tecfolio_TContentFiles();
			$tFiles			= new Class_Model_TFiles();

			$prefix = $this->getPrefix($id);

			// 作成したMyテーマとライティングラボではデータ取得元が異なる
			// ※ここでは参考文献の引用コンテンツは無視される(IDが存在しないため、取得されない)
			if($prefix != self::PREFIX_LABO)
				$data = $tContentFiles->selectFromMultipleId($t_content_id);
			else
				$data = $tFiles->selectFromMultipleId($t_content_id);

			// Zipファイルへのファイル追加
			foreach($data as $d)
			{
				// 同名ファイルを区別する
				$filename		= $this->addStrToFileName( mb_convert_encoding($d->name, 'SJIS-win', 'UTF-8'), '_' . date('YmdHis', strtotime($d->lastupdate)) );
				$accept_data	= '';

				fgets($d->data,2);
				$convdata = fgets($d->data);
				$accept_data = pack('H*', $convdata);

				// AWSのPHPver違いによるエラー？
				// 本番あるいは同verの検証環境に乗せて不具合があれば元の処理に戻す
				// ここから
// 				$contents = '';
// 				while (!feof($d->data))
// 				{
// 					$contents .= fread($d->data, 8192);
// 				}

// 				$bin = unpack("H*", $contents);
// 				foreach($bin as $b)
// 					$accept_data .= pack('H*', $b);
				// ここまで

				// 取得ファイルをZipに追加
				$zip->addFromString($filename, $accept_data);
			}

			// 参考文献の引用コンテンツを、インターネットショートカットとしてZipファイルに追加
			// ※こちらでは同名ファイルを区別しない
			foreach($t_content_id as $key => $value)
			{
				if(!empty($titles[$key]))
					$zip->addFromString(mb_convert_encoding($this->convertFileName($titles[$key]), 'SJIS-win', 'UTF-8') . '.url', "[InternetShortcut]\r\nURL=" . $urls[$key]);
			}
			// Zipファイルクローズ
			$zip->close();

			// ストリームに出力
			header('Content-Type: application/zip; name="' . $zipFileName . '"');
			header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
			header('Content-Length: '.filesize($zipFilePath.$zipFileName));
			echo file_get_contents($zipFilePath.$zipFileName);

			// 一時ファイルを削除
			unlink($zipFilePath.$zipFileName);
			exit;
		}
	}

	// removedflg=0ならゴミ箱から選択されたコンテンツを復帰、
	// removedflg=1ならゴミ箱から選択されたコンテンツを完全削除(ajax: 戻り値は JSONの配列)
	public function manipulateremovedAction()
	{
		$flg				= $this->getRequest()->removedflg;
		$t_content_id		= $this->getRequest()->removecheck;

		$tContents = new Class_Model_Tecfolio_TContents();
		$tContentFiles = new Class_Model_Tecfolio_TContentFiles();
		$tPortfolioContents = new Class_Model_Tecfolio_TPortfolioContents();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			if($flg != 1)
			{
				$params = array(
						'delete_flag'	=> '0'
				);

				foreach($t_content_id as $id)
				{
					$tContents->updateFromId($id, $params);
				}
			}
			else
			{
				foreach($t_content_id as $id)
				{
					$content = $tContents->selectFromId($id);

					$tContents->deleteFromId($id);
					$tPortfolioContents->deleteFromContentId($id);

					if(!empty($content->t_content_file_id))		// 参考文献の引用(Amazon/Cinii)でない場合、ファイル削除
						$tContentFiles->deleteFromId($content->t_content_file_id);
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

		echo json_encode(array('id' => $t_content_id));	// 成功時はidを返す
		exit;
	}

	// コンテンツを他テーマへコピー
	public function copycontentsAction()
	{
		$target			= $this->getRequest()->copy_mytheme_id;
		$content_vals	= $this->getRequest()->copy_val;

		$tContents = new Class_Model_Tecfolio_TContents();
		$tContentFiles = new Class_Model_Tecfolio_TContentFiles();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			foreach($content_vals as $content_val)
			{
				if(empty($content_val['url']))
				{
					// コンテンツIDを元に、ファイルの実データを取得
					// ファイルの実データを、同じ構造で挿入(コピー)
					$tmp = $tContentFiles->insertCopyFromContentId($content_val['id']);

					// 上で挿入したファイルIDとMyテーマID($target)でコンテンツを挿入
					$id = $this->getRandomId(self::PREFIX_CONTENTS);

					$params = array(
							'id' 					=> $id,
							'm_mytheme_id'			=> $target,
							't_content_file_id'		=> $tmp
					);

					$tContents->insert($params);
				}
				else
				{
					// 上で挿入したファイルIDとMyテーマID($target)でコンテンツを挿入
					$id = $this->getRandomId(self::PREFIX_CONTENTS);

					$params = array(
							'id' 					=> $id,
							'm_mytheme_id'			=> $target,
							'ref_title'				=> $content_val['title'],
							'ref_url'				=> $content_val['url'],
							'ref_class'				=> $content_val['class']
					);

					$tContents->insert($params);
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


		echo json_encode(array('id' => $target));
		exit;
	}

	// t_filesから他テーマへコンテンツとしてコピー
	public function copyfilesAction()
	{
		$target			= $this->getRequest()->copy_mytheme_id;
		$content_vals	= $this->getRequest()->copy_val;

		$tContents = new Class_Model_Tecfolio_TContents();
		$tContentFiles = new Class_Model_Tecfolio_TContentFiles();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$file_ids = array();
			foreach($content_vals as $content_val)
			{
				$tmp = $tContentFiles->insertCopyFromFileId($content_val['id']);
				$file_ids[] = $tmp;
			}

			// 上で挿入したファイルIDとMyテーマID($target)でコンテンツを挿入
			foreach($file_ids as $file_id)
			{
				$id = $this->getRandomId(self::PREFIX_CONTENTS);

				$params = array(
						'id' 					=> $id,
						'm_mytheme_id'			=> $target,
						't_content_file_id'		=> $file_id
				);

				$tContents->insert($params);
			}

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}


		echo json_encode(array('id' => $target));
		exit;
	}

	// 選択されたコンテンツの公開設定を更新する(ajax: 戻り値は JSONの配列)
	public function updatepublicityAction()
	{
		$id		= $this->getRequest()->selected_id;
		$pub	= $this->getRequest()->publicity;

		$params = array(
				'publicity'		=> $pub == 0 ? 0 : 1
		);

		$tContents	= new Class_Model_Tecfolio_TContents();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			foreach($id as $i)
				$tContents->updateFromId($i, $params);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id, 'publicity' => $pub));
		exit;
	}
	
	// 授業科目の公開設定を更新する(ajax: 戻り値は JSONの配列)
	public function updatepublicitysettingAction()
	{
		$id		= $this->getRequest()->id;
		$pub	= $this->getRequest()->pub_setting;
		
		$params = array(
				'publicity'		=> $pub == 0 ? 0 : 1
		);
		
		$mSubjects	= new Class_Model_Tecfolio_MSubjectsRegistered();
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$mSubjects->updateFromId($id, $params);
		
			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}
		
		echo json_encode(array('id' => $id, 'publicity' => $pub));
		exit;
	}

	// ポートフォリオ
	public function portfolioAction()
	{
		$this->view->assign('subtitle', 'ポートフォリオ');

		// メンター情報取得(左ペインメニュー表示用)
		$tMentor = new Class_Model_Tecfolio_TMentors();
		$mentors = $tMentor->selectFromMemberId($this->member->id);
		$this->view->assign('mentors', $mentors);

		$html = $this->view->render('tecfolio/common/portfolio.tpl');
		$this->getResponse()->setBody($html);
	}

	// ポートフォリオ：メインペイン描画用データの取得(ajax: 戻り値は JSONの配列)
	public function getportfolioAction()
	{
		$this->getportfolio();

		echo json_encode(array('portfolio' => $this->portfolio_arr, 'rubrics' => $this->rubrics_arr, 'mentors' => $this->mentors_arr, 'chat_log' => $this->chat_arr));
		exit;
	}

	// ポートフォリオ(教員ロールで授業科目選択時)：メインペイン描画用データの取得(ajax: 戻り値は JSONの配列)
	public function getsubjectportfolioAction()
	{
		$this->getportfolio();

		$memberid	= $this->getRequest()->memberid;
		$profile_arr = array();

		if(!empty($memberid))
		{
			// プロフィールの読み込み
			$tProfiles		= new Class_Model_Tecfolio_TProfiles();
			$profile		= $tProfiles->selectFromId($memberid);
			$profile_arr	= $profile->toArray();
		}

		echo json_encode(array('portfolio' => $this->portfolio_arr, 'rubrics' => $this->rubrics_arr, 'mentors' => $this->mentors_arr, 'chat_log' => $this->chat_arr, 'profile' => $profile_arr));
		exit;
	}

	public function getportfolio()
	{
		$id			= $this->getRequest()->id;
		$memberid	= $this->getRequest()->memberid;	// 授業科目選択時の教員画面のみ
		$mentorflg	= $this->getRequest()->mentor;		// メンター画面のみ

		$prefix = $this->getPrefix($id);

		$tPortfolio	= new Class_Model_Tecfolio_TPortfolio();

		// ポートフォリオ取得
		// 左ペインで授業科目選択の場合：
		// 		教員では対象の学生のデータを返し、学生では作成者が自身のものだけを返す
		// Myテーマ・学内施設の場合：
		// 		メンター画面では全てのデータを返し、それ以外では作成者が自身の者だけを返す
		if($this->controllerName == 'tecfolio/professor')
		{
			if(empty($memberid))
				$portfolio	= $tPortfolio->selectRelatedDataFromMythemeId($id);
			else
				$portfolio	= $tPortfolio->selectRelatedDataFromMythemeIdAndCreator($id, $memberid);
		}
		elseif(!empty($mentorflg))
		{
			$portfolio	= $tPortfolio->selectRelatedDataFromMythemeId($id);
		}
		else
		{
			$portfolio	= $tPortfolio->selectRelatedDataFromMythemeIdAndCreator($id, $this->member->id);
		}

		$this->portfolio_arr = array();
		foreach($portfolio as $pf)
		{
			$this->portfolio_arr[] = $pf->toArray();
		}

		// 選択可能なルーブリック取得
		$tRubricMap	= new Class_Model_Tecfolio_TRubricMap();
		$rubrics	= $tRubricMap->selectFromParentId($id);

		$this->rubrics_arr = array();
		foreach($rubrics as $rubric)
		{
			$this->rubrics_arr[] = $rubric->toArray();
		}

		// メンター取得
		$tMentors	= new Class_Model_Tecfolio_TMentors();

		// 条件：授業科目ではない、あるいは教員ではない、あるいは$memberidが空ではない(教員のデフォルト画面・「すべて」選択時では取得しない)
		if($prefix != self::PREFIX_SUBJECT || $this->controllerName != 'tecfolio/professor' || !empty($memberid))
			$mentors 	= $tMentors->selectProfileFromMythemeId($id);

		$this->mentors_arr = array();
		$this->chat_arr = array();

		// 相談取得
		if(!empty($mentors))
		{
			$this->mentors_arr 	= $mentors->toArray();
			$tChatMentor	= new Class_Model_Tecfolio_TChatMentor();

			// 授業科目選択時ではメンター(＝教員)と学生のみの書き込み記録を取得する
			if($prefix == self::PREFIX_SUBJECT)
			{
				if($this->controllerName == 'tecfolio/professor')
					$chats	= $tChatMentor->selectFromMentorIdAndTargetId($this->mentors_arr['id'], $memberid);
				else
					$chats	= $tChatMentor->selectFromMentorIdAndTargetId($this->mentors_arr['id'], $this->member->id);
			}
			else
			{
				$chats	= $tChatMentor->selectFromMentorId($this->mentors_arr['id']);
			}

			foreach($chats as $chat)
			{
				$this->chat_arr[] = $chat->toArray();
			}
		}
	}

	// ポートフォリオ：ルーブリック描画用データの取得(ajax: 戻り値は JSONの配列)
	public function getrubricmatrixAction()
	{
		$id = $this->getRequest()->id;

		$tMatrix	= new Class_Model_Tecfolio_TRubricMatrix();

		// 表データ取得
		$matrix	= $tMatrix->selectFromRubricId($id);

		$matrix_arr = array();
		foreach($matrix as $m)
		{
			$matrix_arr[] = $m->toArray();
		}

		echo json_encode(array('matrix' => $matrix_arr));
		exit;
	}

	// 新規ポートフォリオ(ajax: 戻り値は JSONの配列)
	public function insertportfolioAction()
	{
		$m_mytheme_id	= $this->getRequest()->portfolio_mytheme_id;
		$title			= $this->getRequest()->title;

		$t_content_id	= $this->getRequest()->contentcheck;

		// ポートフォリオ挿入
		$tPortfolio = new Class_Model_Tecfolio_TPortfolio();

		$id = $this->getRandomId(self::PREFIX_PORTFOLIO);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$params = array(
					'id' 				=> $id,
					'title'				=> $title,
					'm_mytheme_id'		=> $m_mytheme_id
			);

			$tPortfolio->insert($params);

			if(!empty($t_content_id))
			{
				// コンテンツ挿入
				$tPFC = new Class_Model_Tecfolio_TPortfolioContents();

				foreach($t_content_id as $cid)
				{
					$pfc_id = $this->getRandomId(self::PREFIX_PF_CONTENTS);

					$param = array(
						'id'				=> $pfc_id,
						't_portfolio_id'	=> $id,
						't_content_id'		=> $cid
					);
					$tPFC->insert($param);
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

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}

	// コンテンツ追加(ajax: 戻り値は JSONの配列)
	public function addcontentstoportfolioAction()
	{
		$id				= $this->getRequest()->t_portfolio_id;
		$t_content_id	= $this->getRequest()->contentcheck;

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// コンテンツ挿入
			$tPFC = new Class_Model_Tecfolio_TPortfolioContents();

			foreach($t_content_id as $cid)
			{
				$pfc_id = $this->getRandomId(self::PREFIX_PF_CONTENTS);

				$param = array(
						'id'				=> $pfc_id,
						't_portfolio_id'	=> $id,
						't_content_id'		=> $cid
				);
				$tPFC->insert($param);
			}

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}

	// 選択されたタイトルを削除(ajax: 戻り値は JSONの配列)
	public function deleteportfolioAction()
	{
		$t_portfolio_id		= $this->getRequest()->removecheck;

		$tPortfolio = new Class_Model_Tecfolio_TPortfolio();
		$tPortfolioContents = new Class_Model_Tecfolio_TPortfolioContents();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			foreach($t_portfolio_id as $id)
			{
				$tPortfolioContents->deleteFromPortfolioId($id);
				$tPortfolio->deleteFromId($id);
			}

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $t_portfolio_id));	// 成功時はid(配列)を返す
		exit;
	}

	// ポートフォリオ更新：タイトルのみ(ajax: 戻り値は JSONの配列)
	public function updatetitleAction()
	{
		$id		= $this->getRequest()->id;
		$title	= $this->getRequest()->title;

		// ポートフォリオ挿入
		$tPortfolio = new Class_Model_Tecfolio_TPortfolio();

		$params = array(
				'title'		=> $title
		);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$tPortfolio->updateFromId($id, $params);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}

	// ポートフォリオ更新：ショーケースフラグのみ(ajax: 戻り値は JSONの配列)
	public function updateportfolioAction()
	{
		$id				= $this->getRequest()->id;
		$showcase_flag	= $this->getRequest()->showcase_flag;
		if(empty($showcase_flag))
			$showcase_flag = 0;

		// ポートフォリオ挿入
		$tPortfolio = new Class_Model_Tecfolio_TPortfolio();

		$params = array(
				'showcase_flag'		=> $showcase_flag
		);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$tPortfolio->updateFromId($id, $params);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}

	// ポートフォリオ更新：メンターコメントのみ(ajax: 戻り値は JSONの配列)
	public function updateportfolioformentorAction()
	{
		$id				= $this->getRequest()->id;
		$mentor_comment	= $this->getRequest()->mentor_comment;

		// ポートフォリオ挿入
		$tPortfolio = new Class_Model_Tecfolio_TPortfolio();

		$params = array(
				'mentor_comment'	=> $mentor_comment
		);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$tPortfolio->updateFromId($id, $params);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}

	// ポートフォリオ：編集用描画用データの取得(ajax: 戻り値は JSONの配列)
	public function getportfoliodetailAction()
	{
		$id = $this->getRequest()->t_portfolio_id;

		$tPortfolio	= new Class_Model_Tecfolio_TPortfolio();

		// ポートフォリオ取得
		$portfolio	= $tPortfolio->selectFromId($id);

		$portfolio = $portfolio->toArray();

		// コンテンツ取得
		$contents	= $tPortfolio->selectContentsFromId($id);

		$contents_arr = array();
		foreach($contents as $content)
		{
			$contents_arr[] = $content->toArray();
		}

		// 自己評価取得
		$tRubricInput	= new Class_Model_Tecfolio_TRubricInput();
		$selfrating 	 = $tRubricInput->selectFromPortfolioId($id);

		$selfrating_arr = array();
		foreach($selfrating as $rate)
		{
			$selfrating_arr[] = $rate->toArray();
		}

		// メンター評価取得
		$tRubricMentor	= new Class_Model_Tecfolio_TRubricMentor();
		$mentorrating 	 = $tRubricMentor->selectFromPortfolioId($id);

		$mentorrating_arr = array();
		foreach($mentorrating as $rate)
		{
			$mentorrating_arr[] = $rate->toArray();
		}

		// ルーブリック取得
		$tMatrix	= new Class_Model_Tecfolio_TRubricMatrix();
		$matrix	= $tMatrix->selectFromPortfolioId($id);

		$matrix_arr = array();
		foreach($matrix as $m)
		{
			$matrix_arr[] = $m->toArray();
		}

		echo json_encode(array('portfolio' => $portfolio, 'contents' => $contents_arr, 'selfrating' => $selfrating_arr, 'mentorrating' => $mentorrating_arr, 'matrix' => $matrix_arr));
		exit;
	}

	// ポートフォリオでルーブリックを選択(ajax: 戻り値は JSONの配列)
	public function updateportfoliorubricAction()
	{
		$id			= $this->getRequest()->id;
		$rubric_id  = $this->getRequest()->selectrubric;

		// ポートフォリオ挿入
		$tPortfolio = new Class_Model_Tecfolio_TPortfolio();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$params = array(
					'm_rubric_id' 		=> $rubric_id,
					'self_comment'		=> null,
					'mentor_comment'	=> null
			);

			// ルーブリックの入力値(自己/メンター)を削除
			$tRubricInput = new Class_Model_Tecfolio_TRubricInput();
			$tRubricInput->deleteFromPortfolioId($id);

			$tRubricMentor = new Class_Model_Tecfolio_TRubricMentor();
			$tRubricMentor->deleteFromPortfolioId($id);

			$tPortfolio->updateFromId($id, $params);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}

	// コンテンツをタイトルから解除する(ajax: 戻り値は JSONの配列)
	public function deletepfcAction()
	{
		$id			= $this->getRequest()->id;
		$pfc_id		= $this->getRequest()->pfc_id;

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$tPFC = new Class_Model_Tecfolio_TPortfolioContents();

			$tPFC->deleteFromId($pfc_id);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}

	// ルーブリックを解除(ajax: 戻り値は JSONの配列)
	public function deleteselectedrubricAction()
	{
		$id			= $this->getRequest()->id;

		$tPortfolio = new Class_Model_Tecfolio_TPortfolio();
		$tRubricInput = new Class_Model_Tecfolio_TRubricInput();
		$tRubricMentor = new Class_Model_Tecfolio_TRubricMentor();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// 自己評価削除
			$tRubricInput->deleteFromPortfolioId($id);
			// メンター評価削除
			$tRubricMentor->deleteFromPortfolioId($id);

			// 選択ルーブリック、自己コメント、メンターコメント削除
			$params = array(
					'm_rubric_id' 		=> null,
					'self_comment'		=> null,
					'mentor_comment'	=> null
			);

			$tPortfolio->updateFromId($id, $params);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}

	// 自己評価更新(ajax: 戻り値は JSONの配列)
	public function upsertselfratingAction()
	{
		$portfolio_id	= $this->getRequest()->id;
		$rank			= $this->getRequest()->input_rating;			// 配列

		$self_comment	= $this->getRequest()->input_self_comment;		// 追加：コメント

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// コメント更新
			$tPortfolio = new Class_Model_Tecfolio_TPortfolio();

			$param = array(
					'self_comment'	=> $self_comment
			);

			$tPortfolio->updateFromId($portfolio_id, $param);

			// 自己評価挿入
			$tRubricInput = new Class_Model_Tecfolio_TRubricInput();

			$tRubricInput->deleteFromPortfolioId($portfolio_id);

			for($i = 0; $i < count($rank); $i++)
			{
				$params = array(
						't_portfolio_id' 	=> $portfolio_id,
						'vertical'			=> $i + 1,
						'rank'				=> $rank[$i]
				);

				$tRubricInput->insert($params);
			}

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $portfolio_id));	// 成功時はidを返す
		exit;
	}

	// メンター評価更新(ajax: 戻り値は JSONの配列)
	public function upsertmentorratingAction()
	{
		$portfolio_id	= $this->getRequest()->id;
		$rank			= $this->getRequest()->input_rating;			// 配列

		$mentor_comment	= $this->getRequest()->input_mentor_comment;	// 追加：コメント

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// コメント更新
			$tPortfolio = new Class_Model_Tecfolio_TPortfolio();

			$param = array(
					'mentor_comment'	=> $mentor_comment
			);

			$tPortfolio->updateFromId($portfolio_id, $param);

			// メンター評価挿入
			$tRubricInput = new Class_Model_Tecfolio_TRubricMentor();

			$tRubricInput->deleteFromPortfolioId($portfolio_id);

			for($i = 0; $i < count($rank); $i++)
			{
				$params = array(
						't_portfolio_id' 	=> $portfolio_id,
						'vertical'			=> $i + 1,
						'rank'				=> $rank[$i]
				);

				$tRubricInput->insert($params);
			}

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $portfolio_id));	// 成功時はidを返す
		exit;
	}

	// メンター検索(ajax: 戻り値は JSONの配列)
	public function searchmentorAction()
	{
		$mentor_search_input	= $this->getRequest()->mentor_search_input;

		$mMembers = new Class_Model_Tecfolio_MMembers();
		$members = $mMembers->selectFromNameJpExceptMe($mentor_search_input, $this->member->id);
		$count = count($members);

		$members_arr = array();
		if($count < 100)
		{
			foreach($members as $member)
			{
				$members_arr[] = $member->toArray();
			}
		}

		echo json_encode(array('members' => $members_arr, 'count' => $count));
		exit;
	}

	// メンター依頼
	public function requestmentorAction()
	{
		$mytheme_id		= $this->getRequest()->mytheme_id;
		$mentor_num		= $this->getRequest()->mentor_num;
		$mentor_id		= $this->getRequest()->mentor_selected_id;
		$mentor_name	= $this->getRequest()->mentor_selected_name;
		$mentor_syzkcd	= $this->getRequest()->mentor_selected_syzkcd;

		$id = $this->getRandomId(self::PREFIX_MENTOR);

		$tMentors = new Class_Model_Tecfolio_TMentors();

		$params = array(
				'id'				=> $id,
				'm_mytheme_id'		=> $mytheme_id,
				'mentor_number'		=> $mentor_num,
				'm_member_id'		=> $mentor_id,
				'name_jp'			=> $mentor_name,
				'syzkcd_c'			=> $mentor_syzkcd
		);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$tMentors->insert($params);
			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $mytheme_id));
		exit;
	}

	// メンター承諾/拒否
	public function updatementorAction()
	{
		$id		= $this->getRequest()->mentor_id;
		$flag	= $this->getRequest()->mentor_flag;

		$tMentors = new Class_Model_Tecfolio_TMentors();

		$params = array(
				'agreement_flag'	=> $flag
		);

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$tMentors->updateFromId($id, $params);
			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));
		exit;
	}

	// メンターと相談する(ajax: 戻り値は JSONの配列)
	public function insertchatmentorAction()
	{
		$member = Zend_Auth::getInstance()->getIdentity();
		$m_member_id = $member->id;

		$m_mytheme_id	= $this->getRequest()->chat_mytheme_id;
		$t_mentor_id	= $this->getRequest()->chat_mentor_id;
		$title			= $this->getRequest()->chat_title;
		$body			= $this->getRequest()->chat_body;

		$tgt_member_id	= $this->getRequest()->chat_tgt_id;		// 教員の授業科目のみ

		$params = array(
				'm_mytheme_id'		=> $m_mytheme_id,
				't_mentor_id'		=> $t_mentor_id,
				'm_member_id'		=> $m_member_id,
				'title'				=> $title,
				'body'				=> $body
		);

		if(!empty($tgt_member_id))
			$params['tgt_member_id'] = $tgt_member_id;

		$tChatMentor	= new Class_Model_Tecfolio_TChatMentor();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$id = $tChatMentor->insert($params);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id));	// 成功時はidを返す
		exit;
	}

	// 個人設定
	public function profileAction()
	{
		$this->view->assign('subtitle', '個人設定');

		$html = $this->view->render('tecfolio/common/profile.tpl');
		$this->getResponse()->setBody($html);
	}

	// 個人設定を更新(ajax: 戻り値は JSONの配列)
	public function updateprofileAction()
	{
		$root		= $this->getRoot();
		$id			= $this->member->id;
		$profile	= $this->getRequest()->profile;
		$edittype	= $this->getRequest()->edittype;
		$tmp		= $this->getRequest()->image_main_hidden;
		$img		= str_replace('/tmp/', '/img/', $tmp);

		if(empty($profile['mentor_flag']))
			$mentor_flag = 0;
		else
			$mentor_flag = 1;

		if(empty($profile['email_flag']))
			$email_flag = 0;
		else
			$email_flag = 1;


		$tProfiles = new Class_Model_Tecfolio_TProfiles();

		$params = array(
			'nickname'		=> '',//$profile['nickname'],
			//'languages'		=> $profile['languages'],
			'email_2'		=> $profile['email_2'],
			'email_3'		=> $profile['email_3'],
			//'image_name'	=> $profile['image_name'],
			'speciality'	=> $profile['speciality'],
			'seminar'		=> $profile['seminar'],
			'highschool'	=> $profile['highschool'],
			'birthday'		=> $profile['birthday'],
			'sex'			=> $profile['sex'],
			'birthplace'	=> $profile['birthplace'],
			'mentor_flag'	=> $mentor_flag,
			'hobby'			=> $profile['hobby'],
			'ability'		=> $profile['ability'],
			'likes'			=> $profile['likes'],
			'dislikes'		=> $profile['dislikes'],
			'personality'	=> $profile['personality'],
			'strength'		=> $profile['strength'],
			'weekness'		=> $profile['weekness'],
			'cert_1'		=> $profile['cert_1'],
			'cert_2'		=> $profile['cert_2'],
			'cert_3'		=> $profile['cert_3'],
			'cert_4'		=> $profile['cert_4'],
			'cert_5'		=> $profile['cert_5'],
			'pr'			=> $profile['pr'],
			'memories'		=> $profile['memories'],
			'tried'			=> $profile['tried'],
			'succeeded'		=> $profile['succeeded'],
			'failed'		=> $profile['failed']
		);

		// 画像に変更がある場合
		if(!empty($tmp))
		{
			$params['input_name'] = $img;
			$params['image_name'] = $profile['image_name'];
		}

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// 追加/変更
			if(empty($edittype))
			{
				$params['m_member_id'] = $id;

				$tProfiles->insert($params);
			}
			else
			{
				$tProfiles->updateFromId($id, $params);
			}

			// 画像に変更がある場合
			if(!empty($tmp))
			{
				foreach ( glob($root . '/img/' . $this->member->id . '*') as $val ) {
					unlink($val);
				}
				copy($root . $tmp, $root . $img);
			}

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode(array('id' => $id, 'edittype' => $edittype));	// 成功時はidを返す
		exit;
	}

	public function getRoot()
	{
		$root	= $_SERVER['DOCUMENT_ROOT'];

		if(substr($root, -1) == '/')
		{
			$root = substr($root, 0, -1);
		}

		return $root;
	}

	public function getRandomNum()
	{
		return date("YmdHis", time()) . sprintf("%08d", mt_rand(0,99999999));
	}

	public function getRandomId($prefix)
	{
		return $prefix . '_ID' . date("YmdHis", time()) . sprintf("%08d", mt_rand(0,99999999));
	}

	/*** 画像に関する仕様 ***/
	/*
	 * アップロードされた画像は必要に応じてリサイズされ、ファイルとして格納される
	 * DBには物理ファイル名と元ファイル名を保持する
	 * 画像の変更時には一時ファイルがアップロードされる
	 * .fileUploader以下には、元ファイル名の表示、type="file"、(変更時のみ)一時ファイル名の保持をするinputを設置する
	 * ※一ページ内での複数ファイルアップロードには対応しない共通仕様
	 *
	 ************************/

	// 画像出力
	// @param	$file		$_FILES
	// @param	$num		出力フォルダ指定(0='/tmp', 1='/img')
	// @return	出力ファイルのパス
	public function saveFile($file, $num=0)
	{
		$root		= $this->getRoot();

		if($num==1)	$dir = 'img';
		else		$dir = 'tmp';

		$path = $dir;
		// 各フォルダが存在しない場合、これを作成する
		if(!file_exists($root . "/" . $path)) {
			mkdir($root . "/" . $path, 0755, true);
		}
		$member = Zend_Auth::getInstance()->getIdentity();
		$ext	= mb_strtolower(substr($file['name'], strrpos($file['name'], '.')));

		$filepath = $path . "/" . $this->addRandToFile($member->id . '.' . $ext);

		// 旧ファイルの削除
		$oldfiles = $root . "/" . $path . "/" . $member->id . "*";
		// glob() でヒットしたファイルのリストを取得
		foreach ( glob($oldfiles) as $val ) {
			unlink($val);
		}

		// 画像ファイルを出力
		$fp = fopen($root . "/" . $filepath, "w");
		if (!(empty($fp))) {
			flock($fp, LOCK_EX);
			fputs($fp, file_get_contents($file['tmp_name']));
			flock($fp, LOCK_UN);
			fclose($fp);
		}

		return '/' . $filepath;
	}

	// 画像ファイルをリサイズしてコピーする
	// @param	$from		ソース画像ファイルパス
	// @param	$to			出力画像ファイルパス
	// @param	$width		画像横幅
	// @param	$height		画像縦幅
	// @param	$flg		TRUEならソース画像を削除する
	// @return	出力ファイルのパス
	public function resizeImg($from, $to, $maxwidth, $maxheight=NULL, $flg=FALSE)
	{
		$root	= $this->getRoot();

		// ソース画像の指定
		$from = $root . $from;

		// ソース画像の縦横サイズを取得
		list($width, $height) = getimagesize($from);

		// 高さの指定がなかった場合、元画像の高さを代入
		if(empty($maxheight)){
			$maxheight = $height;
		}

// 		// 比率を維持、かつ最大サイズを考慮した画像サイズを計算
// 		$r1			= $width / $maxwidth;
// 		$r2			= $height / $maxheight;
// 		$cnvt_flg 	= ($r1 > $r2) ? 1 : 0;
// 		$ratio		= $cnvt_flg ? $r1 : $r2;

// 		// 浮動小数点の丸め誤差対策
// 		$newwidth	= sprintf('%f', $width  / $ratio);
// 		$newheight	= sprintf('%f', $height / $ratio);

		// 比率維持はしない(80x80で固定)
		$newwidth	= $maxwidth;
		$newheight	= $maxheight;

		// 拡張子取得(小文字)
		$ext	= mb_strtolower(substr($from, strrpos($from, '.')));

		// サイズを指定して、背景用画像を生成
		$canvas = imagecreatetruecolor($newwidth, $newheight);

		//ブレンドモードを無効にする
		imagealphablending($canvas, false);

		//完全なアルファチャネル情報を保存するフラグをonにする
		imagesavealpha($canvas, true);

		// ファイル名から、画像インスタンスを生成
		switch($ext)
		{
			case '.png':
				$image = imagecreatefrompng($from);
				break;

			case '.gif':
				$image = imagecreatefromgif($from);
				break;

			case '.jpg':
			case '.jpeg':
			case '.jpe':
			default:
				$image = imagecreatefromjpeg($from);
		}

		// 背景画像に、画像をコピーする
		imagecopyresampled(
			$canvas,		// 背景画像
			$image,			// ソース画像
			0,				// 背景画像の x座標
			0,				// 背景画像の y座標
			0,				// ソース画像の x座標
			0,				// ソース画像の y座標
			$newwidth,		// 背景画像の幅
			$newheight,		// 背景画像の高さ
			$width,			// ソース画像ファイルの幅
			$height			// ソース画像ファイルの高さ
		);

		// 画像を出力する
		imagepng(
			$canvas,		// 背景画像
			$root . $to,	// 出力するファイル名
			0				// 画像精度
		);
		switch($ext)
		{
			case '.png':
				imagepng(
				$canvas,		// 背景画像
				$root . $to,	// 出力するファイル名
				0				// 画像精度
				);
				break;

			case '.gif':
				imagegif(
				$canvas,		// 背景画像
				$root . $to		// 出力するファイル名
				);
				break;

			case '.jpg':
			case '.jpeg':
			case '.jpe':
			default:
				imagejpeg(
				$canvas,		// 背景画像
				$root . $to,	// 出力するファイル名
				100				// 画像精度
				);
		}

		// メモリを開放する
		imagedestroy($canvas);

		// 元画像の削除
		if($flg)
			unlink($root . $from);
	}

	// 画像アップ時にサムネイルを即時に表示する
	// ここでアップロードした画像は確認画面遷移時に破棄し、再利用可能な情報を保持するために再アップロードする
	public function uploadimgAction()
	{
		if(!empty($_FILES['file']['tmp_name']))
		{
			$_FILES['file']['name'] = mb_convert_encoding($_FILES['file']['name'], 'UTF-8');

			$translate = Zend_Registry::get('Zend_Translate');

			if(strpos($_FILES['file']['type'], 'image', 0) === 0)
			{
				if($_FILES['file']['size'] < 10485760)
				{
					$filepath = $this->saveFile($_FILES['file'], 0);
					$this->resizeImg($filepath, $filepath, 80, 80);
				}
				else
				{
					$filepath = 'ERROR:' . sprintf($this->fmtTranslation($translate->_("アップロード上限を超えています(ファイルサイズ：%1)")), $this->formatBytes($_FILES['file']['size'], 2)) . "\n";
				}
			}
			else
			{
				$filepath = 'ERROR:' . $translate->_("画像ファイルを選択してください") . "\n";
			}
			echo $filepath;
			exit;
		}
		echo 'error';
		exit;
	}

	/**
	 * バイト数をフォーマットする
	 * @param integer $bytes
	 * @param integer $precision
	 * @param array $units
	 */
	function formatBytes($bytes, $precision = 2, array $units = null)
	{
		if ( abs($bytes) < 1024 )
		{
			$precision = 0;
		}

		if ( is_array($units) === false )
		{
			$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		}

		if ( $bytes < 0 )
		{
			$sign = '-';
			$bytes = abs($bytes);
		}
		else
		{
			$sign = '';
		}

		$exp   = floor(log($bytes) / log(1024));
		$unit  = $units[$exp];
		$bytes = $bytes / pow(1024, floor($exp));
		$bytes = sprintf('%.'.$precision.'f', $bytes);
		return $sign.$bytes.' '.$unit;
	}

	// 年月日時分秒 + $digit桁の乱数を生成する
	// 例:2015010122334499999999
	public function rand($digit)
	{
		$num = 9;

		for($i = 1; $i < $digit; $i++)
		{
			$num = $num * 10 + 9;
		}

		return date("YmdHis", time()) . sprintf("%08d", mt_rand(0,$num));
	}

	// ファイル名に乱数を付加する
	public function addRandToFile($str, $num=8)
	{
		// 最後に現れる拡張子ドット位置
		$pos = strrpos($str, '.');

		// 指定位置に日時ベースの乱数追加
		$s = substr_replace(md5($str), $this->rand($num), $pos) . substr($str, $pos);	// 文字列.拡張子→(文字列 + 乱数) + .拡張子

		return $s;
	}

	// CiNiiのWeb APIテスト
	public function ciniiAction()
	{
		$html = $this->view->render('tecfolio/common/cinii.tpl');
		$this->getResponse()->setBody($html);
	}

	public function getciniiAction()
	{
		$url = 'http://ci.nii.ac.jp/opensearch/search';

		$text 			= $this->getRequest()->search_text;
		$text_hidden 	= $this->getRequest()->search_text_hidden;

		$index 			= $this->getRequest()->search_index;
		$index_hidden 	= $this->getRequest()->search_index_hidden;

		$order			= $this->getRequest()->search_order;
		$order_hidden 	= $this->getRequest()->search_order_hidden;

		$flag			= $this->getRequest()->search_flag;

		// ページネーションからの遷移の場合、アクティブな値ではなく、退避させた値で検索を行う
		if($flag == 1)
		{
			$text	= $text_hidden;
			$index	= $index_hidden;
			$order	= $order_hidden;
		}
		$start 		= $this->getRequest()->start_num;

		$result['error'] = '';

		$translate = Zend_Registry::get('Zend_Translate');

		if(empty($text))
		{
			$result['error'] .= $translate->_("検索ワードを入力してください") . "\n";
		}

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		$url_param = $url . '?' . $index . '=' . $text . '&lang=' . $this->locale . '&format=rss&count=50&start=' . $start . '&sortorder=' . $order;

		// Proxy設定
		$r_default_context = stream_context_get_default(array
			('http' => array(
//					'proxy' => 'tcp://xxxxxxxx.xx.xx:8080',
					'request_fulluri' => True,
				),
			)
		);
		libxml_set_streams_context($r_default_context);

		$rss = simplexml_load_file($url_param, 'SimpleXMLElement', LIBXML_NOCDATA);

		$channel = array();

		if(!empty($rss->channel))
		{
			$channel['title']		= $rss->channel->title;
			$channel['link']		= $rss->channel->link;
			$channel['opensearch']	= $rss->channel->children('opensearch',true);
		}

		$rows = array();

		if(!empty($rss->item))
		{
			$i = 0;
			foreach($rss->item as $item)
			{
				$rows[$i] = array();
				$rows[$i]['title'] 			= $item->title;
				$rows[$i]['link'] 			= $item->link;
				$rows[$i]['description'] 	= $item->description;
				$rows[$i]['dc'] 			= $item->children('dc',true);
				$rows[$i]['prism'] 			= $item->children('prism',true);

				$i++;
			}
		}

		echo json_encode(array('channel' => $channel, 'rows' => $rows));
		exit;
	}

	// services_opensearchでの実装
	public function getciniioldaction()
	{
		require_once 'Services/OpenSearch.php';

		$url = 'http://search.hatena.ne.jp/osxml';

		$os = new Services_OpenSearch($url);

		$result = $os->search('research');

		$rows = array();
		foreach($result as $item)
		{
			$rows[] = array(
					'title'			=> $item['title'],
					'description'	=> $item['description'],
					'link'			=> $item['link'],
			);
		}

		echo json_encode(array('rows' => $rows));
		exit;
	}

	// AmazonのWeb APIテスト
	public function amazonAction()
	{
		$html = $this->view->render('tecfolio/common/amazon.tpl');
		$this->getResponse()->setBody($html);
	}

	function urlencode_rfc3986($str) {

		return str_replace('%7E', '~', rawurlencode($str));
	}

	public function getamazonAction()
	{
		$index 		= $this->getRequest()->search_index;
		$text 		= $this->getRequest()->search_text;
		$hidden 	= $this->getRequest()->search_text_hidden;
		$flag		= $this->getRequest()->search_flag;

		// ページネーションからの遷移の場合、アクティブな値ではなく、退避させた値で検索を行う
		if($flag == 1)
			$text = $hidden;
		$start 		= $this->getRequest()->start_num;

		$result['error'] = '';

		$translate = Zend_Registry::get('Zend_Translate');

		if(empty($text))
		{
			$result['error'] .= $translate->_("検索ワードを入力してください") . "\n";
		}

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		// 必須
		$access_key_id = 'XXXXXXXXXXXXXXXXXXXX';
		$secret_access_key = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
		$baseurl = 'https://ecs.amazonaws.jp/onca/xml';

		// パラメータ
		$params['Service'] = 'AWSECommerceService';
		$params['AWSAccessKeyId'] = $access_key_id;
		$params['Version'] = '2011-08-01';
		$params['Operation'] = 'ItemSearch';
		$params['SearchIndex'] = $index;
		$params['Power'] = 'binding:not kindle';
		$params['Title'] = $text;
		$params['AssociateTag'] = 'xxxxxxxxxxxx-xx';
		$params['ResponseGroup'] = 'Medium';
		$params['Sort'] = 'daterank';
		$params['ItemPage'] = $start;
		$params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
		ksort($params);

		// 送信用URL・シグネチャ作成
		$canonical_string = '';
		foreach ($params as $k => $v) {
			$canonical_string .= '&' . $this->urlencode_rfc3986($k) . '=' . $this->urlencode_rfc3986($v);
		}
		$canonical_string = substr($canonical_string, 1);
		$parsed_url = parse_url($baseurl);
		$string_to_sign = "GET\n{$parsed_url['host']}\n{$parsed_url['path']}\n{$canonical_string}";
		$signature = base64_encode(hash_hmac('sha256', $string_to_sign, $secret_access_key, true));
		$url = $baseurl . '?' . $canonical_string . '&Signature=' . $this->urlencode_rfc3986($signature);

		// Proxy設定
		$r_default_context = stream_context_get_default(array
			('http' => array(
//					'proxy' => 'tcp://xxxxxxxx.co.jp:8080',
					'request_fulluri' => True,
				),
			)
		);
		libxml_set_streams_context($r_default_context);

		// xml取得
		$xml = simplexml_load_file($url);

		$channel	= array();
		$rows		= array();

		if(!empty($xml))
		{
			$channel['TotalResults'] 	= $xml->Items->TotalResults;
			$channel['ItemPage'] 		= $start;

			$i = 0;
			foreach($xml->Items->Item as $item)
			{
				$rows[$i]['ASIN'] 				= $item->ASIN;
				$rows[$i]['DetailPageURL'] 		= substr($item->DetailPageURL, 0, strpos($item->DetailPageURL, '%3FSubscriptionId'));
				$rows[$i]['ItemAttributes'] 	= $item->ItemAttributes;
				$rows[$i]['ImageSets'] 			= $item->ImageSets;

				$i++;
			}
		}

		echo json_encode(array('channel' => $channel, 'rows' => $rows));
		exit;
	}

	// Ciniiからコンテンツ追加
	public function insertciniiAction()
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$result = $this->insertSubjectContents($this->getRequest()->id, array($this, 'insertcinii'));

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => $e->getMessage()));
			return;
		}

		echo json_encode(array('id' => $result['m_mytheme_id']));	// 成功時はidを返す
		return;
	}
	public function insertcinii($publicity)
	{
		$m_mytheme_id		= $this->getRequest()->id;

		$title				= $this->getRequest()->cinii_title;
		$url				= $this->getRequest()->cinii_url;

		$t_content_id		= array();

		$tContents = new Class_Model_Tecfolio_TContents();

		// 2016/02/29 エラー処理追加
		$result['error'] = '';

		if(empty($title))
		{
			$result['error'] .= '追加する文献情報を選択してください<br>';
		}

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		foreach($title as $key => $value)
		{
			$id = $this->getRandomId(self::PREFIX_CONTENTS);
			$t_content_id[] = $id;

			$params = array(
					'id' 				=> $id,
					'm_mytheme_id'		=> $m_mytheme_id,
					'ref_title'			=> $title[$key],
					'ref_url'			=> $url[$key],
					'ref_class'			=> '0',
					'poster_name'		=> $this->member->name_jp,
					'publicity'			=> $publicity
			);

			$tContents->insert($params);
		}

		return array('m_mytheme_id' => $m_mytheme_id, 't_content_id' => $t_content_id);
	}

	// Amazonからコンテンツ追加
	public function insertamazonAction()
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$result = $this->insertSubjectContents($this->getRequest()->id, array($this, 'insertamazon'));

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => $e->getMessage()));
			return;
		}

		echo json_encode(array('id' => $result['m_mytheme_id']));	// 成功時はidを返す
		return;
	}
	public function insertamazon($publicity)
	{
		$m_mytheme_id		= $this->getRequest()->id;

		$title				= $this->getRequest()->amazon_title;
		$url				= $this->getRequest()->amazon_url;

		$t_content_id		= array();

		$tContents = new Class_Model_Tecfolio_TContents();

		// 2016/02/29 エラー処理追加
		$result['error'] = '';

		if(empty($title))
		{
			$result['error'] .= '追加する文献情報を選択してください<br>';
		}

		if ($result['error'] != '')
		{
			echo json_encode($result);
			exit;
		}

		foreach($title as $key => $value)
		{
			$id = $this->getRandomId(self::PREFIX_CONTENTS);
			$t_content_id[] = $id;

			$params = array(
					'id' 				=> $id,
					'm_mytheme_id'		=> $m_mytheme_id,
					'ref_title'			=> $title[$key],
					'ref_url'			=> $url[$key],
					'ref_class'			=> '1',
					'poster_name'		=> $this->member->name_jp,
					'publicity'			=> $publicity
			);

			$tContents->insert($params);
		}

		return array('m_mytheme_id' => $m_mytheme_id, 't_content_id' => $t_content_id);
	}

	// ルーブリック
	public function rubricAction()
	{
		$this->view->assign('subtitle', 'ルーブリック');

		$html = $this->view->render('tecfolio/common/rubric.tpl');
		$this->getResponse()->setBody($html);
	}

	// ルーブリック取得(ajax: 戻り値は JSONの配列)
	public function getrubricAction()
	{
		$id	= $this->getRequest()->id;

		$tRubricMap = new Class_Model_Tecfolio_TRubricMap();
		$rubrics = $tRubricMap->selectFromMythemeId($id);

		$rubrics_arr = array();
		foreach($rubrics as $rubric)
		{
			$rubrics_arr[] = $rubric->toArray();
		}

		echo json_encode(array('rubrics' => $rubrics_arr));
		exit;
	}

	public function exportrubricAction()
	{
		function setUnprotected($obj, $ranges)
		{
			if(!is_array($ranges))
			{
				$obj->getStyle($ranges)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
			}
			else
			{
				foreach($ranges as $range)
				{
					$obj->getStyle($range)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
				}
			}
		}

		require_once(dirname(__FILE__) . '/../../libs/PHPExcel/PHPExcel.php');

		$excel = new PHPExcel();
		// シートの設定
		$excel->setActiveSheetIndex(0);
		$sheet = $excel->getActiveSheet();
		$sheet->setTitle('sheet name');

		$id	= $this->getRequest()->id;

		// DBよりデータ取得
		$mRubric = new Class_Model_Tecfolio_MRubric();
		$rubric = $mRubric->selectFromIdIncludingLicense($id);

		$tMatrix = new Class_Model_Tecfolio_TRubricMatrix();
		$matrix = $tMatrix->selectFromRubricId($id);

		$base_v = 7;	// 観点タイトルの縦位置
		$alpha = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

		$max_h = 0;
		$max_v = 0;

		// ルーブリック本体部分の出力
		foreach($matrix as $m)
		{
			$sheet->setCellValue($alpha[$m['horizontal']] . ($base_v + $m['vertical']), $m['description']);
			$sheet->setCellValue($alpha[$m['horizontal']] . '6', $m['rank']);

			if($m['horizontal'] > $max_h)
				$max_h = $m['horizontal'];
			if($m['vertical'] > $max_v)
				$max_v = $m['vertical'];
		}

		// 追記用に縦横1マスずつの余白を持つ
		// 追加：縦は評点のため更に+1
		$max_h += 1;
		$max_v += 2;

		$sum_h		= 'A';
		for($i = 0; $i < $max_h; $i++)
			$sum_h++;					// 水平最大値(アルファベット大文字)
		$sum_v		= 6 + $max_v;		// 垂直最大値(数値)

		// 各見出しと対応する既存の値、説明文の設定
		$sheet->setCellValue('A1', '※緑色の部分に入力してください。');
		$sheet->setCellValue('A2', 'タイトル');
		$sheet->setCellValue('B2', $rubric->name);
		$sheet->setCellValue('A4', '課題文');
		$sheet->setCellValue('B4', $rubric->theme);
		$sheet->setCellValue('A5', '※「観点」「尺度」の数は、任意に設定できます。');
		$sheet->setCellValue('B6', '　評点→');
		$sheet->setCellValue('A7', '観点タイトル');
		$sheet->setCellValue('B7', "　尺度タイトル→\n↓観点の説明");		// use double quotes when you add escape sequences in a PHP string
		$sheet->setCellValue('A' . ($sum_v + 1), '↑観点タイトルに入力すると、観点が有効になります。');
		$sheet->setCellValue('A' . ($sum_v + 3), 'メモ');
		$sheet->setCellValue('B' . ($sum_v + 3), $rubric->memo);
		$sheet->setCellValue('A' . ($sum_v + 5), '原著者');
		$sheet->setCellValue('B' . ($sum_v + 5), $rubric->original_name_jp);
		$sheet->setCellValue('A' . ($sum_v + 6), '改変者');
		$sheet->setCellValue('B' . ($sum_v + 6), $rubric->editor_name_jp);
		$sheet->setCellValue('A' . ($sum_v + 8), 'ライセンス');
		$sheet->setCellValue('B' . ($sum_v + 8), $rubric->t_rubric_license_name);

		// 結合する列
		$sheet->mergeCells('B2:F2');	// タイトル
		$sheet->mergeCells('B4:F4');	// 課題文
		$sheet->mergeCells('B' . ($sum_v + 3) . ':F' . ($sum_v + 3));	// メモ

		// 基本スタイル
		$sheet->getDefaultStyle()->getFont()->setName('メイリオ');
		$sheet->getDefaultStyle()->getFont()->setSize(10);
		$sheet->getDefaultStyle()->getAlignment()->setWrapText(true);		// 折り返して全体を表示(Wrap text)
		$sheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$sheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

		$sheet->getProtection()->setSheet(true);

		switch($rubric->t_rubric_license_id)
		{
			case 1:
				$str = '"すべての権利を放棄,帰属表示,帰属表示－継承,帰属表示－改変禁止"';
				break;
			case 2:
				$str = '"帰属表示,帰属表示－継承,帰属表示－改変禁止"';
				break;
			case 3:
				$str = '"帰属表示－継承"';
				break;
		}

		// ライセンスの入力規則(リストボックス)
		$objValidation = $sheet->getCell('B' . ($sum_v + 8))->getDataValidation();
		$objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
		$objValidation->setAllowBlank(false);
		$objValidation->setShowInputMessage(true);
		$objValidation->setShowErrorMessage(true);
		$objValidation->setShowDropDown(true);
		$objValidation->setFormula1($str);

		// 各セル周囲のボーダーのみ個別設定が必要
		for($i = 'A'; $i <= $sum_h; $i++)
		{
			for($j = 5; $j <= $sum_v; $j++)
			{
				$sheet->getStyle($i . $j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOTTED);
			}
		}

		// 評点・観点・尺度部分のスタイル
		$syakudo	= 'A7:' . $sum_h . $sum_v;
		$kanten		= 'B6:' . $sum_h . '6';
		$sheet->getStyle($syakudo)->applyFromArray(
				array(
						'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'c4d79b')
						),
						'borders' => array(
								'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
								'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
								'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
								'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
						)
				)
		);
		$sheet->getStyle($kanten)->applyFromArray(
				array(
						'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb' => 'c4d79b')
						),
						'borders' => array(
								'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
								'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
								'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
								'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
						)
				)
		);
		// 評点・観点・尺度の内、入力可能なセルの保護解除
		setUnprotected($sheet, array('C6:'.$sum_h.'7', 'A8:'.$sum_h.$sum_v));

		// ラベルとなるセルのスタイル
		$label_arr = array('A2','A4', 'B6', 'A7', 'B7', 'A'.($sum_v + 3), 'A'.($sum_v + 5), 'A'.($sum_v + 6), 'A'.($sum_v + 8));

		foreach($label_arr as $label)
		{
			$sheet->getStyle($label)->applyFromArray(
					array(
							'font' => array(
									'bold'      => true
							),
							'fill' => array(
									'type' => PHPExcel_Style_Fill::FILL_SOLID,
									'color' => array('rgb' => 'a6a6a6')
							)
					)
			);
			$sheet->getStyle($label)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);	// ボーダーで囲う
		}

		// ラベルの右で入力可能なセルのスタイル
		$label_arr = array('B2:F2','B4:F4','B'.($sum_v + 3).':F'.($sum_v + 3), 'B'.($sum_v + 5), 'B'.($sum_v + 6), 'B'.($sum_v + 8));

		foreach($label_arr as $label)
		{
			$sheet->getStyle($label)->applyFromArray(
					array(
							'fill' => array(
									'type' => PHPExcel_Style_Fill::FILL_SOLID,
									'color' => array('rgb' => 'c4d79b')
							)
					)
			);
			$sheet->getStyle($label)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			setUnprotected($sheet, $label);		// 保護解除
		}

		// 説明文セルのスタイル
		$label_arr = array('A1','A5','A' . ($sum_v + 1));

		foreach($label_arr as $label)
		{
			$sheet->getStyle($label)->applyFromArray(
					array(
							'font' => array(
									'bold'		=> true,
									'color'		=> array('rgb' => 'ff0000')
							)
					)
			);
			$sheet->getStyle($label)->getAlignment()->setWrapText(false);		// 改行なしで表示
		}

		// 評点・観点・尺度タイトル部分のスタイル
		// 評点
		$title_hyoten	= 'B6:' . $sum_h . '6';
		$sheet->getStyle($title_hyoten)->applyFromArray(
				array(
						'font' => array(
								'bold'      => true
						)
				)
		);
		// 横軸
		$title_yoko		= 'A7:' . $sum_h . '7';
		$sheet->getStyle($title_yoko)->applyFromArray(
				array(
						'font' => array(
								'bold'      => true
						),
						'borders' => array(
								'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE)
						)
				)
		);
		// 縦軸
		$title_tate		= 'A6:A' . $sum_v;
		$sheet->getStyle($title_tate)->applyFromArray(
				array(
						'font' => array(
								'bold'      => true
						),
						'borders' => array(
								'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
						)
				)
		);
		// 縦軸:観点の説明
		$title_desc		= 'B6:B' . $sum_v;
		$sheet->getStyle($title_desc)->applyFromArray(
				array(
						'borders' => array(
								'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
						)
				)
		);

		// 幅と高さの設定
		$sheet->getColumnDimension('A')->setWidth(25);
		for($i = 'B'; $i <= 'Q'; $i++)
			$sheet->getColumnDimension($i)->setWidth(25);

		$base_height = 18;		// 基準となる高さ
		$heights = array(
				$base_height,	$base_height*2,	$base_height,	$base_height*3,	$base_height,
				$base_height*3
		);

		$heights_bot = array(
				$base_height,	$base_height,	$base_height*2,
				$base_height,	$base_height,	$base_height,	$base_height,	$base_height
		);

		// 観点・尺度部分の高さ
		for($i = 0; $i < $max_v; $i++)
			$heights[] = $base_height*4;

		$heights = array_merge($heights, $heights_bot);

		for($i = 1; $i < count($heights); $i++)
		{
			$sheet->getRowDimension($i)->setRowHeight($heights[$i-1]);
		}

		$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$writer->save('php://output');

		$filename = $rubric->name . (!empty($rubric->original_name_jp) ? '(原著者 '.$rubric->original_name_jp.
									(!empty($rubric->editor_name_jp) ? ', 改変者 '.$rubric->editor_name_jp.')': ')') : '');

		// ダウンロード
		$this->getResponse()
		->setHeader('Content-Type', 'application/vnd.ms-excel')
		->setHeader('Content-Disposition', 'attachment; filename="'.mb_convert_encoding($filename, 'SJIS').'.xlsx"')
		->setHeader('Cache-Control', 'no-cache')
		->setHeader('Pragma', 'no-cache')
		->sendResponse();
		exit;
	}

	// アルファベットを数字変換(先頭を0とする)
	public function alpha_to_numeric($alpha = '')
	{
		$arr = array(
				'A' => 0,
				'B' => 1,
				'C' => 2,
				'D' => 3,
				'E' => 4,
				'F' => 5,
				'G' => 6,
				'H' => 7,
				'I' => 8,
				'J' => 9,
				'K' => 10,
				'L' => 11,
				'M' => 12,
				'N' => 13,
				'O' => 14,
				'P' => 15,
				'Q' => 16,
				'R' => 17,
				'S' => 18,
				'T' => 19,
				'U' => 20,
				'V' => 21,
				'W' => 22,
				'X' => 23,
				'Y' => 24,
				'Z' => 25,
		);

		return (isset($arr[$alpha])) ? $arr[$alpha] : $arr;
	}

	public function readxlsx($filepath)
	{
		require_once(dirname(__FILE__) . '/../../libs/PHPExcel/PHPExcel/IOFactory.php');

		$fullpath = $this->getRoot() . $filepath;

		$translate = Zend_Registry::get('Zend_Translate');

		// ファイルの存在チェック
		if (!file_exists($fullpath))
		{
			$result['error'] = $translate->_("ファイル「" . $fullpath . "」が見つかりません") . "\n";
			echo json_encode($result);
			exit;
		}

		// PHPExcelによるファイルの読み込み
		$obj = PHPExcel_IOFactory::load($fullpath);

		// 配列で返す
		return $obj->getActiveSheet()->toArray(null,true,true,true);
	}

	/*
	 * * ルーブリックのインポート
	 *
	 * 1. ファイルアップロード
	 * 2. Excel読み込み
	 * 3. ルーブリックマスタ(m_rubric)挿入
	 * 4. 観点・尺度(t_rubric_matrix)挿入
	 * 5. Myテーマとの関連付け(t_rubric_map)
	 *
	 * ※各工程にチェックを挟み、失敗なら全て巻き戻す
	 */
	public function importrubricAction()
	{
		$parent_id = $this->getRequest()->id;

		// パラメータチェック用
		$result = array('error' => '');

		$translate = Zend_Registry::get('Zend_Translate');

		if(!empty($_FILES['file']['tmp_name']))
		{
			// 1. ファイルアップロード
			$_FILES['file']['name'] = mb_convert_encoding($_FILES['file']['name'], 'UTF-8');
			if($_FILES['file']['type'] == 'application/excel'
					|| $_FILES['file']['type'] == 'application/msexcel'
					|| $_FILES['file']['type'] == 'application/vnd.ms-excel'
					|| $_FILES['file']['type'] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
			{
				if($_FILES['file']['size'] < 1048576)
				{
					$filepath = $this->saveFile($_FILES['file'], 0);
				}
				else
				{
					$result['error'] .= sprintf($this->fmtTranslation($translate->_("アップロード上限を超えています(ファイルサイズ：%1)")), $this->formatBytes($_FILES['file']['size'], 2)) . "\n";
				}
			}
			else
			{
				$result['error'] .= $translate->_("Excelファイルを指定してください") . "\n";
			}

			if ($result['error'] != '')
			{
				echo json_encode($result);
				exit;
			}

			// 2. Excelを読み込み、連想配列でデータを受け取る
			$data = $this->readxlsx($filepath);

			// ルーブリックIDの準備
			$id = $this->getRandomId(self::PREFIX_RUBRIC);

			// 各保存する値の準備
			$flg = 0;		// 0=表の上部, 1=表の1行目, 2=表の1行目以外(表の内部), 9=表の下部
			$matrix_arr = array();

			$max_h	= 0;	// 縦最大値
			$max_v	= 0;	// 横最大値
			$min_v	= 0;	// 縦最小値
			$rank_v	= 0;	// 評点の縦位置
			$rank_h	= 0;	// 評点の横最大値
			$not_match = 0;	// 評点と尺度の数が一致しない場合は1

			$limit_h	= 15;	// 水平方向の許可最大数
			$limit_v	= 15;	// 垂直方向の許可最大数

			$rank_input	= array();	// 評点の値重複チェック用

			$emp_flg = 0;		// 入力データ数によるエラー検知用

			// マスタデータ挿入部分
			foreach($data as $y => $line)
			{
				if($flg == 0 && !empty($line['B']) && $line['B'] === '　評点→')
				{
					foreach($line as $x => $str)
					{
						// 横位置'C'以降のセルで、同じ縦位置にある尺度タイトルが空でなければチェック開始
						// パスした場合、重複チェックのために値を配列に保存する
						if(($x != 'A' && $x != 'B') && !empty($data[$y+1][$x]))
						{
							if(trim(trim($str), '　') === '')
								$result['error'] .= sprintf($this->fmtTranslation($translate->_("評点を入力してください(%1%2)")), $x, $y) . "\n";
							elseif(!is_numeric($str))
								$result['error'] .= sprintf($this->fmtTranslation($translate->_("評点は0以上100以下の数値を入力してください(%1%2)")), $x, $y) . "\n";
							elseif(0 > $str || $str > 100)
								$result['error'] .= sprintf($this->fmtTranslation($translate->_("評点は0以上100以下の数値を入力してください(%1%2)")), $x, $y) . "\n";
							else
							{
								$rank_input[] = $str;
								// 評点の横最大値を保存(更新)
								$rank_h = $this->alpha_to_numeric($x);
							}
						}
					}
					if($rank_input !== array_unique($rank_input))
						$result['error'] .= $translate->_("評点は重複のない値を入力してください");

					// 評点の縦位置を保存
					$rank_v = $y;
				}
				else if(!empty($line['A']))
				{
					if($flg == 0)
					{
						// 表部分以前
						switch($line['A'])
						{
							case '観点タイトル':
								$flg = 1;			// 表部分処理に入るフラグ
								break;
							case 'タイトル':
								$name		= $line['B'];
								break;
							case '課題文':
								$theme		= $line['B'];
								break;
						}
					}
					elseif($flg == 9)
					{
						// 表部分以後
						// ※念の為、観点タイトル名と混合しないように区別する
						switch($line['A'])
						{
							case 'メモ':
								$memo		= $line['B'];
								break;
							case '原著者':
								$original	= $line['B'];
								break;
							case '改変者':
								$editor		= $line['B'];
								break;
							case 'ライセンス':
								if(empty($line['B']))
									$result['error'] .= $translate->_("ライセンスを選択してください") . "<br>";
								else
									$license	= $this->license_convert[$line['B']];
								break;
						}
					}
				}

				if($flg == 1)
				{
					// 縦の最小値(表部分の開始行)を保存
					$min_v = $y;

					//配列の最後にポインタを移動
					end($line);
					//このときのキーを取得
					$array_last_info = each($line);
					$array_last_key = $array_last_info["key"];

					// ルーブリック表部分の先頭行処理
					foreach($line as $x => $str)
					{
						$tmp_x = $this->alpha_to_numeric($x);

						// 空文字検知で終了
						if(empty($str))
						{
							if(($this->alpha_to_numeric($x) - 1) != $rank_h)
							{
								$result['error'] .= $translate->_("評点の数と尺度の数が一致しません") . "<br>";
								$not_match = 1;
							}

							// 横の最大値を保存
							$max_h = $tmp_x;
							break;
						}

						// 配列の要素がなくなる直前の処理
						if($x == $array_last_key)
						{
							// 横の最大値(尺度の数+1)を保存
							$max_h = $tmp_x;
							if($max_h - 1 > $limit_h)
							{
								// 横(尺度の数)は最大で<$limit_h>
								$result['error'] .= sprintf($this->fmtTranslation($translate->_("尺度は%1項目以内で入力してください")), $limit_h) . "\n";
							}
						}

						// ラベル用セルはスキップ
						if($x == 'A' && $str == '観点タイトル' || $x = 'B' && preg_match('/^　尺度タイトル/', $str))
						{
							continue;
						}

						$matrix_arr[$y][$tmp_x] = array(
							'm_rubric_id'	=> $id,
							'vertical'		=> '0',
							'horizontal'	=> $tmp_x,
							'description'	=> $str
						);
					}

					$flg = 2;
				}
				elseif($flg == 2)
				{
					// ルーブリック表部分の先頭以外の処理
					foreach($line as $x => $str)
					{
						$tmp_x = $this->alpha_to_numeric($x);

						// 行ごとに空文字検知時点で整合性チェック
						// 数が合わないようであればエラー追加
						// ※観点の説明であるtmp_x == 1は空文字許可
						if($tmp_x != 1 && (empty($str) || preg_match('/^↑観点タイトルに入力すると、観点が有効になります。/', $str)))
						{
							if($tmp_x == 0)
							{
								if($y - $min_v - 1 > $limit_v)
								{
									// 終了時処理として観点の数をチェックする
									$result['error'] .= sprintf($this->fmtTranslation($translate->_("観点は%1項目以内で入力してください")), $limit_v) . "\n";
								}

								// 先頭行が以下の場合は表部分の読み込みが終了したと判断して状態遷移
								// ・空文字
								// ・サンプルルーブリックの説明セル「↑観点タイトルに入力すると、観点が有効になります。」
								$flg = 9;
							}
							elseif($max_h != $tmp_x && $not_match != 1)
							{
								// 列数の不一致
								$result['error'] .= sprintf($this->fmtTranslation($translate->_("尺度の説明を入力してください(%1%2)")), $x, $y) . "<br>";
								continue;
							}

							break;
						}

						// t_rubric_inputへの挿入データ
						$matrix_arr[$y][$tmp_x] = array(
							'm_rubric_id'	=> $id,
							'vertical'		=> $y - $min_v,
							'horizontal'	=> $tmp_x,
							'description'	=> $str
						);

						if(!empty($str))
							$emp_flg++;

						// 尺度の縦列であるセルにはランクを設定
						if($tmp_x > 1)
							$matrix_arr[$y][$tmp_x]['rank']	= $data[$rank_v][$x];
					}
				}
			}

			// 項目一切なしで登録できたため、仮に4項目以上は入力が必要とする
			// 他に既にエラーが検出されていれば、エラーとして表示しない
			if($emp_flg < 5 && $result['error'] == '')
				$result['error'] .= $translate->_("内容を入力してください") . "<br>";

			if(empty($name))
				$result['error'] .= $translate->_("タイトルを入力してください") . "<br>";

			if ($result['error'] != '')
			{
				echo json_encode($result);
				exit;
			}

			// マスタ挿入データ
			$param_master = array(
					'id'					=> $id,
					'name'					=> $name,
					'theme'					=> !empty($theme) ? $theme : '',
					'memo'					=> !empty($memo) ? $memo : '',
					'original_name_jp'		=> !empty($original) ? $original : '',
					'editor_name_jp'		=> !empty($editor) ? $editor : '',
					't_rubric_license_id'	=> !empty($license)	? $license : 1,		// 判別不能なら'すべての権利を放棄'を設定
			);

			// マッピング挿入データ
			$param_map = array(
					'parent_id'		=> $parent_id,
					'm_rubric_id'	=> $id
			);

			$db = Zend_Db_Table::getDefaultAdapter();
			$db->beginTransaction();
			try
			{
				$mRubric = new Class_Model_Tecfolio_MRubric();
				// ルーブリックマスタ挿入
				$mRubric->insert($param_master);

				$tMatrix = new Class_Model_Tecfolio_TRubricMatrix();
				// 表データ挿入
				foreach($matrix_arr as $y)
				{
					foreach($y as $line)
					{
						$tMatrix->insert($line);
					}
				}

				$tRubricMap = new Class_Model_Tecfolio_TRubricMap();
				// マッピングデータ挿入
				$tRubricMap->insert($param_map);

				$db->commit();

				echo json_encode(array('name' => $name));
				exit;
			}
			catch (Exception $e)
			{
				$db->rollback();
				echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
				exit;
			}
		}
	}

	// ルーブリック：ルーブリック描画用データの取得(ajax: 戻り値は JSONの配列)
	public function getrubricandmatrixAction()
	{
		$id = $this->getRequest()->id;

		$mRubric	= new Class_Model_Tecfolio_MRubric();
		// マスタデータ取得
		$rubric		= $mRubric->selectFromId($id);

		$rubric_arr = $rubric->toArray();

		$tMatrix	= new Class_Model_Tecfolio_TRubricMatrix();
		// 表データ取得
		$matrix		= $tMatrix->selectFromRubricId($id);

		$matrix_arr = array();
		foreach($matrix as $m)
		{
			$matrix_arr[] = $m->toArray();
		}

		echo json_encode(array('rubric' => $rubric_arr, 'matrix' => $matrix_arr));
		exit;
	}

	// 選択されたルーブリックを削除(ajax: 戻り値は JSONの配列)
	public function deleterubricAction()
	{
		$parent_id		= $this->getRequest()->id;
		$m_rubric_ids	= $this->getRequest()->removecheck;

		$tRubricMap = new Class_Model_Tecfolio_TRubricMap();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			foreach($m_rubric_ids as $id)
			{
				$tRubricMap->deleteFromParentIdAndRubricId($parent_id, $id);
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

	// 選択されたルーブリックをコピー(ajax: 戻り値は JSONの配列)
	public function copyrubricAction()
	{
		$parent_id		= $this->getRequest()->copy_mytheme_id;
		$m_rubric_ids	= $this->getRequest()->copy_id;

		$tRubricMap = new Class_Model_Tecfolio_TRubricMap();

		// 'コピー先に同一のルーブリックが存在します。'の場合は処理中断
		$multiple = $tRubricMap->selectFromParentIdAndMultipleRubricId($parent_id, $m_rubric_ids);

		$translate = Zend_Registry::get('Zend_Translate');

		if(count($multiple) > 0)
		{
			echo json_encode(array('error' => $translate->_("コピー先に同一のルーブリックが存在します")));
			exit;
		}

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			foreach($m_rubric_ids as $id)
			{
				$params = array(
					'parent_id'			=> $parent_id,
					'm_rubric_id'		=> $id,
					'original_flag'		=> '0'
				);
				$tRubricMap->insert($params);
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

	// 授業科目：メンバーと相談する
	public function insertchatsubjectAction()
	{
		$id				= $this->getRequest()->id;

		$title			= $this->getRequest()->chat_title;
		$body			= $this->getRequest()->chat_body;

		$tChatSubj		= new Class_Model_Tecfolio_TChatSubject();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// アップロードファイル挿入
			if(!empty($_FILES['addbypc']['tmp_name']))
				$result_file	= $this->insertSubjectContents($id, array($this, 'insertcontents'));

			// Amazon引用データ挿入
			if(!empty($this->getRequest()->amazon_title))
				$result_amazon	= $this->insertSubjectContents($id, array($this, 'insertamazon'));

			// Cinii引用データ挿入
			if(!empty($this->getRequest()->cinii_title))
				$result_cinii	= $this->insertSubjectContents($id, array($this, 'insertcinii'));

			// 授業科目相談データ挿入
			$params_chat = array(
					'm_subject_reg_id'	=> $id,
					'm_member_id'		=> $this->member->id,
					'm_member_name_jp'	=> $this->member->name_jp,
					'title'				=> $title,
					'body'				=> $body
			);
			$chat_id = $tChatSubj->insert($params_chat);

			// 授業科目相談-コンテンツ間マッピングデータ挿入
			if(!empty($_FILES['addbypc']['tmp_name']))
			{
				foreach($result_file['t_content_id'] as $cid)
				{
					$this->insertChatSubjectContents($chat_id, $cid);
				}
			}
			if(!empty($this->getRequest()->amazon_title))
			{
				foreach($result_amazon['t_content_id'] as $cid)
				{
					$this->insertChatSubjectContents($chat_id, $cid);
				}
			}
			if(!empty($this->getRequest()->cinii_title))
			{
				foreach($result_cinii['t_content_id'] as $cid)
				{
					$this->insertChatSubjectContents($chat_id, $cid);
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

		echo json_encode(array('success' => $id));
		exit;
	}

	// メンバーと相談するペインへの投稿内容挿入
	public function insertChatSubjectContents($id, $cid)
	{
		$tChatSubjCont	= new Class_Model_Tecfolio_TChatSubjectContents();

		$csc_id = $this->getRandomId(self::PREFIX_CHAT_SUBJ_CONT);

		$param = array(
				'id'				=> $csc_id,
				't_chat_subject_id'	=> $id,
				't_content_id'		=> $cid
		);
		$tChatSubjCont->insert($param);
	}

	// 投稿とコンテンツ一覧取得
	public function getchatsubjectAction()
	{
		$id				= $this->getRequest()->id;

		$tChatSubject	= new Class_Model_Tecfolio_TChatSubject();
		$chats			= $tChatSubject->selectFromSubjectId($id, $this->member->id, $this->member->staff_no);

		// 投稿内容をベースに、複数ファイルが紐づく可能性がある
		// 投稿内容は一意である必要があるため、添字はIDを代入する
		foreach($chats as $chat)
		{
			if(empty($chat_arr[$chat->id]))
			{
				$chat_arr[$chat->id] = $chat->toArray();
			}
			$chat_arr[$chat->id]['contents'][] = array(
					'content_file_id'		=> $chat->content_files_id,
					'content_file_name'		=> $chat->content_files_name,
					'content_file_type'		=> $chat->content_files_type,
					'ref_title'				=> $chat->ref_title,
					'ref_url'				=> $chat->ref_url,
					'ref_class'				=> $chat->ref_class,
					'display'				=> $chat->display
			);
		}
		// このままではID順となるため、逆順にソートし、添字を振り直す
		// (結果としては投稿日降順となる)
		$chat_arr_reversed = array();

		if(!empty($chat_arr))
		{
			krsort($chat_arr);

			foreach($chat_arr as $chat)
			{
				$chat_arr_reversed[] = $chat;
			}
		}

		echo json_encode( array('chat_log' => $chat_arr_reversed ));
		exit;
	}

	// 投稿削除
	public function deletechatsubjectAction()
	{
		$id				= $this->getRequest()->id;

		$tChatSubject	= new Class_Model_Tecfolio_TChatSubject();

		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			$tChatSubject->deleteFromId($id);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();
			echo json_encode(array('error' => 'SQLエラー' . $e->getMessage()));
			exit;
		}

		echo json_encode( array('success' => $id ));
		exit;
	}


//	保留
// 	public function planningAction()
// 	{
// 		$this->view->assign('subtitle', '計画と記録');

// 		$html = $this->view->render('tecfolio/common/planning.tpl');
// 		$this->getResponse()->setBody($html);
// 	}


// 	public function getplanningAction()
// 	{
// 		$id		= $this->getRequest()->id;

// 		$tJikanReg	= new Class_Model_Tecfolio_TJyugyoJikanwariRegistered();
// 		$planning = $tJikanReg->selectFromNendoAndKnrno($this->nendo, $this->jyu_knr_no);

// 		$planning_arr = array();
// 		foreach($planning as $plan)
// 		{
// 			$plannig_arr[] = $plan->toArray();
// 		}

// 		echo json_encode(array('plannning' => $planning_arr, 'id' => $id));
// 		exit;
// 	}
}





