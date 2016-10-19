<?php

require_once('BaseTModels.class.php');

// t_reserve_files テーブルクラス
class Class_Model_TReserveCommentFiles extends BaseTModels
{
	const TABLE_NAME = 't_reserve_comment_files';
	protected $_name   = Class_Model_TReserveCommentFiles::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TReserveCommentFiles::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			$prefix . '_t_reserve_comment_id' => 't_reserve_comment_id',
			$prefix . '_t_work_file_id' => 't_work_file_id',
			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}

	public function selectFromReserveCommentId($t_reserve_comment_id)
	{
		$select = $this->select();

		$select->setIntegrityCheck(false)
				->from(
					array('reservecommentfiles' => $this->_name), "*")

				->join(
					array('work_files' => 't_work_files'),
					'work_files.id = reservecommentfiles.t_work_file_id',
					Class_Model_TWorkFiles::fieldArray()
				)

				->join(
					array('files' => 't_files'),
					'files.id = work_files.t_file_id',
					Class_Model_TFiles::fieldArray()
				)

				->join(
					array('members' => 'm_members'),
					'members.id = reservecommentfiles.creator',
					Class_Model_MMembers::fieldArray()
				)

				
				->where('reservecommentfiles.t_reserve_comment_id = ?', $t_reserve_comment_id)
				->order(array('reservecommentfiles.id asc'));

		return $this->fetchAll($select);
	}

	public function deleteFromReserveCommentId($t_reserve_comment_id)
	{
		$where = $this->getAdapter()->quoteInto('t_reserve_comment_id = ?' , $t_reserve_comment_id);

		return $this->delete($where);
	}
}
