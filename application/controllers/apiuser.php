<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require  'apibase.php';

class Apiuser extends Apibase
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
       echo json_encode($this->user);
    }
}