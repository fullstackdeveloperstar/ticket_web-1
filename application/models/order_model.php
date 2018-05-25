<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Order_model extends CI_Model
{
    public $table_name = "tbl_order";

    public function addOrder($order)
    {
    	$this->db->insert($this->table_name,$order);
    	$insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function getOrder($order_id)
    {
    	$this->db->where('order_id', $order_id);
    	$query = $this->db->get($this->table_name);
    	$result = $query->result_array();

    	if(count($result) == 0)
    	{
    		return false;
    	}

    	return $result[0];
    }
}