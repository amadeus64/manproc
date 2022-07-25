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
		protected $_id 				= 0;
		protected $_log         	= [];
		protected $_managedTypes  	= ["auto","manual"];
		protected $_ManagedTasks 	= [];
		protected $_nonManagedTasks = [];
		protected $_started 		= false;
		protected $_managed			= "manual";
		protected $_host   			= [];
		protected $_tasklist		= [];	

		public function __construct() {	
			$this->_id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
			array_push($this->_log, date('Y-m-d H:i:s', time()). ' -> Core Manager '.$this->_id.' was created.');
			$this->_host = [
				'long'  => php_uname(),
				'short' => PHP_OS,
				'full'	  => (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ?  $this->getOS() : $this->getOSInformation()	
			];
			if($this->_started) {
				$this->_tasklist = $this->getAllTasks();	
			}
			else {
				$this->_tasklist = [];	
			}
		}
		public function start() {
			$this->_started = true;
			array_push($this->_log, date('Y-m-d H:i:s', time()). ' -> Core Manager '.$this->_id.' is started.');
			$this->_tasklist = $this->getAllTasks();
		}
		public function stop() {
			$this->_started = false;
			array_push($this->_log, date('Y-m-d H:i:s', time()). ' -> Core Manager '.$this->_id.' is stopped.');
			$this->_tasklist = [];
		}
		public function getStatus() {
			array_push($this->_log, date('Y-m-d H:i:s', time()). ' -> Core Manager '.$this->_id.' give me status.');
			$result = 'Core Manager is stopped';
			if($this->_started) {
				$result = 'Core Manager '.$this->_id.' is started with pid '.getmypid();
			} 
			return $result;			
		}
		public function setManaged($manage) {
			array_push($this->_log, date('Y-m-d H:i:s', time()). ' Core Manager '.$this->_id.' try to change manage type.');
			if(in_array($manage, $this->_managedTypes)) {
				$this->_managed = $manage;
				$text = 'Core manager type has been set on '.$manage;
				array_push($this->_log, date('Y-m-d H:i:s', time()). ' Core Manager '.$this->_id.' , manage type changed to '.$manage);
				if(!$this->_started) {
					$this->_started = true;
				}
			}
			else {
				array_push($this->_log, date('Y-m-d H:i:s', time()). ' Core Manager cannot start, invalid value for manage type');
				$text = 'Invalid value';
				$this->_started = false;
			}
			return $text;
		}
		public function getManaged() {
			return $this->_managed;	
		}
		public function getLog() {
			return $this->_log;	
		}
		protected function buildTasksList($p) {
			$result = [];
			for($i = 0; $i < count($p); $i++) {
				if($p[$i] !== "") {
					array_push($result, $p[$i]);
				}
			}
			return [
				"total" 	=> count($result),
				"processes"	=> $result
			];
		}
		protected function getWinTasks() {
			exec("tasklist 2>NUL", $task_list);
			// $list = $this->buildTasksList($task_list);
			return $task_list;				
		}
		protected function getLinuxTasks() {
		}
	    protected function killPid($pid) {
	        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	            $result = shell_exec('C:\\WINDOWS\\system32\\cmd.exe /c 2>&1 taskkill /PID '.$pid.' /F');
	        }
	        else {
	            $result = shell_exec("kill -9 ".$pid);
	        }
	        return $result;
	    }
		protected function getAllTasks() {
			if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$result = $this->getWinTasks();
			}
			else {
				$result = $this->getLinuxTasks();
			}
			return  $result;
		}
	    public function getServerMemoryUsage($getPercentage=true) {
	        $memoryTotal = null;
	        $memoryFree = null;

	        if (stristr(PHP_OS, "win")) {
	            // Get total physical memory (this is in bytes)
	            $cmd = "wmic ComputerSystem get TotalPhysicalMemory";
	            @exec($cmd, $outputTotalPhysicalMemory);

	            // Get free physical memory (this is in kibibytes!)
	            $cmd = "wmic OS get FreePhysicalMemory";
	            @exec($cmd, $outputFreePhysicalMemory);

	            if ($outputTotalPhysicalMemory && $outputFreePhysicalMemory) {
	                // Find total value
	                foreach ($outputTotalPhysicalMemory as $line) {
	                    if ($line && preg_match("/^[0-9]+\$/", $line)) {
	                        $memoryTotal = $line;
	                        break;
	                    }
	                }

	                // Find free value
	                foreach ($outputFreePhysicalMemory as $line) {
	                    if ($line && preg_match("/^[0-9]+\$/", $line)) {
	                        $memoryFree = $line;
	                        $memoryFree *= 1024;  // convert from kibibytes to bytes
	                        break;
	                    }
	                }
	            }
	        }
	        else
	        {
	            if (is_readable("/proc/meminfo"))
	            {
	                $stats = @file_get_contents("/proc/meminfo");

	                if ($stats !== false) {
	                    // Separate lines
	                    $stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
	                    $stats = explode("\n", $stats);

	                    // Separate values and find correct lines for total and free mem
	                    foreach ($stats as $statLine) {
	                        $statLineData = explode(":", trim($statLine));

	                        // Total memory
	                        if (count($statLineData) == 2 && trim($statLineData[0]) == "MemTotal") {
	                            $memoryTotal = trim($statLineData[1]);
	                            $memoryTotal = explode(" ", $memoryTotal);
	                            $memoryTotal = $memoryTotal[0];
	                            $memoryTotal *= 1024;  // convert from kibibytes to bytes
	                        }

	                        // Free memory
	                        if (count($statLineData) == 2 && trim($statLineData[0]) == "MemFree") {
	                            $memoryFree = trim($statLineData[1]);
	                            $memoryFree = explode(" ", $memoryFree);
	                            $memoryFree = $memoryFree[0];
	                            $memoryFree *= 1024;  // convert from kibibytes to bytes
	                        }
	                    }
	                }
	            }
	        }

	        if (is_null($memoryTotal) || is_null($memoryFree)) {
	            return null;
	        } else {
	            if ($getPercentage) {
	                return (100 - ($memoryFree * 100 / $memoryTotal));
	            } else {
	                return array(
	                    "total" => $memoryTotal,
	                    "free" => $memoryFree,
	                );
	            }
	        }
	    }
	    public function getNiceFileSize($bytes, $binaryPrefix=true) {
	        if ($binaryPrefix) {
	            $unit=array('B','KiB','MiB','GiB','TiB','PiB');
	            if ($bytes==0) return '0 ' . $unit[0];
	            return @round($bytes/pow(1024,($i=floor(log($bytes,1024)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
	        } else {
	            $unit=array('B','KB','MB','GB','TB','PB');
	            if ($bytes==0) return '0 ' . $unit[0];
	            return @round($bytes/pow(1000,($i=floor(log($bytes,1000)))),2) .' '. (isset($unit[$i]) ? $unit[$i] : 'B');
	        }
	    }
		protected function getOSInformation() {
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
	    protected function getWindowsUser() {
            $cmd = "wmic ComputerSystem get UserName";
            @exec($cmd, $output);
            return $output[1];
	    }    
		protected function getAllActiveIPs() {
            $result = [];
            $cmd = "wmic NICCONFIG get IPAddress";
            @exec($cmd, $output);
            for($i = 0; $i < count($output); $i++) {
            	if((strlen($output[$i]) > 0) && ($output[$i] !== 'IPAddress')) {
            		$tmp = explode(', ',str_replace('{"', '', str_replace('"}', '', $output[$i])));
            		array_push($result , [
            			'IPAddress' => $tmp[0],
            			'MAC'		=> $tmp[1]
            		]);
            	}
            }
            return $result;
	    }
		protected function getOS($user_agent = null) {
			$memUsage = $this->getServerMemoryUsage(false);
		    return [
	    	'RegionCode' 			=> $_SERVER['RegionCode'],
	    	'ComputerName' 			=> $_SERVER['COMPUTERNAME'],
	    	'ComputerUser'			=> $this->getWindowsUser(),
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
	    	'ServerHost' 			=> $_SERVER['HTTP_HOST'],
	    	'ServerPort' 			=> $_SERVER['SERVER_PORT'],
	    	'DocumentRoot'			=> $_SERVER['DOCUMENT_ROOT'],
	    	'Language' 				=> $_SERVER['HTTP_ACCEPT_LANGUAGE'],
	    	'Memory'				=> sprintf("Used %s from %s (%s%%)",
									        $this->getNiceFileSize($memUsage["total"] - $memUsage["free"]),
									        $this->getNiceFileSize($memUsage["total"]),
									        $this->getServerMemoryUsage(true)
									    ),
	    	'RemoteAddr'			=> $_SERVER['REMOTE_ADDR'],
	    	'InternalIP'			=> $this->getAllActiveIPs(),
	    	'ExternalIP'			=> file_get_contents("http://ipecho.net/plain")
		    ];
		}
	    protected function getCpuSN() {
	        $return_arry = array();
	        @exec("wmic cpu get processorid", $return_arry);
	        $cpu_sn = $return_arry[1];
	        return $cpu_sn;
	    }
	    protected function getBaseboardSN() {
	        $return_arry = array();
	        @exec("wmic baseboard get serialnumber", $return_arry);

	        $baseboard_sn = $return_arry[1];
	        $baseboard_sn = str_replace("-", "", $baseboard_sn);//Remove the character "-" in the string
	        return $baseboard_sn;
	    }
		public function about() {
	        return [
			    'name' 		=> 'Core Manager',
			    'version' 	=> '1.0',
			    'author'  	=> 'Amadeus64'
			];
    	}
    	public function addTask($name, $path) {
    	}
    	public function addManagedTask($name, $path) {
    	}
	}	