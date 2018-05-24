<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require  'apibase.php';
require_once APPPATH."third_party/stripe/init.php";

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

    public function createEvent()
    {
        $org_id = $this->user['user_org_id'];
        if($org_id == "0"){
            $data['success'] = false;
            $data['msg'] = "You need to create organizer!";
            echo json_encode($data);
            exit();
        }

        $this->form_validation->set_rules('event_title','Event Title','required');
        $this->form_validation->set_rules('event_description','Event description','required|max_length[2048]|xss_clean');
        $this->form_validation->set_rules('event_start_date_time','Event Start Date','required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('event_end_date_time','Event Start Date','required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('event_address1','Event address 1','required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('event_address2','Event address 2','required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('event_lat','Event lat','required|max_length[128]|xss_clean');
        $this->form_validation->set_rules('event_long','Event long','required|max_length[128]|xss_clean');
        if($this->form_validation->run() == FALSE)
        {
          $data['success'] = false;
          $data['msg'] = "All data is required!!!";
          echo json_encode($data);
          exit();
        }
        else
        {
          $config['upload_path']          = "assets/uploads/event_image";
          $config['allowed_types']        = 'gif|jpg|png';
          $config['max_size']             = 2048000; 
          $config['max_height']           = 7680;
          $config['max_width']            = 10240;
          $config['encrypt_name']         = TRUE;
          $config['remove_spaces']        = TRUE;

          $this->load->library('upload', $config);

          if ( ! $this->upload->do_upload('event_image'))
          {
            $error = array('error' => $this->upload->display_errors());
            $data['success'] = false;
            $data['msg'] = "upload error!";
            echo json_encode($data);
            exit();
          }
          else
          {
            $data['event_title'] = $this->input->post('event_title');
            $data['event_description'] = $this->input->post('event_description');
            $data['event_start_date_time'] = $this->input->post('event_start_date_time');
            $data['event_end_date_time'] = $this->input->post('event_end_date_time');
            $data['event_address1'] = $this->input->post('event_address1');
            $data['event_address_2'] = $this->input->post('event_address2');
            $data['event_lat'] = $this->input->post('event_lat');
            $data['event_long'] = $this->input->post('event_long');
            $uploaddata =  $this->upload->data();
            $data['event_image'] = base_url()."assets/uploads/event_image/".$uploaddata['file_name'];
            $data['event_org_id'] = $this->user['user_org_id'];
            $event_id = $this->event_model->creatEvent($data);
            $prod_info = $this->creatProduct($event_id);
            if(!$prod_info)
            {
              $data['success'] = false;
              $data['msg'] = "Stripe Product creation error";
              echo json_encode($data);
              exit();
            }

            $data['stripe_product_id'] = $prod_info['id'];

            $this->event_model->updateEvent($event_id, $data);
            $return_data['success'] = true;
            $return_data['msg'] = "Event is created successfully!"; 
            $return_data['event'] = $this->event_model->getEvent($event_id);
            echo json_encode($return_data);
            exit();
          }
        }
    }

    public function updateEvent()
    {
      $this->form_validation->set_rules('event_id','Event Id','required');
      $this->form_validation->set_rules('event_title','Event Title','required');
      $this->form_validation->set_rules('event_description','Event description','required|max_length[2048]|xss_clean');
      $this->form_validation->set_rules('event_start_date_time','Event Start Date','required|max_length[128]|xss_clean');
      $this->form_validation->set_rules('event_end_date_time','Event Start Date','required|max_length[128]|xss_clean');
      $this->form_validation->set_rules('event_address1','Event address 1','required|max_length[128]|xss_clean');
      $this->form_validation->set_rules('event_address2','Event address 2','required|max_length[128]|xss_clean');
      $this->form_validation->set_rules('event_lat','Event lat','required|max_length[128]|xss_clean');
      $this->form_validation->set_rules('event_long','Event long','required|max_length[128]|xss_clean');
      if($this->form_validation->run() == FALSE)
      {
        $data['success'] = false;
        $data['msg'] = "All data is required!!!";
        echo json_encode($data);
        exit();
      }
      else
      {
        $config['upload_path']          = "assets/uploads/event_image";
        $config['allowed_types']        = 'gif|jpg|png';
        $config['max_size']             = 2048000; 
        $config['max_height']           = 7680;
        $config['max_width']            = 10240;
        $config['encrypt_name']         = TRUE;
        $config['remove_spaces']        = TRUE;

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('event_image'))
        {
          $error = array('error' => $this->upload->display_errors());
          $data['success'] = false;
          $data['msg'] = "upload error!";
          echo json_encode($data);
          exit();
        }
        else
        {
          $data['event_title'] = $this->input->post('event_title');
          $data['event_description'] = $this->input->post('event_description');
          $data['event_start_date_time'] = $this->input->post('event_start_date_time');
          $data['event_end_date_time'] = $this->input->post('event_end_date_time');
          $data['event_address1'] = $this->input->post('event_address1');
          $data['event_address_2'] = $this->input->post('event_address2');
          $data['event_lat'] = $this->input->post('event_lat');
          $data['event_long'] = $this->input->post('event_long');
          $uploaddata =  $this->upload->data();
          $data['event_image'] = base_url()."assets/uploads/event_image/".$uploaddata['file_name'];
          
          $event_id = $this->input->post('event_id');
          
          $event = $this->event_model->getEvent($event_id);

          if($event['stripe_product_id'] == "" || $event['stripe_product_id'] == null)
          {
            $prod_info = $this->creatProduct($event_id);
            if(!$prod_info)
            {
              $data['success'] = false;
              $data['msg'] = "Stripe Product creation error";
              echo json_encode($data);
              exit();
            }

            $data['stripe_product_id'] = $prod_info['id'];
          }

          $this->event_model->updateEvent($event_id, $data);
          
          $return_data['success'] = true;
          $return_data['msg'] = "Event is updated successfully!"; 
          $return_data['event'] = $this->event_model->getEvent($event_id);
          echo json_encode($return_data);
          exit();
        }
      }
    }

    public function updateEvent1()
    {
      $this->form_validation->set_rules('event_id','Event Id','required');
      $this->form_validation->set_rules('event_title','Event Title','required');
      $this->form_validation->set_rules('event_description','Event description','required|max_length[2048]|xss_clean');
      $this->form_validation->set_rules('event_start_date_time','Event Start Date','required|max_length[128]|xss_clean');
      $this->form_validation->set_rules('event_end_date_time','Event Start Date','required|max_length[128]|xss_clean');
      $this->form_validation->set_rules('event_address1','Event address 1','required|max_length[128]|xss_clean');
      $this->form_validation->set_rules('event_address2','Event address 2','required|max_length[128]|xss_clean');
      $this->form_validation->set_rules('event_lat','Event lat','required|max_length[128]|xss_clean');
      $this->form_validation->set_rules('event_long','Event long','required|max_length[128]|xss_clean');
      if($this->form_validation->run() == FALSE)
      {
        $data['success'] = false;
        $data['msg'] = "All data is required!!!";
        echo json_encode($data);
        exit();
      }
      else
      {
          $data['event_title'] = $this->input->post('event_title');
          $data['event_description'] = $this->input->post('event_description');
          $data['event_start_date_time'] = $this->input->post('event_start_date_time');
          $data['event_end_date_time'] = $this->input->post('event_end_date_time');
          $data['event_address1'] = $this->input->post('event_address1');
          $data['event_address_2'] = $this->input->post('event_address2');
          $data['event_lat'] = $this->input->post('event_lat');
          $data['event_long'] = $this->input->post('event_long');
          $event_id = $this->input->post('event_id');


          $event = $this->event_model->getEvent($event_id);
          
          if($event['stripe_product_id'] == "" || $event['stripe_product_id'] == null)
          {
            $prod_info = $this->creatProduct($event_id);
            if(!$prod_info)
            {
              $data['success'] = false;
              $data['msg'] = "Stripe Product creation error";
              echo json_encode($data);
              exit();
            }

            $data['stripe_product_id'] = $prod_info['id'];
          }


          $this->event_model->updateEvent($event_id, $data);

          $return_data['success'] = true;
          $return_data['msg'] = "Event is updated successfully!"; 
          $return_data['event'] = $this->event_model->getEvent($event_id);
          echo json_encode($return_data);
          exit();
      }
    }

    public function mylist()
    {
      $org_id = $this->user['user_org_id'];
      if($org_id == "0"){
          $data['success'] = false;
          $data['msg'] = "You need to create organizer!";
          echo json_encode($data);
          exit();
      }

      $events = $this->event_model->getListbyOrgId($this->user['user_org_id']);
      if(!$events)
      {
        $data['success'] = false;
        $data['msg'] = "There is not any events.";
        echo json_encode($data);
        exit();
      }

      else
      {
        $data['success'] = true;
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
        exit();
      }
    }


    public function creatProduct($event_id)
    {
      // stripe create product
       $stripe = array(
        "secret_key"      => STRIPE_SECRET_KEY,
        "publishable_key" => STRIPE_PUBLISHABLE_KEY
      );
      try{
      \Stripe\Stripe::setApiKey($stripe['secret_key']);

      $stripe_create_pro = \Stripe\Product::create(array(
        "name" => 'event_'.$event_id,
        "type" => "good",
        "attributes" => ["type"]
      ));
      return $stripe_create_pro;
    }
      catch (\Stripe\Error\RateLimit $e) {
        // Too many requests made to the API too quickly
      } catch (\Stripe\Error\InvalidRequest $e) {
        // Invalid parameters were supplied to Stripe's API
      } catch (\Stripe\Error\Authentication $e) {
        // Authentication with Stripe's API failed
        // (maybe you changed API keys recently)
      } catch (\Stripe\Error\ApiConnection $e) {
        // Network communication with Stripe failed
      } catch (\Stripe\Error\Base $e) {
        // Display a very generic error to the user, and maybe send
        // yourself an email
      } catch (Exception $e) {

      }     
      // end create product

      return false;
    }
}