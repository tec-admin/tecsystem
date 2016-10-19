<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TPortfolio extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_portfolio';
	protected $_name   = Class_Model_Tecfolio_TPortfolio::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TPortfolio::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_m_mytheme_id' => 'm_mytheme_id',
			$prefix . '_showcase_flag' => 'showcase_flag',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// IDから取得（オーバーライド）
	public function selectFromId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('portfolio'=>'t_portfolio'), '*'
		)
		->where('portfolio.id = ?', $id)
		->joinLeft(
				array('rubric' => 'm_rubric'),
				'rubric.id = portfolio.m_rubric_id',
				array('rubric_name' => 'rubric.name')
		)
		->joinLeft(
				array('pfcontents' => 't_portfolio_contents'),
				'pfcontents.t_portfolio_id = portfolio.id',
				''
		)
		->joinLeft(
				array('contents' => 't_contents'),
				'contents.id = pfcontents.t_content_id',
				array('contents_mytheme_id' => 'm_mytheme_id')
		)
		;
		
		return $this->fetchRow($select);
	}
	
	// MyテーマIDから取得
	public function selectFromMythemeId($id)
	{
		$select = $this->select()
		->from(
				array('portfolio'=>'t_portfolio'), '*'
		)
		->where('m_mytheme_id = ?', $id)
		->order('portfolio.id asc')
		;
		
		return $this->fetchAll($select);
	}
	
	public function getRelatedDataSelect($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('portfolio'=>'t_portfolio'),
				array('id', 'm_mytheme_id', 'title', 'm_rubric_id', 'self_comment', 'mentor_comment', 'showcase_flag')
		)
		->joinLeft(
				array('rubric' => 'm_rubric'),
				'rubric.id = portfolio.m_rubric_id',
				array('rubric_name' => 'name')
		)
		->joinLeft(
				array('rubric_matrix' => 't_rubric_matrix'),
				'rubric.id = rubric_matrix.m_rubric_id',
				array(new Zend_Db_Expr("MAX(rubric_matrix.rank) as \"rubric_max\""))
		)
		->joinLeft(
				array('rubric_input' => 't_rubric_input'),
				'portfolio.id = rubric_input.t_portfolio_id',
				array(new Zend_Db_Expr("TO_CHAR(AVG(rubric_input.rank),'FM999.00') as \"rubric_input_avg\""))
		)
		->joinLeft(
				array('rubric_mentor' => 't_rubric_mentor'),
				'portfolio.id = rubric_mentor.t_portfolio_id',
				array(new Zend_Db_Expr("TO_CHAR(AVG(rubric_mentor.rank),'FM999.00') as \"rubric_mentor_avg\""))
		)
		->joinLeft(
				array('pfcontents' => 't_portfolio_contents'),
				'pfcontents.t_portfolio_id = portfolio.id',
				''
		)
		->joinLeft(
				array('contents' => 't_contents'),
				'contents.id = pfcontents.t_content_id',
				array('contents_mytheme_id' => 'm_mytheme_id')
		)
		->where('portfolio.m_mytheme_id = ?', $id)
		->group(array('portfolio.id', 'portfolio.m_mytheme_id', 'portfolio.title', 'portfolio.m_rubric_id',
				'portfolio.self_comment', 'portfolio.mentor_comment', 'portfolio.showcase_flag', 'rubric_name', 'contents_mytheme_id'))
		->order('portfolio.id asc')
		;
		
		return $select;
	}
	
	// MyテーマIDから取得(教員画面：デフォルト)
	public function selectRelatedDataFromMythemeId($id)
	{
		$select = $this->getRelatedDataSelect($id);
		return $this->fetchAll($select);
	}
	
	// MyテーマIDと作成者から取得(教員画面：学生選択後、学生画面：デフォルト)
	public function selectRelatedDataFromMythemeIdAndCreator($id, $creator)
	{
		$select = $this->getRelatedDataSelect($id);
		$select->where('portfolio.creator = ?', $creator);
	
		return $this->fetchAll($select);
	}
	
	public function selectContentsFromMythemeId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('portfolio'=>'t_portfolio'), '*'
		)
		->joinLeft(
				array('pfcontents' => 't_portfolio_contents'),
				'pfcontents.t_portfolio_id = portfolio.id',
				''
		)
		->joinLeft(
				array('contents' => 't_contents'),
				'contents.id = pfcontents.t_content_id',
				''
		)
		->joinLeft(
				array('content_files' => 't_content_files'),
				'contents.t_content_file_id = content_files.id',
				array('content_files_id' => 'id', 'content_files_name' => 'name', 'content_files_type' => 'type', 'content_files_filesize' => 'filesize', 'content_files_createdate' => 'createdate')
		)
		->where('portfolio.m_mytheme_id = ?', $id)
		->where('pfcontents.id != null')
		;
		
		return $this->fetchAll($select);
	}
	
	// コンテンツを取得
	// t_content_filesとt_filesはいずれか一方が結びつく
	public function selectContentsFromId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('portfolio'=>'t_portfolio'), '*'
		)
		->joinLeft(
				array('pfcontents' => 't_portfolio_contents'),
				'pfcontents.t_portfolio_id = portfolio.id',
				array('pfc_id' => 'id')
		)
		->joinLeft(
				array('contents' => 't_contents'),
				'contents.id = pfcontents.t_content_id',
				array('ref_title', 'ref_url', 'ref_class')
		)
		->joinLeft(
				array('content_files' => 't_content_files'),
				'contents.t_content_file_id = content_files.id',
				array('content_files_id' => 'id', 'content_files_name' => 'name', 'content_files_type' => 'type', 'content_files_filesize' => 'filesize', 'content_files_createdate' => 'createdate')
		)
		->joinLeft(
				array('files' => 't_files'),
				new Zend_Db_Expr('CAST(files.id AS VARCHAR) = pfcontents.t_content_id'),
				array('files_id' => 'id', 'files_name' => 'name', 'files_type' => 'type', 'files_filesize' => 'filesize', 'files_createdate' => 'createdate')
		)
		->joinLeft(
				array('mythemes' => 'm_mythemes'),
				'contents.m_mytheme_id = mythemes.id',
				array('mythemes_name' => 'name')
		)
		->joinLeft(
				array('subjects' => 'm_subjects_registered'),
				'contents.m_mytheme_id = subjects.id',
				array('subjects_class_subject' => 'class_subject', 'subjects_yogen' => 'yogen')
		)
		->where('portfolio.id = ?', $id)
		->where('pfcontents.id is not null')
		;
		
		return $this->fetchAll($select);
	}
}

