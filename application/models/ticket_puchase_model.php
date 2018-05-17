<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Ticket_puchase_model extends CI_Model
{
    public $table_name = "tbl_ticket_puchase";

    public function getCountsTickets($userId)
    {
        $this->db->where('tp_user_id', $userId);
        $query = $this->db->get($this->table_name);

        $result = $query->result_array();
        $count = 0;
        foreach ($result as $row) {
        	$count += $row['tp_count'];
        }
        return $count;
    }
}