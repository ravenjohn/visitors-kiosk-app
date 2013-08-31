<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends REST_Controller
{
	public $_model = 'users';

    public $methods = array(
        'visitors_get'    => array(
			'params'		=> '!access_token, ?start_date, ?end_date, ?filter, ?key',
            'description'   => 'Get paginated visitors.',
            'url_format'    => array('users/v1/visitors'),
            'scope'         => ROLE_ADMIN
        ),
        'admins_get' => array(
            'params'        => '!access_token',
            'url_format'    => array('users/v1/admins'),
            'description'   => 'Get paginated admins.',
            'scope'         => ROLE_SUPER_ADMIN
        ),
        'visit_post'    => array(
            'params'        => '!name, !country, !category, ?affiliation, ?contact',
            'url_format'    => array('users/v1/visit'),
            'description'    => 'Visit.'
        ),
        'login_post'    => array(
            'params'        => '!name, !password',
            'url_format'    => array('users/v1/login'),
            'description'    => 'Login.'
        ),
        'admins_post'=> array(
            'params'        => '!access_token, !name, !password',
            'url_format'    => array('users/v1/admins'),
            'description'    => 'Add an admin.',
            'scope'         => ROLE_SUPER_ADMIN
        ),
        'admins_delete'=> array(
            'params'        => '!access_token',
            'url_format'    => array('users/v1/admins/:id'),
            'description'    => 'Delete an admin.',
            'scope'         => ROLE_SUPER_ADMIN
        )
    );

    function __construct()
    {
        parent::__construct();
    }

    public function visit_post()
    {
		$required_fields	= array('name','country','category');
		$data				= $this->_require_fields($required_fields, $this->_post_args);
		$data['category']	= strtolower($data['category']);
		$data['country']	= strtolower($data['country']);
		$data['type']		= ROLE_VISITOR;
		$data				= $this->users_model->create($data);
		$this->response($data);
    }

}