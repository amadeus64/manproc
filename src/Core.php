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

		    // if(!isset($user_agent) && isset($_SERVER['HTTP_USER_AGENT'])) {
		    //     $user_agent = $_SERVER['HTTP_USER_AGENT'];
		    // }

		    // // https://stackoverflow.com/questions/18070154/get-operating-system-info-with-php
		    // $os_array = [
		    //     'windows nt 11'                              =>  'Windows 11',
		    //     'windows nt 10'                              =>  'Windows 10',
		    //     'windows nt 6.3'                             =>  'Windows 8.1',
		    //     'windows nt 6.2'                             =>  'Windows 8',
		    //     'windows nt 6.1|windows nt 7.0'              =>  'Windows 7',
		    //     'windows nt 6.0'                             =>  'Windows Vista',
		    //     'windows nt 5.2'                             =>  'Windows Server 2003/XP x64',
		    //     'windows nt 5.1'                             =>  'Windows XP',
		    //     'windows xp'                                 =>  'Windows XP',
		    //     'windows nt 5.0|windows nt5.1|windows 2000'  =>  'Windows 2000',
		    //     'windows me'                                 =>  'Windows ME',
		    //     'windows nt 4.0|winnt4.0'                    =>  'Windows NT',
		    //     'windows ce'                                 =>  'Windows CE',
		    //     'windows 98|win98'                           =>  'Windows 98',
		    //     'windows 95|win95'                           =>  'Windows 95',
		    //     'win16'                                      =>  'Windows 3.11',
		    //     '(media center pc).([0-9]{1,2}\.[0-9]{1,2})'=>'Windows Media Center',
		    //     '(win)([0-9]{1,2}\.[0-9x]{1,2})'=>'Windows',
		    //     '(win)([0-9]{2})'=>'Windows',
		    //     '(windows)([0-9x]{2})'=>'Windows'
		    // ];

		    // $arch_regex = '/\b(x86_64|x86-64|Win64|WOW64|x64|ia64|amd64|ppc64|sparc64|IRIX64)\b/ix';
		    // $arch = preg_match($arch_regex, $user_agent) ? '64' : '32';

		    // foreach ($os_array as $regex => $value) {
		    //     if (preg_match('{\b('.$regex.')\b}i', $user_agent)) {
		    //         return $value.' x'.$arch;
		    //     }
		    // }

		    // return 'Unknown';
		    return json_encode($_SERVER);
		}
		public function justDoIt() {
	        return response()->json([
			    'name' => 'Abigail',
			    'state' => 'CA',
			]);
    	}
	}	