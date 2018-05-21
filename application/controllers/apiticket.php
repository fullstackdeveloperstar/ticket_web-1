<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require  'apibase.php';

class Apiticket extends Apibase
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

    public function createTicket()
    {
        $this->form_validation->set_rules('ticket_type','Ticket Type','required');
        $this->form_validation->set_rules('ticket_price','Ticket Price','required');
        $this->form_validation->set_rules('ticket_counts','Ticket Counts','required|numeric');
        $this->form_validation->set_rules('ticket_event_id','Ticket Event Id','required|numeric');
        if($this->form_validation->run() == FALSE)
        {
            $data['success'] = false;
            $data['msg'] = "All data is required!!!";
            echo json_encode($data);
            exit();
        }
        else
        {
            $data['ticket_type'] = $this->input->post('ticket_type');
            $data['ticket_price'] = $this->input->post('ticket_price');
            $data['ticket_counts'] = $this->input->post('ticket_counts');
            $data['ticket_event_id'] = $this->input->post('ticket_event_id');

            $ticket_id = $this->ticket_model->addTicket($data);

            $return_data['success'] = true;
            $return_data['msg'] = "Ticket is created successfully";
            echo json_encode($return_data);
            exit();
        }
    }

    public function updateTicket()
    {
        $this->form_validation->set_rules('ticket_type','Ticket Type','required');
        $this->form_validation->set_rules('ticket_price','Ticket Price','required');
        $this->form_validation->set_rules('ticket_counts','Ticket Counts','required|numeric');
        $this->form_validation->set_rules('ticket_id','Ticket Id','required|numeric');
        if($this->form_validation->run() == FALSE)
        {
            $data['success'] = false;
            $data['msg'] = "All data is required!!!";
            echo json_encode($data);
            exit();
        }
        else
        {
            $data['ticket_type'] = $this->input->post('ticket_type');
            $data['ticket_price'] = $this->input->post('ticket_price');
            $data['ticket_counts'] = $this->input->post('ticket_counts');
            $ticket_id = $this->input->post('ticket_id');
            $this->ticket_model->editTicket($ticket_id, $data);

            $return_data['success'] = true;
            $return_data['msg'] = "Ticket is updated successfully";
            echo json_encode($return_data);
            exit();
        }
    }
}