<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Event_model extends CI_Model
{
    public $table_name = "tbl_event";
    public $table_liked = "tbl_event_like";
    public function getAllEvents()
    {
    	$query = $this->db->get($this->table_name);
    	
    	$result = $query->result_array();

    	return $result;
    }

    public function getLikedEvents($userId)
    {
    	$this->db->from($this->table_name);
    	$this->db->join($this->table_liked, $this->table_name.".event_id = ".$this->table_liked.".evl_event_id");
    	$this->db->where($this->table_liked.".evl_user_id", $userId);
    	$query = $this->db->get();
    	return $query->result_array();
    }
}