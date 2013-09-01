<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users_model extends REST_Model
{

	protected static $allowed_types = array('visitor','admin');

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
			'contact',
			'date_created',
			'date_updated'
		);
		
		$this->selectable_columns = array(
			'id',
			'access_token',
			'name',
			'type',
			'affiliation',
			'country',
			'category',
			'contact',
			'date_created',
			'date_updated'
		);
		
		$this->searchable_columns = array(
			'name',
			'affiliation',
			'category',
			'country',
			'contact',
			'date_created'
		);
	}

	
	public function get_user_by_access_token($access_token)
	{
		$query = $this->db->select()->from($this->table_name)->where(array('access_token' => $access_token))->get();
		return ($query->num_rows() >= 1) ?  $query->row_array() : FALSE;
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
		
		$data['access_token'] = $access_token;
		return $data;
	}
	
	public function group_by_country($where = array())
	{
		$this->db->select('country, COUNT(*) as count')->from($this->table_name);
		$this->db->where($where);
		$this->db->group_by('country');
		$query = $this->db->get();
		return $query->result_array();
	}
}