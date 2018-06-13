<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Apiadminbase extends CI_Controller
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('org_model');
        $this->load->model('order_model');
        $this->load->model('event_model');

        $headers = $this->input->request_headers();

        // echo $headers['Token'];
        if(!isset($headers['Token']))
        {
            echo json_encode(array('success' => false, "msg" => "token is required!"));
            exit();
        }

        $this->user = $this->user_model->getUserByToken($headers['Token']);
        // var_dump($user);
        if($this->user == false){
            echo json_encode(array('success' => false, "msg" => "token is mismatched!"));
            exit();
        } 

        if($this->user['roleId'] != '1')
        {
          echo json_encode(array('success' => false, "msg" => "This is not admin user!"));
          exit();  
        }
        
    }

}