<?php

require_once('BaseTModels.class.php');

// t_infomation_files テーブルクラス
class Class_Model_TInfomationFiles extends BaseTModels
{
	protected $_name   = 't_infomation_files';

	public function selectFromInfomationId($t_infomation_id)
	{
		$select = $this->select();

		$select->setIntegrityCheck(false)
				->from(
					array('infofiles' => $this->_name),
					array('infofiles.id', 'infofiles.t_infomation_id', 'infofiles.t_file_id', 'infofiles.createdate', 'infofiles.creator', 'infofiles.lastupdate', 'infofiles.lastupdater')
				)

				->join(
					array('files' => 't_files'),
					'files.id = infofiles.t_file_id',
					Class_Model_TFiles::fieldArray()
				)

				->join(
					array('members' => 'm_members'),
					'members.id = infofiles.creator',
					Class_Model_MMembers::fieldArray()
				)

				
				->where('infofiles.t_infomation_id = ?', $t_infomation_id)
				->order(array('files.id asc'));

		return $this->fetchAll($select);
	}

}
