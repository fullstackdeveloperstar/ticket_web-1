<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Apilogin extends CI_Controller
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('login_model');
        $this->load->model('user_model');
        $this->load->model('email_model');
        $this->load->model('org_model');
    }

    public function index()
    {    
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[128]|xss_clean|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|max_length[32]|');
        $this->form_validation->set_rules('device_token', 'Device_token', 'required|max_length[100]|');

        if($this->form_validation->run() == FALSE)
        {
        	$data['success'] = false;
        	$data['msg'] = "Cridential is required!!!";
            echo json_encode($data);
            exit();
        }

        else
        {
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            $device_token = $this->input->post('device_token');
            
            $result = $this->login_model->loginMe($email, $password);
            
            if(count($result) > 0)
            {
                foreach ($result as $res)
                {
                    $key = $res->user_token;
                    if($res->user_token == ""){
                        $key = uniqid();
                    }

                    $this->user_model->editUser(array('user_token' => $key, 'device_token'=>$device_token), $res->userId);
                    $res->user_token = $key;
                    $sessionArray = array('userId'=>$res->userId,                    
                                          'role'=>$res->roleId,
                                          'roleText'=>$res->role,
                                          'name'=>$res->name,
                                          'isLoggedIn' => TRUE,
                                          'user_token' =>$res->user_token
                                    );

                                    
                    $this->session->set_userdata($sessionArray);

                    if($res->user_org_id != "0")
                    {
                        $org = $this->org_model->getOrg($res->user_org_id);
                    } else{
                        $org = null;
                    }
                    $res->org= $org;
                    $data['success'] = true;
		        	$data['user'] = $res;

		            echo json_encode($data);
		            exit();
                }
            }
            else
            {
                $data['success'] = false;
	        	$data['msg'] = "Email or password mismatch";
	            echo json_encode($data);
	            exit();
            }
        }

    }

    public function signup()
    {
        $this->form_validation->set_rules('fname','Full Name','trim|required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('lname','Full Name','trim|required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('email','Email','trim|required|valid_email|xss_clean|max_length[128]');
        $this->form_validation->set_rules('password','Password','required|max_length[20]');
        $this->form_validation->set_rules('cpassword','Confirm Password','trim|required|matches[password]|max_length[20]');
        $this->form_validation->set_rules('device_token', 'Device_token', 'required|max_length[100]|');
        
        if($this->form_validation->run() == FALSE)
        {
            $data['success'] = false;
            $data['msg'] = "All data is required!";
            echo json_encode($data);
            exit();
        }
        else
        {
            $fname = ucwords(strtolower($this->input->post('fname')));
            $lname = ucwords(strtolower($this->input->post('lname')));
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            $device_token = $this->input->post('device_token');
            
            $checkUserExist = $this->user_model->checkEmailExists($email);
            if(count($checkUserExist) > 0){
                $data['success'] = false;
                $data['msg'] = "User already exists! Please try with other information!";
                echo json_encode($data);
                exit();
            }

            $userInfo = array('email'=>$email, 'password'=>getHashedPassword($password), 'roleId'=>3, 'fname'=>$fname, 'lname' => $lname,'user_org_id'=>"0", 'createdBy'=> -1, 'createdDtm'=>date('Y-m-d H:i:s'), 'user_token' => uniqid(), 'device_token'=> $device_token);
            

            $result = $this->user_model->addNewUser($userInfo);
            
            if($result > 0)
            {
                $userInfo['userId'] = $result;
                $userInfo['role'] = "Employee";
                $userInfo['profile_image'] = "";
                $data['success'] = true;
                $data['msg'] = "Signup is successed";
                $data['user'] = $userInfo;
                echo json_encode($data);
                exit();
            }
            else
            {
                $data['success'] = false;
                $data['msg'] = "User creation failed";
                echo json_encode($data);
                exit();
            }
            
           
        }
        
    }

    public function forgotpassword() 
    {
        $status = '';
        
        $this->form_validation->set_rules('email','Email','trim|required|valid_email|xss_clean');
                
        if($this->form_validation->run() == FALSE)
        {
            $data['success'] = false;
            $data['msg'] = "Email is needed!";
            echo json_encode($data);
            exit();
        }
        else 
        {
            $email = $this->input->post('email');
            
            if($this->login_model->checkEmailExist($email))
            {
                $encoded_email = urlencode($email);
                
                $this->load->helper('string');
                $data['email'] = $email;
                $data['activation_id'] = random_string('alnum',15);
                $data['createdDtm'] = date('Y-m-d H:i:s');
                $data['agent'] = getBrowserAgent();
                $data['client_ip'] = $this->input->ip_address();
                
                $save = $this->login_model->resetPasswordUser($data);                
                
                if($save)
                {
                    $data1['reset_link'] = base_url() . "resetPasswordConfirmUser/" . $data['activation_id'] . "/" . $encoded_email;
                    $userInfo = $this->login_model->getCustomerInfoByEmail($email);

                    if(!empty($userInfo)){
                        $data1["name"] = $userInfo[0]->name;
                        $data1["email"] = $userInfo[0]->email;
                        $data1["message"] = "Reset Your Password";
                    }

                    $sendStatus = $this->email_model->sendEmail($userInfo[0]->email, "Forgot Password",  $data1['reset_link']);                    
                    if($sendStatus){

                        $data['success'] = true;
                        $data['msg'] = "Email is sent";
                        echo json_encode($data);
                        exit();
                    } else {
                        $data['success'] = false;
                        $data['msg'] = "Email has been failed, try again.";
                        echo json_encode($data);
                        exit();
                    }
                }
                else
                {
                    $data['success'] = false;
                    $data['msg'] = "It seems an error while sending your details, try again.";
                    echo json_encode($data);
                    exit();
                }
            }
            else
            {
                $data['success'] = false;
                $data['msg'] = "This email is not registered with us.";
                echo json_encode($data);
                exit();
            }
        }
    }
}