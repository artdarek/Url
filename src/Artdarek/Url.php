<?php

namespace Artdarek;

/**
 * Version: 0.0.5
 * Updated: 2015.08.12
 * Author: 	Dariusz Prząda (artdarek@gmail.com)
 */
class Url {

	/**
	 * [$query description]
	 * @var [type]
	 */
	private $query = [];

	/**
	 * [$defaults description]
	 * @var array
	 */
	private $defaults = [];

	/**
	 * [$data description]
	 * @var [type]
	 */
	private $data = [];

	/**
	 * [$content description]
	 * @var [type]
	 */
	private $base = null;

	/**
	 * [$map description]
	 * @var [type]
	 */
	private $url;

	/**
	 * [$take description]
	 * @var [type]
	 */
	private $take = ['a','c','s1','s2','s3','s4','s5'];

	/**
	 * [__construct description]
	 */
	public function __construct($data = null) 
	{
		$this->make($data);
	}

	/**
	 * [make description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function make($data = null) 
	{	
		$this->defaults = []; // clean defaults array each time make() method is called

		if (is_array($data)) {
			$this->data = $data;
			$this->query = $data;
		}
		return $this;
	}

	/**
	 * [base description]
	 * @param  [type] $base [description]
	 * @return [type]       [description]
	 */
    public function base($base = null) 
    {
    	$this->base = $base;
    	if ($base !== null) {
	    	// get params from base url and assign them to query array
		    $this->_getUrlQueryAndAssignAllParamsToTargetQueryArray($base);
    	}
    	return $this;
    }

    /**
     * [take description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function take($params = []) 
    {
    	$this->take = $params;
    	return $this;
    }

    /**
     * [defaults description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function defaults($params = []) 
    {
    	$this->defaults = $params;
    	return $this;
    }
    
    /**
     * [add description]
     * @param [type] $key   [description]
     * @param [type] $value [description]
     */
    public function add($key,$value) {
    	if ((is_array($value)) and (count($value)>=1)) {
    		$items = [];
    		foreach($value as $item) { 
    			if(isset($item)) $items[]=$item;  
    		}
    		$this->query[$key] = urldecode( implode("_", $items) );
    	} else {
    		$this->query[$key] = $value;
    	}
    	return $this;
    }

    /**
     * [combine description]
     * @param  [type] $key           [description]
     * @param  [type] $variablesKeys [description]
     * @return [type]                [description]
     */
    public function combine($key, $variablesKeys = []) 
    {
    	$values = null;
    	foreach($variablesKeys as $variable) {
    		if (is_array($this->data) and array_key_exists($variable, $this->data)) {
    			$values[] = $this->data[$variable];
    		}
    	} 
    	if (is_array($values)) $this->add($key, $values);
       	return $this;
    }

    /**
     * [createQuery description]
     * @return [type] [description]
     */
    public function createQuery() 
    {
	    $query = '';

	    if( count( $this->query ) > 0 ) {
	        
	        $queryArray = array();
	        
	        foreach( $this->take as $v ) {
	        	if( isset( $this->query[$v] ) && !empty( $this->query[$v] ) ) { 
	        		$queryArray[$v] = $this->query[$v]; 
	        	// else if there is default value defined for that variable
	        	} elseif ( (is_array($this->defaults)) and (array_key_exists($v, $this->defaults))) {
	        		$queryArray[$v] = $this->defaults[$v];
	        	} 
	    	}

	    	if( count( $queryArray ) > 0 ) {
	    		$query = http_build_query( $queryArray );
	    	}

	    }

	    return $query;
    }

    /**
     * [createBase description]
     * @return [type] [description]
     */
	public function createBase($url) 
	{ 	
		// get base url from given url
		$parsed_url = parse_url($url);

		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
		$host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
		$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
		$user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
		$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
		$pass     = ($user || $pass) ? "$pass@" : ''; 
		$path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 

		return $scheme.$user.$pass.$host.$port.$path; 
	} 


	/**
	 * [get description]
	 * @return [type] [description]
	 */
    public function get() 
    {	
    	$url = $this->createBase($this->base);
    	if($this->createQuery() != '')  $url.='?'.$this->createQuery();
	    return $url;
    }
    
    /**
     * [_getUrlQueryAndAssignAllParamsToTargetQueryArray description]
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    private function _getUrlQueryAndAssignAllParamsToTargetQueryArray($url) 
    {
    	// get base url from given url
		$parsed_url = parse_url($url);

    	// get params from base url and assign them to query array
	    if (is_array($parsed_url) and array_key_exists('query', $parsed_url)) {
	    	// build array from query string
	    	parse_str( $parsed_url['query'], $aQuery );
	    	// assign all params to target query array
	    	foreach($aQuery as $k => $v) {
				$this->add($k,$v);
	    	}  
	    }
    }

}