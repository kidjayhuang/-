<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'Base_Controller.php';

class Auth extends Base_Controller {


	public function login()
	{
        try{
            $data = array(
                'code' => getHttpHeader( 'code' ),
                'raw_data' => urldecode(getHttpHeader( 'rawData' )),
                'signature' => getHttpHeader( 'signature' ),
                'encrypted_data' => getHttpHeader( 'encryptedData' ),
                'iv' => getHttpHeader( 'iv' )
            );

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules('code', 'wechat user code', 'required|max_length[32]');
            $this->form_validation->set_rules('raw_data', 'raw_data', 'required');
            $this->form_validation->set_rules('signature', 'signature', 'required');
            $this->form_validation->set_rules('encrypted_data', 'encrypted_data', 'required');
            $this->form_validation->set_rules('iv', 'iv', 'required');


            if ($this->form_validation->run() == FALSE){
                throw new OpException( validation_errors(), ERROR_CODE_PARAM_INVALID );
            }

            $this->load->model( 'auth/WxModel', 'wxm' );

            $session = $this->wxm->login( $data['code'], $data['raw_data'], $data['signature'], $data['encrypted_data'], $data['iv'] );

            $this->output_data( array( 'session' => $session ) );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
	}


    public function check()
    {
        try{
            $session = getHttpHeader( 'session' );

            $this->load->model( 'auth/WxModel', 'wxm' );

            $flag = $this->wxm->check( $session );

            $this->output_data( array( 'expire' => $flag ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

}
