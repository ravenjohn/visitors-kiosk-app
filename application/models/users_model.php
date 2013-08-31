<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users_model extends REST_Model
{

	protected static $allowed_types = array('visitor','admin','superadmin');

	function __construct()
	{
		parent::__construct();
		
		$this->table_name = TABLE_USERS;
		
		$this->columns = array(
			'id',
			'access_token',
			'name',
			'password',
			'type',
			'affiliation',
			'country',
			'category',
			'date_created',
			'date_updated'
		);
		
		$this->selectable_columns = array(
			'id',
			'access_token',
			'name',
			'password',
			'type',
			'affiliation',
			'country',
			'category',
			'date_created',
			'date_updated'
		);
		
		$this->searchable_columns = array(
			'name',
			'affiliation'
		);
	}

}