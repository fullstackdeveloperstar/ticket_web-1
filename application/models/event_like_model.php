<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Event_like_model extends CI_Model
{
    public $table_name = "tbl_event_like";

    public function getCountLike($userId)
    {
        $this->db->where('evl_user_id', $userId);
        $query = $this->db->get($this->table_name);

        $result = $query->result_array();

        return count($result);
    }

    public function isLiked($userId, $event_id)
    {
        $this->db->where('evl_user_id', $userId);
        $this->db->where('evl_event_id', $event_id);
        $query = $this->db->get($this->table_name);

        $result = $query->result_array();
        if(count($result) == 0){
            return false;
        }

        return true;
    }
}