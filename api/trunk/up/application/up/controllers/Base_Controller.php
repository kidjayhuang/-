<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Base_Controller
 * The base class for controllers
 *
 */
abstract class Base_Controller extends CI_Controller {


    public function __construct() {
        parent::__construct();
        $this->load->helper('util');
        $this->load->library('form_validation');
    }

    protected function get_pos_int( $key, $name, $isnull=false, $min=0, $max=0 ){

        $val = $this->input->post( $key );

        if( $isnull && !$val ){
            return 0;
        }

        $val = intval( $val );

        if( !$isnull ){
            if (!$val) {
                throw new OpException($name . '为空', ERROR_CODE_PARAM_INVALID);
            }

            if (!filter_var($val, FILTER_VALIDATE_INT)) {
                throw new OpException($name . '为不是整数', ERROR_CODE_PARAM_INVALID);
            }

            if ($min >= 0 && intval($val) < $min) {
                throw new OpException($name . '不能小于' . $min, ERROR_CODE_PARAM_INVALID);
            }

            if ($max > 0 && intval($val) > $max) {
                throw new OpException($name . '不能大于' . $max, ERROR_CODE_PARAM_INVALID);
            }
        }

        return $val;
    }

    protected function get_pos_url( $key, $name, $isnull=false ){
        $val = $this->input->post( $key );

        if( !$isnull && !$val ) {
            throw new OpException( $name . '为空', ERROR_CODE_PARAM_INVALID );
        }

        if( !filter_var( $val, FILTER_VALIDATE_URL )){
            throw new OpException( $name . '格式不合法', ERROR_CODE_PARAM_INVALID );
        }

        return $val;
    }


    protected function get_pos_if( $key, $name, $isnull=false ){
        $val = $this->input->post( $key );

        if( !$isnull && $val == '' ) {
            throw new OpException( $name . '为空', ERROR_CODE_PARAM_INVALID );
        }

        if( $val != 0 && $val != 1 ){
            throw new OpException( $name . '格式不合法', ERROR_CODE_PARAM_INVALID );
        }

        return intval($val);
    }

    protected function get_pos_float( $key, $name, $isnull=false ){
        $val = $this->input->post( $key );

        if( !$isnull && !$val ) {
            throw new OpException( $name . '为空', ERROR_CODE_PARAM_INVALID );
        }

        if( !filter_var( $val, FILTER_VALIDATE_FLOAT )){
            throw new OpException( $name . '格式不合法', ERROR_CODE_PARAM_INVALID );
        }

        return $val;
    }


    protected function get_pos_phone( $key, $isnull=false ){
        $val = $this->input->post( $key );

        if( !$isnull && $val == '' ) {
            throw new OpException( '手机号码为空', ERROR_CODE_PARAM_INVALID );
        }

        if( strlen ( $val ) != 11 || ! preg_match ( '/^1[3|4|5|8][0-9]\d{4,8}$/', $val )){
            throw new OpException( '手机号码格式错误', ERROR_CODE_PARAM_INVALID );
        }

        return $val;
    }


    protected function get_pos_in( $key, $name, $in ){
        $val = $this->input->post( $key );

        if( !in_array( $val, $in )){
            throw new OpException( $name . '取值错误', ERROR_CODE_PARAM_INVALID );
        }
        return $val;
    }


    protected function get_pos_string( $key, $name, $isnull=false, $minlen=0, $maxlen=0 ){
        $val = $this->input->post( $key );

        if( !isset( $val )){
            throw new OpException( $name . '缺少', ERROR_CODE_PARAM_INVALID );
        }

        if( !$isnull && $val == '' ) {
            throw new OpException( $name . '为空', ERROR_CODE_PARAM_INVALID );
        }

        $len = mb_strlen( $val );

        if( $minlen > 0 && $len < $minlen ) {
            throw new OpException( $name . '长度不能小于' . $minlen, ERROR_CODE_PARAM_INVALID );
        }

        if( $maxlen > 0 && $len > $maxlen ) {
            throw new OpException( $name . '长度不能大于' . $maxlen , ERROR_CODE_PARAM_INVALID);
        }

        return $val;
    }



    protected function get_user(){
        $session = getHttpHeader( 'session' );
        if( $session == '' ){
            throw new OpException('SESSION不能为空', ERROR_CODE_PARAM_INVALID );
        }

        if( strlen( $session ) != 40 or substr( $session, 0, 7 ) != 'SESSION' ){
            throw new OpException('SESSION格式错误', ERROR_CODE_PARAM_INVALID );
        }

        $this->load->model( 'auth/WxModel', 'wxm' );
        $user = $this->wxm->get_user( $session );


        if( !$user ){
            throw new OpException('用户登陆过期', ERROR_CODE_NOT_LOGIN );
        }

        return $user['userid'];
    }

    protected function deal_with_opexception($e, $options = JSON_FORCE_OBJECT) {
        $this->load->view('outputjson', td_output_array_format($e->getCode(), $e->getMessage(), array(), $options));
    }

    protected function output_data($data, $options = NULL) {
        $this->load->view('outputjson', td_output_array_format(ERROR_CODE_SUCCESS, ERROR_MSG_SUCCESS, $data, $options));
    }

    protected function output_data_success() {
        $this->load->view('outputjson', td_output_array_format(ERROR_CODE_SUCCESS, ERROR_MSG_SUCCESS, array(), JSON_FORCE_OBJECT));
    }


}