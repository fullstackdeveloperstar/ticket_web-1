<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require  'apibase.php';
require_once APPPATH."third_party/stripe/init.php";

class Apiorder extends Apibase
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
    }

}