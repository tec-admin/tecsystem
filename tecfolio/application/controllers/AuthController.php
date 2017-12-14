<?php
require_once('BaseController.class.php');
require_once('plugins/LdapAuthAdapter.php');
require_once('plugins/OrgAuthAdapter.php');

// 認証画面
class AuthController extends BaseController
{
	public function init()
	{
		// ここにコントローラの初期化処理を記述する
	}

	// 認証フォーム
	public function indexAction()
	{
	}

	// 外部ログイン画面
	public function loginAction()
	{
	}
	
	public function createLog($user, $message)
	{
		$message = '[' . $_SERVER["REMOTE_ADDR"] . '][' . $user . '] : ' . $message;
		BaseController::loginLogWrite($message);
	}
	
	// ロールが未設定(空)の場合
	public function errorAction()
	{
		$info = Zend_Auth::getInstance()->getIdentity();
		
		Zend_Auth::getInstance()->clearIdentity();	// 認証情報破棄
		
		$errorMessage = 'このIDでのログインは許可されていません';
		
		if(empty($info->type) || $info->type === 'ldap')
		{
			$this->createLog((!empty($info->id) ? $info->id : ''), 'LDAP login error : No Rolls');
			
			$this->view->assign('error', $errorMessage);
			$this->_forward('index', 'auth');
		}
		else
		{
			$this->createLog((!empty($info->id) ? $info->id : ''), 'Authentication error : No Rolls');
			
			$this->view->assign('error', $errorMessage);
			$this->_forward('login', 'auth');
		}
	}

