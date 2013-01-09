<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Byzantin extends My_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		print "Byzantin module default controller output";
	}
}