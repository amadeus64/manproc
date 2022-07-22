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

		    return [
	    	'RegionCode' 			=> $_SERVER['RegionCode'],
	    	'ComputerName' 			=> $_SERVER['COMPUTERNAME'],
	    	'BiosSerialNumber'		=> preg_replace("/[^-\w,]/", "", str_replace("SerialNumber","",shell_exec('wmic bios get serialnumber'))),
	    	'ProcessorSerial'		=> $this->getCpuSN(),
	    	'ProcessorsNumber' 		=> $_SERVER['NUMBER_OF_PROCESSORS'],
	    	'ProcessorArchitecture' => $_SERVER['PROCESSOR_ARCHITECTURE'],
	    	'ProcessorId' 			=> $_SERVER['PROCESSOR_IDENTIFIER'],
	    	'ProcessorLevel' 		=> $_SERVER['PROCESSOR_LEVEL'],
	    	'ProcessorRevision' 	=> $_SERVER['PROCESSOR_REVISION'],
	    	'MotherboardSerial'		=> $this->getBaseboardSN(),
	    	'ComSpec' 				=> $_SERVER['ComSpec'],
	    	'OsType' 				=> $_SERVER['OS'],
	    	'UserAgent' 			=> $_SERVER['HTTP_USER_AGENT'],
	    	'ServerSoftware' 		=> $_SERVER['SERVER_SOFTWARE'],
	    	'RequestTime' 			=> $_SERVER['REQUEST_TIME'],
	    	'FCGI_Role' 			=> $_SERVER['FCGI_ROLE'],
	    	'DocumentRoot' 			=> $_SERVER['DOCUMENT_ROOT'],
	    	'Host' 					=> $_SERVER['HTTP_HOST'],
	    	'Language' 				=> $_SERVER['HTTP_ACCEPT_LANGUAGE'],
		    ];
		}

	   //Get the CPU serial number
	    protected function getCpuSN()
	    {
	        $return_arry = array();
	        @exec("wmic cpu get processorid", $return_arry);
	        $cpu_sn = $return_arry[1];
	        return $cpu_sn;
	    }

	   //Get the motherboard serial number
	    protected function getBaseboardSN()
	    {
	        $return_arry = array();
	        @exec("wmic baseboard get serialnumber", $return_arry);

	        $baseboard_sn = $return_arry[1];
	        $baseboard_sn = str_replace("-", "", $baseboard_sn);//Remove the character "-" in the string
	        return $baseboard_sn;
	    }

		public function justDoIt() {
	        return response()->json([
			    'name' => 'Abigail',
			    'state' => 'CA',
			]);
    	}
	}	