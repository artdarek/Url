<?php

namespace Artdarek;

/**
 * Version: 0.0.7
 * Updated: 2015.10.25
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
	private $take = [];

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
     * @return object self
	 */
	public function make($data = null) 
	{	
		$this->defaults = []; // clean defaults array each time make() method is called

		if (is_array($data)) {
			$this->data = $data; // raw source query data
			$this->query = $data; // destination data for building new query
		}

        // if take array is empty set all vars from url by default
        $this->_takeAllIfNotSpecified();

		return $this;
	}

	/**
	 * [base description]
	 * @param  [type] $base [description]
	 * @return [type]       [description]
     * @return object self
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
     * @return object self
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
     * @return object self
     */
    public function defaults($params = []) 
    {
    	$this->defaults = $params;
    	return $this;
    }
    
    /**
     * Add variable name and value
     * 
     * @param string $key
     * @param string|array $value
     * @return object self
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
    	$this->take[] = $key; 
    	return $this;
    }

    /**
     * [combine description]
     * @param  string $key
     * @param  array $variablesKeys
     * @return object self
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
     * Create url query
     * 
     * @return string
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
     * Get all variable keys and fill $this->take array with them
     * @return void
     */
    private function _takeAllIfNotSpecified() 
    {
        // if take array is empty set all vars from url
        if ((!is_array($this->take)) or (count($this->take) <= 0)) {
        	$this->take = array_keys($this->data);
        }
    }

    /**
     * Create url base
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
	 * Get finall url
	 * @return string
	 */
    public function get() 
    {	
    	$url = $this->createBase($this->base);
    	if($this->createQuery() != '')  $url.='?'.$this->createQuery();
	    return $url;
    }
    
    /**
     * [_getUrlQueryAndAssignAllParamsToTargetQueryArray description]
     * @param  string $url
     * @return void
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