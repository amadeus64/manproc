<?php

	/**
	* PHP PROCESS MANAGER
	* PHP version 	5.3+ 
	* @category 	Library
	* @version		1.0.0
	* @author   	Amadeus <nicu.plesa@gmail.com>
	*/

	namespace amadeus64;

	Class Manproc
	{		
		protected $_store = [];
		protected $_host   = [];

		public function __construct()
		{	

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