<?php

require_once(dirname(__FILE__) . '/TRubricInput.php');

class Class_Model_Tecfolio_TRubricMentor extends Class_Model_Tecfolio_TRubricInput
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_rubric_mentor';
	protected $_name   = Class_Model_Tecfolio_TRubricMentor::TABLE_NAME;
}

