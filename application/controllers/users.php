<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends REST_Controller
{
	public $_model = 'users';

    public $methods = array(
        'visitors_get'    => array(
			'params'		=> '!access_token',
            'description'   => 'Get visitors.',
            'url_format'    => array('users/visitors'),
            'scope'         => ROLE_ADMIN
        ),
        'visitors_by_country_get'    => array(
			'params'		=> '!access_token',
            'description'   => 'Get visitors grouped by country.',
            'url_format'    => array('users/visitors_by_country'),
            'scope'         => ROLE_ADMIN
        ),
        'visit_get'    => array(
            'params'        => '!name, !country, !category, ?affiliation, ?contact',
            'url_format'    => array('users/visit'),
            'description'    => 'Visit.'
        ),
        'login_post'    => array(
            'params'        => '!name, !password',
            'url_format'    => array('users/login'),
            'description'    => 'Login.'
        ),
        'logout_get'    => array(
            'params'        => '!access_token',
            'url_format'    => array('users/logout'),
            'description'    => 'Logout.',
            'scope'         => ROLE_ADMIN
        )
    );

    function __construct()
    {
        parent::__construct();
    }

    public function visit_get()
    {
		$required_fields	= array('name','country','category');
		$data				= $this->_require_fields($required_fields, $this->_get_args);
		$data['category']	= strtolower($data['category']);
		$data['country']	= strtolower($data['country']);
		if($this->get('affiliation'))
		{
			$data['affiliation'] = str_replace('Affiliation','',$data['affiliation']);
		}
		$data['type']		= ROLE_VISITOR;
		$data				= $this->users_model->create($data);
		$this->response($data);
    }

	public function visitor_details_get()
	{
		$data = $this->users_model->get_all(
					array('type' => 'visitor'),
					$this->get('search_key'),
					$this->get('fields'),
					$this->get('page'),
					$this->get('limit'),
					$this->get('sort_field'),
					$this->get('sort_order'));
		$this->response($data);
	}
	public function visitors_get()
	{
		$where = array('type' => 'visitor');
		
		if($this->get('filter') && $this->get('filter_key'))
		{
			$filter = $this->get('filter');
			self::_check_in_array($filter, array('country', 'category'), 'filter');
			$where[$filter] = $this->get('filter_key');
		}
		
		$grouping = $this->get('grouping');
		if(!($year = $this->get('year')))
		{
			$year = date('Y', now());
		}
		if(!($month = $this->get('month')))
		{
			$month = date('m', now());
		}
		$category = $this->get('category');
		
		if($category && $category != 'all'){
			$where['category'] = $category;
		}

		if($grouping === 'daily'){
			$data[] = array('Daily', 'Number of Visitors');
			for($i = 1; $i <= 31; $i++)
			{
				$where['date_created >=']	= date('Y-m-d H:i:s', mktime(0, 0, 0, $month, $i, $year));
				$where['date_created <']	= date('Y-m-d H:i:s', mktime(0, 0, 0, $month, $i+1, $year));
				$timestamp					= mktime(0, 0, 0, date('n') - $i, 1);
				$data[]						= array($i, $this->users_model->get_total_count(
												$where,
												$this->get('search_key'),
												$this->get('fields'),
												$this->get('page'),
												$this->get('limit'),
												$this->get('sort_field'),
												$this->get('sort_order')));
			}
		}
		else if($grouping === 'monthly' || !$grouping){
			$data[] = array('Month', 'Number of Visitors');
			for($i = date('m', now())-1; $i >= date('m', now()) - 12; $i--)
			{
				$where['date_created >=']	= date('Y-m-d H:i:s', mktime(0, 0, 0, date('n') - $i, 1, $year));
				$where['date_created <']	= date('Y-m-d H:i:s', mktime(0, 0, 0, date('n') - $i+1, 1, $year));
				$timestamp					= mktime(0, 0, 0, date('n') - $i, 1);
				// die(print_r($where));
				$data[]						= array(date('M', $timestamp), $this->users_model->get_total_count(
												$where,
												$this->get('search_key'),
												$this->get('fields'),
												$this->get('page'),
												$this->get('limit'),
												$this->get('sort_field'),
												$this->get('sort_order')));
			}
		}
		else if($grouping === 'quarterly'){
			$data[] = array('Quarter', 'Number of Visitors');
			$quarters = array(1 => '1st Quarter', 4 => '2nd Quarter', 7 => '3rd Quarter', 10 => '4th Quarter',);
			for($i = 1; $i <= 12; $i+=3)
			{
				$where['date_created >=']	= date('Y-m-d H:i:s', mktime(0, 0, 0, $i, 1, $year));
				$where['date_created <']	= date('Y-m-d H:i:s', mktime(0, 0, 0, $i+3, 1, $year));
				$timestamp					= mktime(0, 0, 0, date('n') - $i, 1);
				$data[]						= array($quarters[$i], $this->users_model->get_total_count(
												$where,
												$this->get('search_key'),
												$this->get('fields'),
												$this->get('page'),
												$this->get('limit'),
												$this->get('sort_field'),
												$this->get('sort_order')));
			}
		}
		else if($grouping === 'yearly'){
			$data[] = array('Yearly', 'Number of Visitors');
			for($i = intval(date('Y', now())); $i >= intval(date('Y', now())) - 5; $i--)
			{
				$where['date_created >=']	= date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $i));
				$where['date_created <']	= date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $i+1));
				// die(print_r($where));
				$timestamp					= mktime(0, 0, 0, date('n') - $i, 1);
				$data[]						= array('Year ' . date('Y', mktime(0, 0, 0, 1, 1, $i)), $this->users_model->get_total_count(
												$where,
												$this->get('search_key'),
												$this->get('fields'),
												$this->get('page'),
												$this->get('limit'),
												$this->get('sort_field'),
												$this->get('sort_order')));
			}
		}
				
		$this->response($data);
	}
	
	public function visitors_by_country_get(){
		$where = array('type' => 'visitor');
		
		if(!($year = $this->get('year')))
		{
			$year = date('Y', now());
		}
		
		$category = $this->get('category');
		
		if($category && $category != 'all'){
			$where['category'] = $category;
		}
		
		if($grouping === 'daily')
		{
			$where['date_created >=']	= date('Y-m-d H:i:s', mktime(0, 0, 0, intval(date('m', now())), 1));
			$where['date_created <']	= date('Y-m-d H:i:s', mktime(0, 0, 0, intval(date('m', now()))+1, 1));
		}
		else if($grouping === 'monthly' || $grouping === 'quarterly')
		{
			$where['date_created >=']	= date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $year));
			$where['date_created <']	= date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $year+1));
		}
		else if($grouping === 'yearly')
		{
			$where['date_created >=']	= date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, intval(date('Y', now()))-5));
			$where['date_created <']	= date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, intval(date('Y', now()))+1));
		}
		
		$data = $this->users_model->group_by_country($where);
		$ret[] = array('Country', 'Visitors');
		foreach($data as $datum)
		{
			$ret[] = array($datum['country'], $datum['count']);
		}
		$this->response($ret);
	}
	
	public function login_post()
	{
		$required_fields	= array('name', 'password');
		$data				= $this->_require_fields($required_fields, $this->_post_args);
		
		self::_check_strlen($data['password'], 6, 'password');
		$data				= $this->users_model->login($data);

		$this->response($data);
	}
	
	public function logout_get(){
		$this->users_model->update($this->user['id'], array('access_token' => NULL));
		$this->response(array('message' => 'Logout successful'));
	}
}