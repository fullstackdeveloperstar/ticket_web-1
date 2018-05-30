<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require  'apibase.php';
require_once APPPATH."third_party/stripe/init.php";

class Apiorder extends Apibase
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getOrders()
    {
        $orders = $this->order_model->getOrderWhere(array('order_user_id'=>$this->user['userId']));

        if(!$orders)
        {
            $data['success'] = false;
            $data['msg'] = "Order is not exist!";
            echo json_encode($data);
            exit();      
        }

        foreach ($orders as $key => $order) {
            $event_temp = $this->event_model->getEvent($order['order_event_id']);
            $orders[$key]['order'] = $event_temp;
            $orders[$key]['order_tickets_info'] = json_decode($order['order_tickets_info']);
        }

        echo json_encode($orders);
    }

}