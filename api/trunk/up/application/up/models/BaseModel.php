<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BaseModel extends CI_Model
{
    protected $table_name;


    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('RedisCliModel', 'redis' );
        $this->redis->connect();
        //$this->redis->select(3);
    }

    public function getById( $val ){
        return $this->getByField( 'id', $val );
    }

    public function getByField( $field_name, $field_val, $rows = FALSE ){
        $sql = "SELECT * FROM $this->table_name WHERE $field_name = ? ";
        $query = $this->db->query($sql, array( $field_val ));

        if( $rows ){
            return $query->result_array();
        }
        else{
            return $query->row_array();
        }
    }

    public function getByFields( $fields, $vals, $gets=null, $order=null, $rows = FALSE ){
        $sql = 'SELECT ' ;

        if( $gets == null ){
            $sql .= '*';
        }
        else{
            $sql .= implode( ',', $gets );
        }

        $sql .= " FROM $this->table_name WHERE 1=1 ";

        foreach( $fields as $field ){
            $sql .= " and $field = ? ";
        }

        //echo $sql;

        $query = $this->db->query($sql, $vals);


        if( $rows ){
            return $query->result_array();
        }
        else{
            return $query->row_array();
        }
    }

    public function insert($insert_data, $need_escape = TRUE ) {
        try {
            $data = $insert_data;

            if ($need_escape) {
                foreach ($data as $k => $v) {
                    $v = $this->db->escape($v);
                }
            }

            if (TRUE === $this->db->insert($this->table_name, $data)) {
                $insert_id = $this->db->insert_id();
                if (0 === $insert_id) {
                    return TRUE;
                } else {
                    return $insert_id;
                }
            } else {
                return FALSE;
            }
        }
        catch( Exception $e ){
            $db_error = $this->db->error();
            throw new OpException( $db_error['message'], ERROR_CODE_MYSQL_FAIL );
        }
    }



    public function update( $id, $update_data, $need_escape = TRUE) {
        $data = $update_data;

        if( $need_escape ){
            foreach( $data as $k => $v ){
                $v = $this->db->escape( $v );
            }
        }
        $this->db->update($this->table_name, $data, array('id' => $id));
        return $this->db->affected_rows();
    }


    public function update_where($update_data, $where_arr, $return_affected_rows = TRUE) {
        $ret = $this->db->update($this->table_name, $update_data, $where_arr);

        if ($return_affected_rows) {
            return $this->db->affected_rows();
        } else {
            return $ret;
        }
    }


    public function delete( $in_data, $need_escape = TRUE) {
        $data = $in_data;

        if( $need_escape ){
            foreach( $data as $k => $v ){
                $v = $this->db->escape( $v );
            }
        }
        $this->db->delete($this->table_name, $data);
        return $this->db->affected_rows();
    }
}