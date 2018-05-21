<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Org_model extends CI_Model
{
    public $table_name = "tbl_org";

    public function getOrg($org_id)
    {	
    	$this->db->where('org_id', $org_id);
    	$query = $this->db->get($this->table_name);
    	$result = $query->result_array();

    	if(count($result) > 0)
    	{
    		return $result[0];
    	}

    	return null;
    }

    public function createOrg($data)
    {
        $this->db->insert($this->table_name, $data);
        
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function updateOrg($orgId, $data)
    {
        $this->db->where('org_id', $orgId);
        $this->db->update($this->table_name, $data);
        
        return TRUE;
    }
}