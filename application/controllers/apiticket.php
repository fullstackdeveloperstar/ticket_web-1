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
                  $data['success'] = false;
                  $data['msg'] = "Stripe Product creation error";
                  echo json_encode($data);
                  exit();
                }

                $product_id = $prod_info['id'];
                $this->event_model->updateEvent($data['ticket_event_id'], array('stripe_product_id' => $product_id));
            }

            $sku_info = $this->createSKU($product_id, $data['ticket_price'], $data['ticket_counts'], $data['ticket_type']);

            if(!$sku_info)
            {
                $data['success'] = false;
                $data['msg'] = "Can't create SKU for this ticket.";
                echo json_encode($data);
                exit();
            }
            $data['ticket_sku_id'] = $sku_info['id'];
            // $this->ticket_model->editTicket($ticket_id, $data);
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
            
            $ticket = $this->ticket_model->getTicket($ticket_id);
            if(!$ticket){
                $data['success'] = false;
                $data['msg'] = "Ticket is not exist";
                echo json_encode($data);
                exit();
            }

            $sku_id = $ticket['ticket_sku_id'];
            if($sku_id == "" || $sku_id == null)
            {
                $event = $this->event_model->getEvent($ticket['ticket_event_id']);

                $product_id = $event['stripe_product_id'];
                if($product_id == "" || $product_id == null)
                {
                    $prod_info = $this->creatProduct($ticket['ticket_event_id']);
                    if(!$prod_info)
                    {
                      $data['success'] = false;
                      $data['msg'] = "Stripe Product creation error";
                      echo json_encode($data);
                      exit();
                    }

                    $product_id = $prod_info['id'];
                    $this->event_model->updateEvent($ticket['ticket_event_id'], array('stripe_product_id' => $product_id));
                }


                $sku_info = $this->createSKU($product_id, $data['ticket_price'], $data['ticket_counts'], $data['ticket_type']);
                $sku_id = $sku_info['id'];

            }
            else {
                if(!$this->updateSKU($sku_id, $data['ticket_price'], $data['ticket_counts'], $data['ticket_type']))
                {
                  $data['success'] = false;
                  $data['msg'] = "SKU update Error";
                  echo json_encode($data);
                  exit();
                }
            }

            $data['ticket_sku_id'] = $sku_id;
            $this->ticket_model->editTicket($ticket_id, $data);

            $return_data['success'] = true;
            $return_data['msg'] = "Ticket is updated successfully";
            echo json_encode($return_data);
            exit();
        }
    }

    public function createSKU($product, $price, $quantity,$type)
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
                "type" => $type,
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

    public function updateSKU($sku_id,$price, $quantity,$type)
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
            $sku->attributes= array('type'=>$type);
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
}