<?php

	/**
	* PHP PROCESS MANAGER
	* PHP version 	5.3+ 
	* @category 	Library
	* @version		1.0.0
	* @author   	Amadeus <nicu.plesa@gmail.com>
	*/

	namespace Amadeus64\Manproc;

	Class Core
	{		
		protected $_store = [];
		protected $_host   = [];

		public function __construct()
		{	
			$this->_host = [
				'simple'  => php_uname(),
				'details' => PHP_OS,
				'full'	  => (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ?  $this->getOS() : $this->getOSInformation()	
			];	
		}
		protected function getOSInformation()
	    {
	        if (false == function_exists("shell_exec") || false == is_readable("/etc/os-release")) {
	            return null;
	        }

	        $os         = shell_exec('cat /etc/os-release');
	        $listIds    = preg_match_all('/.*=/', $os, $matchListIds);
	        $listIds    = $matchListIds[0];

	        $listVal    = preg_match_all('/=.*/', $os, $matchListVal);
	        $listVal    = $matchListVal[0];

	        array_walk($listIds, function(&$v, $k){
	            $v = strtolower(str_replace('=', '', $v));
	        });

	        array_walk($listVal, function(&$v, $k){
	            $v = preg_replace('/=|"/', '', $v);
	        });

	        return array_combine($listIds, $listVal);
	    }

		public function getOS($user_agent = null) {
		    return json_decode(json_encode($_SERVER));
		}

		public function justDoIt() {
	        return response()->json([
			    'name' => 'Abigail',
			    'state' => 'CA',
			]);
    	}
	}	