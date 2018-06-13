<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require  'apiadminbase.php';

class Apiadminuser extends Apiadminbase
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
    	$return_data['success'] = true;
    	$return_data['user'] = $this->user;
    	echo json_encode($return_data);
    }

    public function getUserList()
    {
    	$users = $this->user_model->getUserList();
    	if(!$users)
    	{
    		$return_data['success'] = false;
    		$return_data['msg'] = "There is not any users";
    		echo json_encode($return_data);
    		exit();
    	}
    	else{
    		
    	}
    }

}