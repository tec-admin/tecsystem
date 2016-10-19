<?php

// 文字列フィールドをHTML出力用に加工
class Zend_View_Helper_DateOut
{
	public $view;
	public $dow = array(
			0 => '日',
			1 => '月',
			2 => '火',
			3 => '水',
			4 => '木',
			5 => '金',
			6 => '土',
		);
	public function setView(Zend_View_Interface $view)
	{
		$this->view = $view;
	}

	// @param $force	trueなら強制的に日本語表記にする
	// @param $title	trueなら英語表記の月と曜日を略さずに表記する
	// @param $shift	trueなら英語の場合に末尾のカンマ等を消さない(この次にA 11:30-12:10のようなシフトの表示が続く)
	public function dateFormat($date, $format="Y年m月d日(wj) H:i", $force=false, $title=false, $shift=false)
	{
		if(empty($date)) return null;
		
		if(!Zend_Registry::isRegistered('Zend_Locale') || Zend_Registry::get('Zend_Locale') == 'ja' || $force == true)
		{
			$format = str_replace('wj', $this->dow[date('w', strtotime($date))], $format);
		}
		else
		{
			$tmp = "";
			
			if(preg_match('/wj/', $format)) {
				if($title)	$tmp .= "l, ";
				else		$tmp .= "D, ";
			}
			if(preg_match('/m/', $format)) {
				if($title)	$tmp .= "F ";
				else		$tmp .= "M ";
			}
			if(preg_match('/d/', $format))		$tmp .= "d, ";
			if(preg_match('/Y/', $format))		$tmp .= "Y, ";
			if(preg_match('/H:i/', $format))	$tmp .= "H:i";
			
			// 末尾にスペース OR カンマ+スペースなら消す
			if(!$shift)
				$tmp = preg_replace('/,\s$|\s$/', '', $tmp);
			
			if(!empty($tmp))
				$format = $tmp;
		}

		return date($format, strtotime($date));
	}

	public function time($time)
	{
		return date('H:i',strtotime($time));	// 秒は削る
	}

	public function dateAdd($date, $num, $format="Y/m/d H:i")
	{
		return date($format, strtotime($date . " " . $num . " days"));
	}
	
	public function timeAdd($date, $num, $format="Y/m/d H:i")
	{
		$pieces = explode(":", $num);
		return date($format, strtotime($date . " " . $pieces[0] . ' hours + ' . $pieces[1] . ' minutes'));
	}
}