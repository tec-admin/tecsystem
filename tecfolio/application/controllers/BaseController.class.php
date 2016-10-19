<?php

class BaseController extends Zend_Controller_Action
{
	public $moduleName;
	public $controllerName;
	public $actionName;
	public $baseurl;
	public $member;

	public $profile;
	public $locale;


    public function preDispatch()
    {
		// リクエストをController、Viewでも使えるように設定する
		$req = $this->getRequest();
		$this->moduleName		= $req->getModuleName();
		$this->controllerName	= $req->getControllerName();
		$this->actionName		= $req->getActionName();
		$this->view->assign('moduleName',		$this->moduleName);
		$this->view->assign('controllerName',	$this->controllerName);
		$this->view->assign('actionName',		$this->actionName);

        // ベースURL設定
        $this->serverurl = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') .  $_SERVER['SERVER_NAME'];
		$this->view->assign('serverurl', $this->serverurl);
		$this->view->assign('phpself', $_SERVER['PHP_SELF']);
		$this->baseurl = substr($_SERVER['PHP_SELF'], 0, -(strlen('index.php') + 1));
		$this->view->assign('baseurl', $this->baseurl);

		// ユーザ情報
		$this->member = Zend_Auth::getInstance()->getIdentity();
		$this->view->assign('member', $this->member);

		// アプリケーションタイプ
		$this->view->assign('apptype', APPLICATION_TYPE);

		// 今日の日付（システムから参照）
		$this->view->assign('nowdate', Zend_Registry::get('nowdate'));
		$this->view->assign('nowdatetime', Zend_Registry::get('nowdatetime'));

		// シフト入力ON/OFF
		$this->view->assign("disable_shiftinput", false);

		// ビューヘルパーをアサインする
		$this->view->assign("vhHtmlOut", $this->view->getHelper("htmlOut"));
		$this->view->assign("vDate", $this->view->getHelper("dateOut"));
		$this->view->assign("vByte", $this->view->getHelper("byteOut"));

		$frontController = Zend_Controller_Front::getInstance();

		$config = $frontController->getParam("bootstrap")->getOptions();
		$this->logpath = $config['trace']['log']['path'];
		$this->logname = $config['trace']['log']['name'];

		$actionStack = $frontController->getPlugin('Zend_Controller_Plugin_ActionStack');

		$reserveid = $this->getRequest()->reserveid;

		// 予約IDが存在しない場合(主に送信メールのURLから画面遷移時)
		if(!empty($this->member) && !empty($reserveid))
		{
			$tReserves = new Class_Model_TReserves();
			$reserve = $tReserves->selectFromId($reserveid);
			if(empty($reserve))
			{
				$this->_redirect($this->controllerName . "/error");
			}
		}
		
		// プロフィールの取得
		$tProfiles = new Class_Model_Tecfolio_TProfiles();
		$profile = array();
		if(!empty($this->member))
			$profile = $tProfiles->selectFromId($this->member->id);

		$this->view->assign('profile', $profile);

		// 現在のロケールの引用
		if(Zend_Registry::isRegistered('Zend_Locale'))
			$this->locale = Zend_Registry::get('Zend_Locale');
		else
			$this->locale = 'ja';

		$this->view->assign('locale', $this->locale);

		// usage ex. $this->_helper->layout()->disableLayout();
		Zend_Controller_Action_HelperBroker::addHelper(new Zend_Layout_Controller_Action_Helper_Layout);
	}
	
	public function fmtTranslation($text)
	{
		return preg_replace('/(\%\d)/', '$1\$s', $text);
	}
	

	// 勤務業務管理・学期前シフト管理用
	public function getDowArray()
	{
		if(!Zend_Registry::isRegistered('Zend_Locale') || Zend_Registry::get('Zend_Locale') == 'ja')
		{
			$dowarray = array('月','火','水','木','金');
		}
		else
		{
			$dowarray = array('Mon','Tue','Wed','Thu','Fri');
		}
	
		return $dowarray;
	}

