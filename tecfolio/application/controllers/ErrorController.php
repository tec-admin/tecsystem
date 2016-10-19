<?php

class ErrorController extends Zend_Controller_Action
{

	public function errorAction()
	{
		$errors = $this->_getParam('error_handler');

		if (!$errors || !$errors instanceof ArrayObject) {
			$this->view->message = 'You have reached the error page';
			return;
		}

		switch ($errors->type)
		{
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				// 404 error -- controller or action not found
				$this->getResponse()->setHttpResponseCode(404);
				$priority = Zend_Log::NOTICE;
				$this->view->message = 'ページが存在しません';
				break;
			default:
				// application error
				$this->getResponse()->setHttpResponseCode(500);
				$priority = Zend_Log::CRIT;
				$this->view->message = '予期せぬエラーが発生しました。';
				break;
		}

		// 例外があれば設定
		if ($errors->exception)
		{
			$this->view->exception = true;	// 例外情報あり

			// 各種例外メッセージ設定
			$this->view->exceptionMessage		= $errors->exception->getMessage();
			$this->view->exceptionTraceString	= $errors->exception->getTraceAsString();
			$this->view->requestParams			= var_export($errors->request->getParams(), true);
		}

		// ログ出力
		if ($log = $this->getLog())
		{
			$log->setTimestampFormat("y/m/d-h:i:s");
			$log->setEventItem("user", "satake");
			$log->log('----- Error start -----', $priority);
			$log->log($errors->exception->getMessage(), $priority);
			$log->log($errors->exception->getTraceAsString(), $priority);
			$log->log(var_export($errors->request->getParams(), true), $priority);
			$log->log('----- Error end -----', $priority);
		}

		/*
		// Log exception, if logger available
		if ($log = $this->getLog()) {
			$log->log($this->view->message, $priority, $errors->exception);
			$log->log('Request Parameters', $priority, $errors->request->getParams());
		}
		*/

		// conditionally display exceptions
		if ($this->getInvokeArg('displayExceptions') == true)
		{
			$this->view->exception = $errors->exception;
		}

		$this->view->request   = $errors->request;
	}

	public function getLog()
	{
		$bootstrap = $this->getInvokeArg('bootstrap');
		if (!$bootstrap->hasResource('Log')) {
			return false;
		}
		$log = $bootstrap->getResource('Log');
		return $log;
	}


}

