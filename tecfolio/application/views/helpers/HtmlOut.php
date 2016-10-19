<?php

// 文字列フィールドをHTML出力用に加工
class Zend_View_Helper_HtmlOut
{
	public $view;
	public function setView(Zend_View_Interface $view)
	{
		$this->view = $view;
	}

	public function escape($text)
	{
		return str_replace("\n", '<br />', htmlspecialchars($text));
	}

	public function htmlOut($text)
	{
		return str_replace("\n", '<br />', htmlspecialchars($text));
	}
}