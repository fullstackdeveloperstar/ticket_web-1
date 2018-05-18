<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require  'apibase.php';

class Apievent extends Apibase
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

    public function index()
    {
       $events = $this->event_model->getAllEvents();

       foreach ($events as $key => $event) 
       {
            $tickets = $this->ticket_model->getTickets($event["event_id"]);
            $org = $this->org_model->getOrg($event["event_org_id"]);
            $isliked = $this->event_like_model->isLiked($this->user['userId'], $event['event_id']);
            $events[$key]['tickets'] = $tickets;
            $events[$key]['org'] = $org;
            $events[$key]['is_liked'] = $isliked;
       }
       echo json_encode($events);

    }

    public function getLikedEventsList()
    {
        $events = $this->event_model->getLikedEvents($this->user['userId']);
         foreach ($events as $key => $event) 
         {
              $tickets = $this->ticket_model->getTickets($event["event_id"]);
              $org = $this->org_model->getOrg($event["event_org_id"]);
              $events[$key]['tickets'] = $tickets;
              $events[$key]['org'] = $org;
              $events[$key]['is_liked'] = true;
         }
        $data['success'] = true;
        $data['events'] = $events;

        echo json_encode($data);
    }

    public function toggleLike()
    {
        $this->form_validation->set_rules('event_id','Event Id','required|numeric');

        if($this->form_validation->run() == FALSE)
        {
          $data['success'] = false;
          $data['msg'] = "Please send Event Id";
          echo json_encode($data);
          exit();
        }
        else
        {
          $event_id = $this->input->post('event_id');
          $is_liked = $this->event_like_model->isLiked($this->user['userId'],$event_id);
          // echo json_encode(array('isliked'=>$is_liked));
          if($is_liked)
          {
            $this->event_like_model->deleteLike($this->user['userId'],$event_id);
            $data['success'] = true;
            $data['msg'] = "unliked";
            echo json_encode($data);
            exit();
          }
          else {
            $this->event_like_model->addLike($this->user['userId'],$event_id);
            $data['success'] = true;
            $data['msg'] = "liked";
            echo json_encode($data);
            exit();
          }
        }
    }
}