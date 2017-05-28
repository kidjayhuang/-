<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * 初始化数据脚本，生产环境执行后要设置为不可访问
 *
 * rayqin created at 2016.12.07
 *
 */

require_once 'Base_Controller.php';

class Data extends Base_Controller {


    //初始化测试数据
	public function register_code()
	{
        try{


            $this->load->model( 'circle/CodeModel', 'dm' );

            $this->dm->init();

            $this->output_data_success();

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
	}



}
