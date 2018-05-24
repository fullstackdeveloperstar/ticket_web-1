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

    public function addTicket($data)
    {
    	$this->db->insert($this->table_name, $data);
        
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function editTicket($ticket_id, $data)
    {
        $this->db->where('ticket_id', $ticket_id);
        $this->db->update($this->table_name, $data);
    }
}