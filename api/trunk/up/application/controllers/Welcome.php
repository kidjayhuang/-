<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'Base_Controller.php';

class Welcome extends Base_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
        $this->load->library( "form_validation" );
        $data = array(
            'username' => 'johndoe',
            'password' => 'mypassword',
            'email' => 'mypassword'
        );

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('email', 'Email', 'required|is_unique[users.email]');
        if ($this->form_validation->run() == FALSE){
            echo validation_errors();
        }
    }

    private function rediscli(){

        try{
            $this->load->model('RedisCliModel', 'redis');
            $this->redis->connect();
            $this->redis->set('test1', 'rayqin test');
            echo $this->redis->get( 'test1' );
        }
        catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }

    }

    private function redis()
    {

        $host = "localhost";
        $port = 6379;
        $instanceid = "";
        $pwd = "";

        $redis = new Redis();
        //连接redis
        if ($redis->connect($host, $port) == false) {
            die($redis->getLastError());
        }
        //鉴权
        if ($redis->auth('pp2016') == false) {
            die($redis->getLastError());
        }

        /**接下来可以愉快的开始操作redis实例，可以参考：https://github.com/phpredis/phpredis */

        //设置key
        if ($redis->set("redis", "tencent") == false) {
            die($redis->getLastError());
        }
        echo "set key redis suc, value is:tencent\n";

        //获取key
        $value = $redis->get("redis");
        echo "get key redis is:".$value."\n";


    }
}
