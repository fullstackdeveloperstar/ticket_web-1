<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Event_model extends CI_Model
{
    public $table_name = "tbl_event";

    public function getAllEvents()
    {
    	$query = $this->db->get($this->table_name);
    	
    	$result = $query->result_array();

    	return $result;
    }
}