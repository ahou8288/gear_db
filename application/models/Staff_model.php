<?php
class Staff_model extends CI_Model {

        public function __construct()
        {
                $this->load->database();
        }

    public function get_staff()
    {
        $query = $this->db->get('Staff');
        return $query->result_array();
        // if ($field === FALSE || $value === FALSE){
        //     $query = $this->db->get('Defects');
        //     return $query->result_array();
        // }
        // if($field === 'ID'){
        //     $query = $this->db->get_where('Defects', array($field => $value));
        //     return $query->row_array();  
        // }

        // $this->db->order_by('Type', 'asc');
        // $query = $this->db->get_where('Defects', array($field => $value));
        // return $query->result_array();
    }

    public function get_rates()
    {
        $query = $this->db->get('Rates');
        return $query->result_array();
    }



	public function save_defect($data){

	}
}