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
		protected $_manage 		= [];
		protected $_host   		= [];
		protected $_tasklist	= [];	

		public function __construct()
		{	
			$this->_host = [
				'long'  => php_uname(),
				'short' => PHP_OS,
				'full'	  => (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ?  $this->getOS() : $this->getOSInformation()	
			];
			$this->_tasklist = $this->getAllTasks();	
		}
		protected function getWinTasks() {
			exec("tasklist 2 > NUL", $task_list);
			return $task_list;			
		}
		protected function getLinuxTasks() {
			
		}
		// protected function killWinTaskByPID($pid) {

		// }
		// protected function killWinTaskByTaskname($taskname) {
		// 	exec("taskkill /F /IM taskName.exe 2>NUL", $result);
		// 	return $result;
		// }
		protected function getAllTasks() {
			if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$result = $this->getWinTasks();
			}
			else {
				$result = $this->getLinuxTasks();
			}
			return  $result;
		}
	    public function getServerMemoryUsage($getPercentage=true)
	    {
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

	                        //
	                        // Extract size (TODO: It seems that (at least) the two values for total and free memory have the unit "kB" always. Is this correct?
	                        //

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
			$memUsage = $this->getServerMemoryUsage(false);
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
	    	'Memory'				=> sprintf("Used %s from %s (%s%%)",
									        $this->getNiceFileSize($memUsage["total"] - $memUsage["free"]),
									        $this->getNiceFileSize($memUsage["total"]),
									        $this->getServerMemoryUsage(true)
									    ),
	    	'RemoteAddr'			=> $_SERVER['REMOTE_ADDR'],
	    	'InternalIP'			=> getHostByName(getHostName()),
	    	'ExternalIP'			=> file_get_contents("http://ipecho.net/plain")
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