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
}