	// 認証プロセス
	public function processAction()
	{
		Zend_Auth::getInstance()->clearIdentity();	// 認証情報破棄
		
		$err = $this->getRequest()->error;
		
		$errorMessage = 'IDもしくはパスワードに誤りがあります';

		// Zend_Authオブジェクトのインスタンス取得
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity())
		{	// 認証済み(プラグイン(plugins/AuthPlugin)でチェック済みなので auth/process が直接呼び出された場合のみ)
			$this->_redirect('/');
		}
		else
		{	// 未認証
			// フォームからのリクエスト取得
			$req = $this->getRequest();
			$account	= $req->getPost('account');
			$password	= $req->getPost('password');

			// LDAP認証を行う
//			if (APPLICATION_TYPE == '0')
			if (APPLICATION_TYPE == 'kwl')
			{
				// LDAP認証追加 ここから
				$adapter = new LdapAuthAdapter($account, $password);
				$options = array
				(
					'server1' => array(
						'host'				=> 'xxxx.xxxxx.xx.jp',
						'port'				=> xxx,
						'baseDn'			=> 'dc=xxxxxxxx,dc=xx,dc=xx',
						'username'			=> 'cn=xxxxx,ou=xxxxx,dc=xxxx,dc=xx,dc=jp',
						'password'			=> 'xxxxxxxx',
						'bindRequiresDn'	=> true
					)
				);
				$adapter1 = new Zend_Auth_Adapter_Ldap($options,$account,$password);
				// 認証
				$result = $auth->authenticate($adapter1);
				// LDAP認証追加 ここまで
				if ($result->isValid())
				{
					$adapter->authenticate();
					$stdObj = $adapter->getMemberInfo();
					$auth->getStorage()->write($stdObj);	// ユーザ情報をセッションへ保存

					// セッションに保存済みのリクエストを取得
					$sess = new Zend_Session_Namespace('cScQlPsMuqoKayhj');
					$action		= $sess->currentAction;
					$controller	= $sess->currentController;
					$module		= $sess->currentModule;
					
					// 保存済みのURLを取得し、リダイレクトする
					$url		= $sess->currentURL;

					// 保存済みリクエスト削除
					$sess->currentAction		= NULL;
					$sess->currentController	= NULL;
					$sess->currentModule		= NULL;
					
					$this->createLog($account, 'LDAP login succeeded');
					
					if($action === 'index')
					{
						// 権限を判断してリダイレクト
						$this->redirectRoles($stdObj->roles);
					}
					else
					{
						// 元のリクエスト先にリダイレクト
						$this->_redirect($url);
					}
				}
				else
				{
					$this->createLog($account, 'LDAP login failed : Incorrect ID or Password');
					
			        $this->view->assign('error', $errorMessage);
					$this->_forward('index', 'auth');
				}
				return;
			}
			
			// 認証
			if (empty($account) || empty($password))
			{
		        $this->view->assign('error', $errorMessage);
				$this->_forward('index', 'auth');
			}
			else
			{
				$adapter = new OrgAuthAdapter($account, $password);
				
				//$result = $auth->authenticate($a_db);
				$result = $adapter->authenticate();
				if ($result->isValid())
				{	// 成功
					// パスワード以外のユーザ情報をセッションへ保存
					$stdObj = $adapter->getMemberInfo();
					$auth->getStorage()->write($stdObj);	// ユーザ情報をセッションへ保存

					// セッションに保存済みのリクエストを取得
					$sess = new Zend_Session_Namespace('cScQlPsMuqoKayhj');
					$action		= $sess->currentAction;
					$controller	= $sess->currentController;
					$module		= $sess->currentModule;
					
					// 保存済みのURLを取得し、リダイレクトする
					$url		= $sess->currentURL;
					
					// 保存済みリクエスト削除
					$sess->currentAction		= NULL;
					$sess->currentController	= NULL;
					$sess->currentModule		= NULL;
					$sess->currentURL			= NULL;
					
					if($action === 'index')
					{
						// 権限を判断してリダイレクト
						$this->redirectRoles($stdObj->roles);
					}
					else
					{
						// 元のリクエスト先にリダイレクト
						$this->_redirect($url);
					}
				}
				else
				{	// 失敗
			        $this->view->assign('error', $errorMessage);
					$this->_forward('index', 'auth');
				}
			}
		}
	}
	
	public function orgprocessAction()
	{
		Zend_Auth::getInstance()->clearIdentity();	// 認証情報破棄
		
		$errorMessage = 'IDもしくはパスワードに誤りがあります';
	
		// Zend_Authオブジェクトのインスタンス取得
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity())
		{	// 認証済み(プラグイン(plugins/AuthPlugin)でチェック済みなので auth/process が直接呼び出された場合のみ)
			$this->_redirect('/');
		}
		else
		{	// 未認証
			// フォームからのリクエスト取得
			$req = $this->getRequest();
			$account	= $req->getPost('account');
			$password	= $req->getPost('password');
	
		// 認証
			if (empty($account) || empty($password))
			{
				$this->createLog((!empty($account) ? $account : ''), 'Authentication failed : Incorrect ID or Password');
				
		        $this->view->assign('error', $errorMessage);
				$this->_forward('login', 'auth');
			}
			else
			{
				$adapter = new OrgAuthAdapter($account, $password);
				
				//$result = $auth->authenticate($a_db);
				$result = $adapter->authenticate();
				if ($result->isValid())
				{	// 成功
					// パスワード以外のユーザ情報をセッションへ保存
					$stdObj = $adapter->getMemberInfo();
					$auth->getStorage()->write($stdObj);	// ユーザ情報をセッションへ保存

					// セッションに保存済みのリクエストを取得
					$sess = new Zend_Session_Namespace('cScQlPsMuqoKayhj');
					$action		= $sess->currentAction;
					$controller	= $sess->currentController;
					$module		= $sess->currentModule;
					
					// 保存済みのURLを取得し、リダイレクトする
					$url		= $sess->currentURL;
					
					// 保存済みリクエスト削除
					$sess->currentAction		= NULL;
					$sess->currentController	= NULL;
					$sess->currentModule		= NULL;
					$sess->currentURL			= NULL;
					
					$this->createLog($account, 'Authentication succeeded');
					
					if($action === 'index' || $action !== 'error')
					{
						// 権限を判断してリダイレクト
						$this->redirectRoles($stdObj->roles);
					}
					else
					{
						// 元のリクエスト先にリダイレクト
						$this->_redirect($url);
					}
				}
				else
				{	// 失敗
					$this->createLog($account, 'Authentication failed : Incorrect ID or Password');
					
			        $this->view->assign('error', $errorMessage);
					$this->_forward('login', 'auth');
				}
			}
		}
	}

	// ログアウト
	public function logoutAction()
	{
		// ログアウト後のリダイレクト先を分岐する
		$type = Zend_Auth::getInstance()->getIdentity()->type;
		
		Zend_Auth::getInstance()->clearIdentity();	// 認証情報破棄
		Zend_Session::destroy();	// セッション情報削除
		
		if($type === 'org')
			$this->_redirect('auth/login');
		else
			$this->_redirect('/');			// トップページへリダイレクト（結果、ログイン画面へ戻る）
	}

	// 権限を判断してリダイレクト(2014/04/18 S.Satake)
	public function redirectRoles($roles)
	{
		$role = explode(',', $roles);
		if (count($role) > 1)
		{	// 複数のロール
			$this->_redirect('seltop/index');
		}
		else
		{	// 単一ロール
			if (strstr($roles, 'Student'))
				$this->_redirect('labo/index');
			elseif (strstr($roles, 'Staff'))
				$this->_redirect('staff/index');
			elseif (strstr($roles, 'Administrator'))
				$this->_redirect('admin/index');
			elseif (strstr($roles, 'Professor'))
				$this->_redirect('tecfolio/professor/file');
			else
			{
				$this->_redirect('auth/error');
			}
		}
	}
}

