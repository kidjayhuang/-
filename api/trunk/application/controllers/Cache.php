<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'Base_Controller.php';

class Cache extends Base_Controller {

    public function __construct() {
        parent::__construct();
    }


    public function article()
    {
        try{
            $this->load->model( 'article/ArticleModel', 'article' );
            $this->article->init_cache_from_db();
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

}
