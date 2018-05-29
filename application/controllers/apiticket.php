<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require  'apibase.php';
require_once APPPATH."third_party/stripe/init.php";

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
            
            $event = $this->event_model->getEvent($data['ticket_event_id']);
            $product_id = $event['stripe_product_id'];
            if($product_id == "" || $product_id == null)
            {
                $prod_info = $this->creatProduct($data['ticket_event_id']);
                if(!$prod_info)
                {
                  $error_data['success'] = false;
                  $error_data['msg'] = "Stripe Product creation error";
                  echo json_encode($error_data);
                  exit();
                }

                $product_id = $prod_info['id'];
                $this->event_model->updateEvent($data['ticket_event_id'], array('stripe_product_id' => $product_id));
            }

            $sku_info = $this->createSKU($product_id, $data['ticket_price'], $data['ticket_counts'], $data['ticket_type'],$data['ticket_event_id']);

            if(!$sku_info)
            {
                $error_data['success'] = false;
                $error_data['msg'] = "Can't create SKU for this ticket.";
                echo json_encode($error_data);
                exit();
            }
            $data['ticket_sku_id'] = $sku_info['id'];
            // $this->ticket_model->editTicket($ticket_id, $data);
            $ticket_id = $this->ticket_model->addTicket($data);
            $return_data['success'] = true;
            $return_data['msg'] = "Ticket is created successfully";

            $tickets = $this->ticket_model->getTickets($event["event_id"]);
            $org = $this->org_model->getOrg($event["event_org_id"]);
            $isliked = $this->event_like_model->isLiked($this->user['userId'], $event['event_id']);
            $event['tickets'] = $tickets;
            $event['org'] = $org;
            $event['is_liked'] = $isliked;
            $return_data['event'] =  $event;

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
            
            $ticket = $this->ticket_model->getTicket($ticket_id);
            if(!$ticket){
                $error_data['success'] = false;
                $error_data['msg'] = "Ticket is not exist";
                echo json_encode($error_data);
                exit();
            }
            $event = $this->event_model->getEvent($ticket['ticket_event_id']);
            $sku_id = $ticket['ticket_sku_id'];
            if($sku_id == "" || $sku_id == null)
            {
                

                $product_id = $event['stripe_product_id'];
                if($product_id == "" || $product_id == null)
                {
                    $prod_info = $this->creatProduct($ticket['ticket_event_id']);
                    if(!$prod_info)
                    {
                      $error_data['success'] = false;
                      $error_data['msg'] = "Stripe Product creation error";
                      echo json_encode($error_data);
                      exit();
                    }

                    $product_id = $prod_info['id'];
                    $this->event_model->updateEvent($ticket['ticket_event_id'], array('stripe_product_id' => $product_id));
                }


                $sku_info = $this->createSKU($product_id, $data['ticket_price'], $data['ticket_counts'], $data['ticket_type'], $ticket['ticket_event_id']);
                $sku_id = $sku_info['id'];

            }
            else {
                if(!$this->updateSKU($sku_id, $data['ticket_price'], $data['ticket_counts'], $data['ticket_type'], $ticket['ticket_event_id']))
                {
                  $error_data['success'] = false;
                  $error_data['msg'] = "SKU update Error";
                  echo json_encode($error_data);
                  exit();
                }
            }

            $data['ticket_sku_id'] = $sku_id;
            $this->ticket_model->editTicket($ticket_id, $data);

            $return_data['success'] = true;
            $return_data['msg'] = "Ticket is updated successfully";

            $tickets = $this->ticket_model->getTickets($event["event_id"]);
            $org = $this->org_model->getOrg($event["event_org_id"]);
            $isliked = $this->event_like_model->isLiked($this->user['userId'], $event['event_id']);
            $event['tickets'] = $tickets;
            $event['org'] = $org;
            $event['is_liked'] = $isliked;
            $return_data['event'] =  $event;

            echo json_encode($return_data);
            exit();
        }
    }

    public function createSKU($product, $price, $quantity,$type, $event_id)
    {
        $stripe = array(
            "secret_key"      => STRIPE_SECRET_KEY,
            "publishable_key" => STRIPE_PUBLISHABLE_KEY
          );

        try{
            \Stripe\Stripe::setApiKey($stripe['secret_key']);
            $sku_info = \Stripe\SKU::create(array(
              "product" => $product,
              "attributes" => array(
                "type" => "sku_".$event_id."_".$type,
              ),
              "price" => $price,
              "currency" => "usd",
              "inventory" => array(
                "type" => "finite",
                "quantity" => $quantity
              )
            ));

            return $sku_info;
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

    public function updateSKU($sku_id,$price, $quantity,$type,  $event_id)
    {   
        $stripe = array(
            "secret_key"      => STRIPE_SECRET_KEY,
            "publishable_key" => STRIPE_PUBLISHABLE_KEY
        );
         try{
           \Stripe\Stripe::setApiKey($stripe['secret_key']);
            $sku = \Stripe\SKU::retrieve($sku_id);
            $sku->price = $price;
            $sku->inventory['quantity'] = $quantity;
            $sku->attributes= array('type'=>"sku_".$event_id."_".$type);
            $response = $sku->save();
            return true;
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

          return false;
    }

    public function orderTicket()
    {
   
        if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
            // throw new Exception('Request method must be POST!');
            $data['success'] = false;
            $data['msg'] = 'Request method must be POST!';
            echo json_encode($data);
            exit();
        }
         
        //Make sure that the content type of the POST request has been set to application/json
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if(strcasecmp($contentType, 'application/json') != 0){
            // throw new Exception('Content type must be: application/json');
            $data['success'] = false;
            $data['msg'] = 'Content type must be: application/json';
            echo json_encode($data);
            exit();
        }
         
        //Receive the RAW post data.
        $content = trim(file_get_contents("php://input"));
         
        //Attempt to decode the incoming RAW post data from JSON.
        $decoded = json_decode($content, true);
         
        //If json_decode failed, the JSON is invalid.
        if(!is_array($decoded)){
            throw new Exception('Received content contained invalid JSON!');
            $data['success'] = false;
            $data['msg'] = 'Content type must be: application/json';
            echo json_encode($data);
            exit();
        }
         
        //Process the JSON.
        $items = $decoded['items'];
        $temp_items = $items;
        
        if(count($items) == 0)
        {
            $data['success'] = false;
            $data['msg'] = 'Item amounts must be more than 1!!!';
            echo json_encode($data);
            exit();
        }

        if(!isset($decoded['event_id']) || $decoded['event_id'] == null || $decoded['event_id'] == "")
        {
            $data['success'] = false;
            $data['msg'] = 'Event id is missing.';
            echo json_encode($data);
            exit();   
        } else {
            $event_id = $decoded['event_id'];
            $event = $this->event_model->getEvent($event_id);
            if(!$event ){
                $data['success'] = false;
                $data['msg'] = 'Event is not exist.';
                echo json_encode($data);
                exit();   
            }
        }

        foreach ($items as $key => $item) {
            if($item['amount'] == 0)
            {
                $data['success'] = false;
                $data['msg'] = 'Amount must be much more than 1.';
                echo json_encode($data);
                exit();       
            }

            $ticket = $this->ticket_model->getTicket($item['ticket_id']);

            if(!$ticket){
                $data['success'] = false;
                $data['msg'] = 'Ticket is not exist';
                echo json_encode($data);
                exit();          
            }

            if($ticket['ticket_sku_id'] == "")
            {
                $data['success'] = false;
                $data['msg'] = 'SKU is not exist';
                echo json_encode($data);
                exit();             
            }
            if ($event_id != $ticket['ticket_event_id'])
            {
                $data['success'] = false;
                $data['msg'] = 'Event id and ticket is not matched.';
                echo json_encode($data);
                exit();   
            }

            $items[$key]['ticket'] = $ticket;

        }
        
        $orderinfo = array();
        $orderinfo['currency'] = 'usd';
        $order_items = array();
        foreach ($items as $key => $item) {
            $temp_item['parent'] = $item['ticket']['ticket_sku_id'];
            $temp_item['quantity'] = $item['amount'];
            array_push($order_items, $temp_item); 
        }
        $orderinfo['items'] = $order_items;
        $orderinfo['shipping'] = array(
            "name" => $this->user['fname'] . " " . $this->user['lname'],
            "address" => array(
              "line1" => $this->user['user_addr_street'],
              "city" => $this->user['user_addr_city'],
              "state" => $this->user['user_addr_state'],
              "country" => $this->user['user_addr_country'],
              "postal_code" => $this->user['user_addr_postal']
            )
        );
        $orderinfo['email'] = $this->user['email'];

        $order = $this->orders($orderinfo);

        $order_data['order_stripe_order_id'] = $order['id'];
        $order_data['order_user_id'] = $this->user['userId'];
        $order_data['order_tickets_info'] = json_encode($temp_items);
        $order_data['order_event_id'] = $event_id;
        $ordered_id = $this->order_model->addOrder($order_data);
        
        $get_order = $this->order_model->getOrder($ordered_id);
        
        $get_order['order_tickets_info'] = json_decode($get_order['order_tickets_info']);
        echo json_encode($get_order);
        

    }

    private function orders($orderinfo)
    {
        $stripe = array(
        "secret_key"      => STRIPE_SECRET_KEY,
        "publishable_key" => STRIPE_PUBLISHABLE_KEY
        );
        try{
            \Stripe\Stripe::setApiKey($stripe['secret_key']);
             $response = \Stripe\Order::create($orderinfo);
             return $response;
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
          return false;
    }

    public function orderPay()
    {
        $this->form_validation->set_rules('token','Token','required');
        $this->form_validation->set_rules('order_id','Order id','required|numeric');

          if($this->form_validation->run() == FALSE)
        {
            $data['success'] = false;
            $data['msg'] = "All data is required!";
            echo json_encode($data);
            exit();
        }
        else
        {
            $order_id = $this->input->post('order_id');
            $token = $this->input->post('token');
            $order = $this->order_model->getOrder($order_id);
            if(!$order)
            {
                $data['success'] = false;
                $data['msg'] = 'Order is not exist.';
                echo json_encode($data);
                exit(); 
            }

            if($order['order_status'] != "checked")
            {
                $data['success'] = false;
                $data['msg'] = 'Order is not checked yet.';
                echo json_encode($data);
                exit(); 
            }
            
            $response = $this->orderPayStripe($order['order_stripe_order_id'], $token);

            if(!$response)
            {
                $data['success'] = false;
                $data['msg'] = 'Pay is not done! Please try again';
                echo json_encode($data);
                exit();    
            }
            $order['order_status'] = 'paid';
            $this->order_model->updateOrder($order_id, $order);
            $data['success'] = true;
            $data['msg'] = 'Order is paied successfully.';
            echo json_encode($data);
            exit(); 
        }
    }

    private function orderPayStripe($order_id, $token)
    {
         $stripe = array(
            "secret_key"      => STRIPE_SECRET_KEY,
            "publishable_key" => STRIPE_PUBLISHABLE_KEY
        );
        try{
              \Stripe\Stripe::setApiKey($stripe['secret_key']);
               $order = \Stripe\Order::retrieve($order_id);
               $response = $order->pay(array(
                  "source" => $token // obtained with Stripe.js
                ));
               // var_dump($response);

               return $response;
        } 
         catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
           // var_dump($e);
          } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            // var_dump($e);
          } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
           // var_dump($e);
          } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
                // var_dump($e);
          } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            // var_dump($e);
          } catch (Exception $e) {
            // var_dump($e);
          }

          return false;
    }

    public function check()
    {
        if($this->user['user_org_id'] == "0" || $this->user['user_org_id'] == null){
            $data['success'] = false;
            $data['msg'] = "Your permission is not allowed!!!";
            echo json_encode($data);
            exit();
        }

        $this->form_validation->set_rules('stripe_order_id','Stripe Order Id','required');
        // $this->form_validation->set_rules('order_id','Order Id','required|numeric');                                                                                                                                                                                                                            
        if($this->form_validation->run() == FALSE)
        {
            $data['success'] = false;
            $data['msg'] = "All data is required!";
            echo json_encode($data);
            exit();
        }
        else
        {
            $stripe_order_id = $this->input->post('stripe_order_id');
            // $order_id = $this->input->post('order_id');

            // $order = $this->order_model->getOrderWhere(array('order_id'=>$order_id, "order_stripe_order_id" => $stripe_order_id));
            $order = $this->order_model->getOrderWhere(array("order_stripe_order_id" => $stripe_order_id));

            if(!$order)
            {
                $data['success'] = false;
                $data['msg'] = "Order is not exist!";
                echo json_encode($data);
                exit();       
            }
            $order = $order[0];
            $event = $this->event_model->getEvent($order['order_event_id']);
            
            if(!$event)
            {
                $data['success'] = false;
                $data['msg'] = "Order is not exist!";
                echo json_encode($data);
                exit();          
            }

            if($event['event_org_id'] != $this->user['user_org_id'])
            {
                $data['success'] = false;
                $data['msg'] = "Your permission is not allowed!";
                echo json_encode($data);
                exit();
            }

            $this->order_model->check($order['order_id']);

            $data['success'] = true;
            $data['msg'] = "Order is checked";
            echo json_encode($data);
            exit();
        }
    }
}