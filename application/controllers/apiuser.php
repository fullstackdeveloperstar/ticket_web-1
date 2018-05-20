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
        $this->load->model('event_like_model');
        $this->load->model('ticket_puchase_model');
    }
    public function index()
    {
       echo json_encode($this->user);
    }

    public function update()
    {
        $this->form_validation->set_rules('fname','First Name','trim|required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('lname','Last Name','trim|required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('email','Email','trim|required|valid_email|xss_clean|max_length[128]');
        // $this->form_validation->set_rules('profile_image','Profile Image','required');
        
        if($this->form_validation->run() == FALSE)
        {
            $data['success'] = false;
            $data['msg'] = "All data is required!";
            echo json_encode($data);
            exit();
        }
        else
        {
            $config['upload_path']          = "assets/uploads/user_profile";
            $config['allowed_types']        = 'gif|jpg|png';
            $config['max_size']             = 2048000; 
            $config['max_height']           = 7680;
            $config['max_width']            = 10240;
            $config['encrypt_name']         = TRUE;
            $config['remove_spaces']        = TRUE;

            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('profile_image'))
            {
                $error = array('error' => $this->upload->display_errors());
                $data['success'] = false;
                $data['msg'] = "upload error!";
                echo json_encode($data);
                exit();
            }
            else
            {
                $uploaddata =  $this->upload->data();
                $data['fname'] = ucwords(strtolower($this->input->post('fname')));
                $data['lname'] = ucwords(strtolower($this->input->post('lname')));
                $data['email'] = $this->input->post('email');
                $data['profile_image'] = base_url()."assets/uploads/user_profile/".$uploaddata['file_name'];

                $this->user_model->editUser($data, $this->user['userId']);

                $return_data['success'] = true;
                $return_data['msg'] = "Update success!"; 
                $this->reloaduser();
                $return_data['user'] = $this->user;
                echo json_encode($return_data);
                exit();
            }
        }
    }

    public function update1()
    {
        $this->form_validation->set_rules('fname','First Name','trim|required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('lname','Last Name','trim|required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('email','Email','trim|required|valid_email|xss_clean|max_length[128]');
        
        if($this->form_validation->run() == FALSE)
        {
            $data['success'] = false;
            $data['msg'] = "All data is required!";
            echo json_encode($data);
            exit();
        }
        else
        {
            $data['fname'] = ucwords(strtolower($this->input->post('fname')));
            $data['lname'] = ucwords(strtolower($this->input->post('lname')));
            $data['email'] = $this->input->post('email');
            $this->user_model->editUser($data, $this->user['userId']);
            $return_data['success'] = true;
            $return_data['msg'] = "Update success!"; 
            echo json_encode($return_data);
            exit();
       
        }
    }

    public function uploadUserProfile()
    {
        $config['upload_path']          = "assets/uploads/user_profile";
        $config['allowed_types']        = 'gif|jpg|png';
        $config['max_size']             = 2048000; 
        $config['max_height']           = 7680;
        $config['max_width']            = 10240;
        $config['encrypt_name']         = TRUE;
        $config['remove_spaces']        = TRUE;

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('profile_image'))
        {
            $error = array('error' => $this->upload->display_errors());
            $data['success'] = false;
            $data['msg'] = "Upload Error!";
            echo json_encode($data);
            exit();
        }
        else
        {
            $uploaddata =  $this->upload->data();
         
            $data['profile_image'] = base_url()."assets/uploads/user_profile/".$uploaddata['file_name'];

            $this->user_model->editUser($data, $this->user['userId']);

            $return_data['success'] = true;
            $return_data['msg'] = "Upload success!"; 
            echo json_encode($return_data);
            exit();
        }
    }

    public function updatePassword()
    {
        $this->form_validation->set_rules('oldpassword','Old Password','required|max_length[20]');
        $this->form_validation->set_rules('password','Password','required|max_length[20]');
        $this->form_validation->set_rules('cpassword','Confirm Password','trim|required|matches[password]|max_length[20]');

        if($this->form_validation->run() == FALSE)
        {
            $data['success'] = false;
            $data['msg'] = "All data is required!";
            echo json_encode($data);
            exit();
        }
        else
        {
            $password = $this->input->post('password');
            $oldpassword = $this->input->post('oldpassword');

            if(!verifyHashedPassword($oldpassword, $this->user['password']))
            {
                $data['success'] = false;
                $data['msg'] = "Old Password is not mismatched";
                echo json_encode($data);
                exit();
            }
            else
            {
                $data['password'] = getHashedPassword($password);
                $this->user_model->editUser($data, $this->user['userId']);

                $return_data['success'] = true;
                $return_data['msg'] = "Password is chnaged";
                echo json_encode($return_data);
                exit();
            }   
        }
    }

    public function getTicketsAndLikedEventsCount()
    {
        $data['like_count'] = $this->event_like_model->getCountLike($this->user['userId']);
        $data['ticket_count'] = $this->ticket_puchase_model->getCountsTickets($this->user['userId']);
        $data['success'] = true;
        echo json_encode($data);
    }
}