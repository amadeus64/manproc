<?php

	/**
	* PHP PROCESS MANAGER
	* PHP version 	5.3+ 
	* @category 	Library
	* @version		1.0.0
	* @author   	Amadeus <nicu.plesa@gmail.com>
	*/

	namespace amadeuspm;

	Class Process
	{		
		protected $_store = [];
		protected $_host   = [];

		public function __construct( $name = NULL, $path = NULL, $type = NULL)
		{	
			if(!is_null($name)) {
				$this->_name = $name;
			}
			if(!is_null($path)) {
				$this->_path = $path;
			}
			if(!is_null($type)) {
				$this->_type = $type;
			}

			if(is_null($name) && is_null($path) && is_null($type)) {
				return $this->_host;
			}
			else {
				
			}	
		}
		public function getPID() {
			return $this->_pid;
		}
		public function add2store() {

		}
		public function remove2store() {

		}
		public function getHostInfo() {
			return $this->_host;
		}
	}	