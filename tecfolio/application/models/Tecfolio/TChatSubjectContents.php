<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TChatSubjectContents extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_chat_subject_contents';
	protected $_name	= Class_Model_Tecfolio_TChatSubjectContents::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TChatSubjectContents::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
				
			$prefix . '_t_chat_subject_id' => 't_chat_subject_id',
			$prefix . '_t_content_id' => 't_content_id',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// 授業科目相談IDで削除
	public function deleteFromChatSubjectId($id)
	{
		$where = $this->getAdapter()->quoteInto('t_chat_subject_id = ?', $id);
	
		return $this->delete($where);
	}
	
	// コンテントIDで削除
	public function deleteFromContentId($id)
	{
		$where = $this->getAdapter()->quoteInto('t_content_id = ?', $id);
	
		return $this->delete($where);
	}
}

