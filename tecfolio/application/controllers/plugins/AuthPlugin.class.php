<?php

require_once('UnityAuthAdapter.php' );

// 認証プラグイン
class Class_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
	public function dispatchLoopStartup($req)
	{
		// Zend_Auth オブジェクト取得
		$auth = Zend_Auth::getInstance();

		if (!$auth->hasIdentity())
		{	// 未認証
			if ($req->getModuleName() != 'default' ||
				$req->getControllerName() != 'auth' ||
				$req->getActionName() != 'process')
			{	// 現在のリクエストが default/auth/process 以外の場合のみ処理を実行

				// 統合認証で認証済みかをチェック
				$uid = getenv('HTTP_UID');
				if (!empty($uid))
				{	// 統合認証済み
					// 認証アダプタ作成
					$adapter = new UnityAuthAdapter($uid);

					// 認証
					$result = $auth->authenticate($adapter);
					if ($result->isValid())
						$auth->getStorage()->write($adapter->getMemberInfo());	// ユーザ情報をセッションへ保存
					else
					{
						// もともとのリクエストをセッションへ保存
						$sess = new Zend_Session_Namespace('cScQlPsMuqoKayhj');
						$sess->currentModule		= $req->getModuleName();
						$sess->currentController	= $req->getControllerName();
						$sess->currentAction		= $req->getActionName();
						
						// URLをセッションへ保存する
						$sess->currentURL			= (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

						// 強制的に default/auth/index を設定
						$req->setModuleName('default');
						$req->setControllerName('auth');
						$req->setActionName('index');
					}
				}
				else
				{	// 統合認証で認証されてない(独自認証へ)
					
					// Authコントローラの場合、URLを保存しない
					// 		→特定URLへのアクセスからauth/indexへリダイレクト後、
					//		学外関係者画面からのログインでも、元のURLへと飛ぶ
					if($req->getControllerName() != 'auth')
					{
						// もともとのリクエストをセッションへ保存
						$sess = new Zend_Session_Namespace('cScQlPsMuqoKayhj');
						$sess->currentModule		= $req->getModuleName();
						$sess->currentController	= $req->getControllerName();
						$sess->currentAction		= $req->getActionName();
						// URLをセッションへ保存する
						$sess->currentURL			= (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

						// 本認証以外は強制的に仮認証へ
						// 強制的に default/auth/index を設定
						$req->setModuleName('default');
						$req->setControllerName('auth');
						$req->setActionName('index');
					}
				}
			}
		}
	}

}

