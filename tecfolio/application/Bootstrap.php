<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	// オートローダ登録
	protected function _initResourceAutoLoader()
	{
		$resourceLoader = new Zend_Loader_Autoloader_Resource(
			array(
				'basePath'  => APPLICATION_PATH,
				'namespace' => 'Class',
			)
		);

		$resourceLoader->addResourceTypes(
			array(
				// ベースモデルディレクトリ
				'table' => array(
						'namespace' => 'Model',
						'path'    => 'models',
				),
			),
			array(
				// TECfolioモデルディレクトリ
				'table' => array(
						'namespace' => 'Model',
						'path'    => 'models/Tecfolio',
				)
			)
		);

		return $resourceLoader;
	}

	// プラグイン登録
	protected function _initRegisterPlugins()
	{
		// frontcontrollerのインスタンスを作成
		if(!$this->hasResource('frontController'))
			$this->bootstrap('frontController');

		// frontcontrollerのインスタンス取得
		$front = $this->getResource('frontcontroller');

		// プラグインを登録
		//

		// 認証プラグイン
		if (!defined('CMDLINE'))
		{
			require_once('controllers/plugins/AuthPlugin.class.php' );
			$front->registerPlugin(new Class_Plugin_Auth());
		}
	}

	// ヘルパー登録
	protected function _initHelpers()
	{
		Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/controllers/helpers', 'Class_Action_Helper');
	}

	// DB接続設定
	protected function _initDb()
	{
		try
		{
			$options = new Zend_Config($this->getOptions());

			// 読み込むDBをアプリケーションタイプに合わせて変更
			if (APPLICATION_TYPE == 'twc')
				$db_config		= $options->dbtwc;
			else
				$db_config		= $options->dbkwl;

			$dbConect = Zend_Db::factory($db_config->adapter, $db_config->params);
			Zend_Registry::set('db', $dbConect);
			Zend_Db_Table::setDefaultAdapter($dbConect);
		}
		catch(Exception $e)
		{
			// Bootstrap.php内ではExceptionを表示させる機能は読み込まれていないため、ここに書きます。
			echo '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8" />';
			echo '<title>エラー</title></head><body>';
			if ('production' == APPLICATION_ENV)
			{
				echo '<h1>エラーです。</h1>';
			}
			else
			{
				echo '<h1>データベースに接続できません。</h1>';
				echo '<h3>Message</h3>';
				echo $e->getMessage();
				echo '<h3>File</h3>';
				echo $e->getFile();
				echo '<h3>Line</h3>';
				echo $e->getLine();
				echo '<h3>Trace</h3>';
				echo '<pre>';
				echo $e->getTraceAsString();
				echo '</pre>';
			}
			echo '</body></html>';
			exit;
		}
	}

	// View初期化(Smarty連携)
   	protected function _initView()
	{
		$options = new Zend_Config($this->getOptions());
		$view_config	= $options->view->toArray();
		$smarty_config	= $options->view->smarty->toArray();

		// Smarty設定(View)
		require_once('Smarty/Smarty.class.php' );
		require_once('smarty/Zend_View_Smarty.class.php');
		$view = new Zend_View_Smarty($view_config['scriptPath'], $smarty_config);
		$view->addHelperPath($view_config['helperPath']);	// ヘルパーパス追加

		/*
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view)
			->setViewBasePathSpec($view_config['scriptPath'])
			->setViewScriptPathSpec(':controller/:action.:suffix')
			->setViewScriptPathNoControllerSpec(':action.:suffix')
			->setViewSuffix('tpl');
		*/

		// ViewRendererを追加する
		$objViewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$objViewRenderer->setView($view)
			->setViewBasePathSpec($view_config['scriptPath'])
			->setViewScriptPathSpec(':controller/:action.:suffix')
			->setViewScriptPathNoControllerSpec(':action.:suffix')
			->setViewSuffix('tpl');

		Zend_Controller_Action_HelperBroker::addHelper($objViewRenderer);
	}

	// Zendアプリケーション共通変数設定
	protected function _initAppVar()
	{
		// 現在の時間
		Zend_Registry::set('nowdatetime', date("Y-m-d H:i:s", time()));
		Zend_Registry::set('nowdate', date("Y-m-d", time()));
	}
	
	protected function _initLocale()
	{
		// ロケールの設定
		$locale_list = array(
				1 => 'ja',
				2 => 'en'
		);
		
		// ロケールの設定
		$member = Zend_Auth::getInstance()->getIdentity();
		
		if(!empty($member->languages))
			$loc = $locale_list[$member->languages];
		else
			$loc = $locale_list[1];
		
		Zend_Locale::setDefault($loc);
		
		$objLocale = new Zend_Locale($loc);
		Zend_Registry::set('Zend_Locale', $objLocale);
		
		// 翻訳アダプタの設定
		$translate = new Zend_Translate(
				array(
						'adapter'	=> 'gettext',
						'content'	=> APPLICATION_PATH . '/languages/files/lang_ja.mo',
						'locale'	=> 'ja',
				)
		);
		$translate->addTranslation(
				array(
						'content' => APPLICATION_PATH . '/languages/files/lang_en.mo',
						'locale' => 'en'
				)
		);
		$translate->setLocale($loc);
		
		Zend_Registry::set('Zend_Translate', $translate);
		
		return $objLocale;
	}
	
	// ルーティング
	protected function _initRoutes()
	{
		$front = Zend_Controller_Front::getInstance()->getRouter();
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini');
		$front->addConfig($config, 'routes');
	}
}
