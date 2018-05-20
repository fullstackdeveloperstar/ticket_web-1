<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require  'apibase.php';

class Apiorg extends Apibase
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('event_model');
        $this->load->model('ticket_model');
        $this->load->model('org_model');
        $this->load->model('event_like_model');
    }

    public function createOrg()
    {
        $org_id = $this->user['user_org_id'];
        if($org_id != "0"){
            $data['success'] = false;
            $data['msg'] = "Organizer is already exist";
            echo json_encode($data);
            exit();
        }

        $this->form_validation->set_rules('org_name','Org Name','trim|required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('org_description','Org description','trim|required|max_length[2048]|xss_clean');
        $this->form_validation->set_rules('org_email','Email','trim|required|valid_email|xss_clean|max_length[128]');
        if($this->form_validation->run() == FALSE)
        {
            $data['success'] = false;
            $data['msg'] = "All data is required!";
            echo json_encode($data);
            exit();
        }
        else
        {
            $config['upload_path']          = "assets/uploads/org_image";
            $config['allowed_types']        = 'gif|jpg|png';
            $config['max_size']             = 2048000; 
            $config['max_height']           = 7680;
            $config['max_width']            = 10240;
            $config['encrypt_name']         = TRUE;
            $config['remove_spaces']        = TRUE;

            $this->load->library('upload', $config);

            if ( ! $this->upload->do_upload('org_image'))
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
                $data['org_description'] = $this->input->post('org_description');
                $data['org_name'] = $this->input->post('org_name');
                $data['org_email'] = $this->input->post('org_email');
                $data['org_image'] = base_url()."assets/uploads/org_image/".$uploaddata['file_name'];

                $org_id = $this->org_model->createOrg($data);

                $userdata['user_org_id'] = $org_id;
                $this->user_model->editUser($userdata, $this->user['userId']);

                $return_data['success'] = true;
                $return_data['msg'] = "Organizer is created successfully!"; 
                // $this->reloaduser();
                // $return_data['user'] = $this->user;
                $return_data['org'] = $this->org_model->getOrg($org_id);
                echo json_encode($return_data);
                exit();
            }
        }
    }

}