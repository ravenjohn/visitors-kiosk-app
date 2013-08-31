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

	
	public function get_user_by_access_token($access_token)
	{
		$query = $this->db->select()->from($this->table_name)->where(array('access_token' => $access_token))->get();
		return ($query->num_rows() >= 1) ?  $query->row_array() : FALSE;
	}
	
	
	public function unique_name($name)
	{
		if ($this->exists_by_fields(array('name' => $name, 'type !=' => ROLE_VISITOR)))
		{
			throw new Exception('Awwwwwww, sad. The name you\'ve chosen is not available.', 400);
		}
	}
	
	public function login($data)
	{
		$this->db->select($this->selectable_columns);
		$query	= $this->db->get_where($this->table_name, array('name' => $data['name'], 'password' => md5(PASSWORD_SALT . $data['password'] . PASSWORD_SALT), 'type !=' => ROLE_VISITOR));
		$data	= $query->row_array();
		
		if(empty($data))
		{
			throw new Exception('Name and Password did not match.');
		}

		$access_token = md5(time().uniqid());

		$this->update($data['id'], array('access_token' => $access_token));
		
		return array('access_token' => $access_token);
	}
}