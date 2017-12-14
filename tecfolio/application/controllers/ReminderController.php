<?php

// SMTPライブラリ
require_once('sendmail.php');

class ReminderController extends Zend_Controller_Action
{
	public function indexAction()
	{
		exit;
	}

	// 送信日時をチェックし、現在の時刻を過ぎていれば送信
	public function checkAction()
	{
		// 特定のステータス（未送信など）をすべて取得
		$tReminders = new Class_Model_TReminders();
		$reminders = $tReminders->selectFromStatus(1);

		// メール送信オブジェクト作成
		$smtp = & new Sendmail();

		// 個別送信
		$mMembers = new Class_Model_MMembers();
		foreach ($reminders as $reminder)
		{
			if (strtotime($reminder->senddatetime) <= strtotime(Zend_Registry::get('nowdatetime')))
			{	// 指定日時を過ぎているので送信する
				// 送信元の指定があれば設定
				if ($reminder->m_member_id_from != 0)
				{
					$member = $mMembers->selectFromId($reminder->m_member_id_from);
					$smtp->from($member->email);
				}
				else	// デフォルト
					$smtp->from('xxxxxxxx@xxxxxxx.xx.jp');


				if ($reminder->m_member_id_to != 0)
				{
					$member = $mMembers->selectFromId($reminder->m_member_id_to);
					if (!empty($member->email))
						$smtp->easyMail($member->email, $reminder->subject, $reminder->body);
				}

				$tReminders->getAdapter()->beginTransaction();
				try
				{
					$tReminders->updateFromId($reminder->id, array('status' => 0));	// ステータスを送信済みへ変更
					$tReminders->getAdapter()->commit();
				}
				catch (Exception $e)
				{
					$tReminders->getAdapter()->rollback();
					$this->logWrite('SQLエラー:' . $e->getMessage());
					exit;
				}
			}
		}
		exit;
	}

	public function logWrite($message, $level=Zend_Log::DEBUG)
	{
		$frontController = Zend_Controller_Front::getInstance();
		$config = $frontController->getParam("bootstrap")->getOptions();

		$this->logpath = $config['trace']['log']['path'];
		$this->logname = $config['trace']['log']['name'];

		// Zend_Log::EMRG
		// Zend_Log::ALERT
		// Zend_Log::CRIT
		// Zend_Log::ERR
		// Zend_Log::WARN
		// Zend_Log::NOTICE
		// Zend_Log::INFO
		// Zend_Log::DEBUG
		$logFile = $this->logpath . '/' . strtr($this->logname, array('#DT#' => strftime('%d')));
		$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
		$logger->log($message, $level);
	}
}