	// 個人設定テーブルの言語フィールドを更新(ajax: 戻り値は JSONの配列)
	public function updatelanguagesAction()
	{
		$id			= $this->member->id;
		$language	= $this->getRequest()->language;

		$tProfiles = new Class_Model_Tecfolio_TProfiles();
		$profiles = $tProfiles->selectProfileFromId($id);

		$params = array(
				'languages'		=> $language,
		);
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
			// 追加/変更
			if(empty($profiles))
			{
				$params['m_member_id'] = $id;

				$tProfiles->insert($params);
			}
			else
			{
				$tProfiles->updateFromId($id, $params);
			}

			// セッション内、言語設定の更新
			$auth			= Zend_Auth::getInstance();
			$obj			= $this->member;
			$obj->languages	= $language;

			$auth->getStorage()->write($obj);

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

	public function logWrite($message, $level=Zend_Log::DEBUG)
	{
		// Zend_Log::EMRG
		// Zend_Log::ALERT
		// Zend_Log::CRIT
		// Zend_Log::ERR
		// Zend_Log::WARN
		// Zend_Log::NOTICE
		// Zend_Log::INFO
		// Zend_Log::DEBUG
		$frontController = Zend_Controller_Front::getInstance();
		$config = $frontController->getParam("bootstrap")->getOptions();
		$this->logpath = $config['trace']['log']['path'];
		$this->logname = $config['trace']['log']['name'];
		$logFile = $this->logpath . '/' . strtr($this->logname, array('#DT#' => strftime('%d')));
		$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
		$logger->log($message, $level);
	}

	public function loginLogWrite($message, $level=Zend_Log::INFO)
	{
		$frontController = Zend_Controller_Front::getInstance();
		$config = $frontController->getParam("bootstrap")->getOptions();
		$this->logpath = $config['trace']['log']['path'];
		$this->logname = "login_#DT#.log";

		// ログは一週間ごと・日曜日の日付のファイルにまとめる
		$today = strtotime(Zend_Registry::get('nowdate'));
		$w = date("w", $today);
		$beginning = date('Ymd', strtotime("-{$w} day", $today));

		$logFile = $this->logpath . '/' . strtr($this->logname, array('#DT#' => $beginning));
		$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
		$logger->log($message, $level);
	}

	// イメージの表示
	public function imgAction()
	{
		// 最大サイズ
		$maxwidth	= $this->getRequest()->maxwidth;
		if (!isset($maxwidth))
			$maxwidth = 0;
		$maxheight	= $this->getRequest()->maxheight;
		if (!isset($maxheight))
			$maxheight = 0;

		// コンテンツ取得
		$id = $this->getRequest()->id;
		if (!isset($id) || $id == 0)
		{
			$filename = "/home/tecfolio/public_html/image/noimage.gif";
			$handle = fopen( $filename, "rb" );
			$data = fread( $handle, filesize($filename) ); // filesize()関数でファイルサイズを指定
			fclose($handle);
		}
		else
		{
			$db = Zend_Db_Table::getDefaultAdapter();	// DBアダプタ取得
			$stt = $db->query('SELECT type,data FROM t_files WHERE id= ?', array($id));

			$stt->bindColumn('type', $type, Zend_Db::PARAM_STR);
			$stt->bindColumn('data', $data, Zend_Db::PARAM_STR);

			if (!$stt->fetch(Zend_Db::FETCH_BOUND))
				exit;
		}

		if (!empty($data))
		{
			// イメージのリサイズとPNGへの変換を行い出力
			$im = ImageCreateFromString($data);
			if ($im != FALSE)
			{
				$newwidth	= $width	= imagesx($im);
				$newheight	= $height	= imagesy($im);

				if ($maxwidth != 0 && $maxheight != 0)
					list($newwidth, $newheight) = $this->calcViewSize($width, $height, $maxwidth, $maxheight);

				// リサイズ
				$newim = imagecreatetruecolor($newwidth, $newheight);
				imagecopyresized($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
				ImageDestroy($im);

				Header("Content-type: image/png");
				ImagePng($newim);
				ImageDestroy($newim);
			}
		}

		exit();
	}

	// 表示用サイズの計算
	protected function calcViewSize($width , $height, $maxwidth, $maxheight)
	{
		if ($width > $maxwidth)
		{
			$height	= (int)($maxwidth * $height / $width);
			$width	= $maxwidth;
		}

		if ($height > $maxheight)
		{
			$width	= (int)($maxheight * $width / $height);
			$height	= $maxheight;
		}

		return array($width, $height);
	}

	// その他のファイルタイプ（通常ダウンロード）
	public function downloadAction()
	{
		$id = $this->getRequest()->id;
		if (isset($id) && $id > 0)
		{
			$db = $db = Zend_Db_Table::getDefaultAdapter();	// DBアダプタ取得
			$stt = $db->query('SELECT data,type,name,filesize FROM t_files WHERE id= ?', array($id));

			//$stt->bindColumn('data', $data, Zend_Db::PARAM_STR);
			$stt->bindColumn('data', $data, Zend_Db::PARAM_LOB);
			$stt->bindColumn('type', $type, Zend_Db::PARAM_STR);
			$stt->bindColumn('name', $name, Zend_Db::PARAM_STR);
			$stt->bindColumn('filesize', $filesize, Zend_Db::PARAM_INT);

			if ($stt->fetch(Zend_Db::FETCH_BOUND))
			{
				header('Content-Type: ' . $type);
				header('Content-Disposition: attachment; filename="' . $name . '"');
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

	// 添付ファイルの削除
	public function deletefile($class, $redirectpath = null)
	{
		$this->_helper->AclCheck('Management', 'Edit');

		$id = $this->getRequest()->id;
		if (!empty($id) && $id != 0)
		{
			$model = $class->selectFromId($id);
			if (!empty($model) && $model->t_file_id != 0)
			{
				$tFiles = new Class_Model_TFiles();
				$tFiles->getAdapter()->beginTransaction();
				try
				{
					// モデル本体のファイル情報を削除
					$params = array("t_file_id" => 0);
					$class->updateFromId($id, $params);

					// 添付ファイルを削除
					$tFiles->deleteFromId($model->t_file_id);

					$tFiles->getAdapter()->commit();
				}
				catch (Exception $e)
				{
					$tFiles->getAdapter()->rollback();
					die($e->getMessage());
				}
			}
		}

		if (!empty($redirectpath))
			$this->_redirect($redirectpath);
	}

	// ローカルナビのリンクを作成する
	protected function setLocalNavigation($naviarray = array())
	{
		if (count($naviarray) <= 0)
			return NULL;

		$localnavi = "";
		foreach ($naviarray as $navi)
		{
			if ($localnavi != "")
				$localnavi .= '　＞　';

			if ($navi['url'] == '')
				$localnavi .= $navi['name'];
			else
				$localnavi .= '<a href="' . $navi['url'] . '">' . $navi['name'] . '</a>';
		}
		$localnavi .= '　＞　' . $this->view->__get('title');

		$this->view->assign('localnavi', $localnavi);
	}

	// コース情報を取得、設定する
	protected function setCourseMenu($courseid)
	{
		// コース情報
		$tCourses = new Class_Model_TCourses();
		$this->course = $tCourses->selectFromId($courseid);
		$this->view->assign('course', $this->course);

		// 自身が所属しているグループを取得
		$tMemberGroups = new Class_Model_TMemberGroups();
		$this->groups = $tMemberGroups->selectFromCourseIdAndMemberId($courseid, Zend_Auth::getInstance()->getIdentity()->id);
		$this->view->assign('groups', $this->groups);
	}

}

