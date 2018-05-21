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

    public function creatEvent($data)
    {
        $this->db->insert($this->table_name, $data);
        
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function getEvent($event_id)
    {
        $this->db->where('event_id', $event_id);
        $query = $this->db->get($this->table_name);

        $result = $query->result_array();
        if(count($result)>0){
            return $result[0];
        }
        return false;
    }

    public function updateEvent($event_id, $data)
    {
        $this->db->where('event_id', $event_id);
        $this->db->update($this->table_name, $data);
    }
}