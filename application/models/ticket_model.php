<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Ticket_model extends CI_Model
{
    public $table_name = "tbl_ticket";

    public function getTickets($event_id)
    {
    	$this->db->where('ticket_event_id', $event_id);
    	$this->db->order_by('ticket_price', 'ASC');
    	$query = $this->db->get($this->table_name);
    	$result = $query->result_array();

    	return $result;
    }
}