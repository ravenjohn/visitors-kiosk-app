<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rest extends REST_Controller
{
	
	function __construct()
	{
		parent::__construct();
	}

	public function index_get($id = NULL)
	{	
		$this->load->config('rest');
	
		$data	= array();
		
		$dir	= APPPATH.'/controllers/*.php';

        foreach (glob($dir) as $file)
		{
            require_once($file);
			
			$filename	= substr($file,strrpos($file, '/') + 1);
			$name		= substr($filename, 0, strrpos($filename, '.'));
			$classname	= ucfirst($name);
			
			if ($classname === 'Rest' || (!config_item('rest_enable_oauth') && $classname === 'Oauth'))
			{
				continue;
			}
			
			$ctlr = new $classname();
			
			if (empty($ctlr->_model))
			{
				continue;
			}
			
			$modelname	= $ctlr->_model . '_model';
			$model		= new $modelname();
			
			$api = array();
			
			if (!empty($model->selectable_columns))
			{
				$api['selectable']	= implode(', ', $model->selectable_columns);
			}
			
			if (!empty($model->sortable_columns))
			{
				$api['sortable']	= implode(', ', $model->sortable_columns);
			}
			
			if (!empty($model->searchable_columns))
			{
				$api['searchable']	= implode(', ', $model->searchable_columns);
			}
						
			$methods = array();
			
			foreach($ctlr->methods as $key => $value)
			{
			
				$method			= $value;
				$method_name	= substr($key, 0, strrpos($key, '_'));
				$verb			= strtoupper(substr($key, strrpos($key, '_') + 1));
				$method[$verb]	= self::getApiURL();
					
				if($verb === 'GET' && $method_name === 'index')
				{
					$method['params'] = self::getDefaultGETParams();
				}
				
				if($method_name !== 'index')
				{
					$method[$verb] .= $name . '/'.$method_name;
				}
				
				if(isset($method['url_format']))
				{
					$method[$verb] = array();
					
					foreach($method['url_format'] as $format)
					{
						$method[$verb][] = self::getApiURL() . $format;
					}
					unset($method['url_format']);
				}
				
				else if($method_name === 'index')
				{
					$method[$verb] .= $name;
				}
					
				$methods[] = $method;
			}

			$api['methods']				= $methods;
			$data['API'][$classname]	= $api;
		}

		$this->response($data);
	}
	
	
	/**
	 * Gets the URL of the server
	 * @return  string
	 **/
	private static function getServerURL()
	{
		$pageURL = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
		{
			$pageURL .= 's';
		}
		
		$pageURL .= '://';
		
		if ($_SERVER['SERVER_PORT'] != '80')
		{
			$pageURL .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
		}
		
		else
		{
			$pageURL .= $_SERVER['SERVER_NAME'];
		}
		
		return $pageURL;
	}

	/**
	 * Gets the URL of API
	 * @return  string
	 **/
	private static function getApiURL()
	{
		$url = self::getServerURL() . $_SERVER['REQUEST_URI'];
		return preg_replace('/rest\/v[0-9]+\//', '', $url);
	}

	/**
	 * Returns the default parameters of GET method
	 * @return  CSV of parameters
	 **/
	private static function getDefaultGETParams()
	{
		return '?search_key, ?fields, ?limit, ?page, ?sort_field, ?sort_order';
	}
}

