<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'Base_Controller.php';

class User extends Base_Controller {

    public function __construct() {
        parent::__construct();

        $this->_user_id = 0;
        $this->_circle_id = 0;
    }

    public function simple_profile(){
        try{
            $this->_init( true );
            $res = $this->circleLogic->user_simple_profile( $this->_circle_id, $this->_user_id );
            $this->output_data( $res  );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }

    }

    public function reply_to_me(){

        try{
            $this->_init( true, true );
            $page = $this->get_pos_int( 'page', '页面', false, 1, 100 );
            $res = $this->articleLogic->reply_to_me( $page );
            $this->output_data( array( 'list' => $res ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    public function create_tips(){

        try{
            $this->_init( );
            $res = $this->circleLogic->create_tips( $this->_user_id );
            $this->output_data( $res );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    public function notice_count(){

        try{
            $this->_init( );
            $times = $this->circleLogic->notice_count( $this->_user_id );
            $this->output_data( array( 'count' => $times ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    public function reply_to_me_count(){

        try{
            $this->_init( true );
            $times = $this->articleLogic->reply_to_me_count( );
            $this->output_data( array( 'count' => $times ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    public function update_times(){

        try{
            $this->_init( true );
            $times = $this->articleLogic->update_times(   );
            $this->output_data( array( 'times' => $times ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    public function my_circle(){

        try{
            $this->_init();
            $res = $this->circleLogic->my_circle_list(  $this->_user_id );
            $this->output_data( $res );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    public function circle_list(){

        try{
            $this->_init();
            $res = $this->circleLogic->user_circle_list(  $this->_user_id );
            $this->output_data( array( 'list' => $res ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    private function _init( $need_circle = false, $need_update_time = false  ){
        $this->_user_id = $this->get_user();
        if( $need_circle ){
            $circle_id = $this->get_pos_int( 'circle_id', '圈子ID', false, 10001, 99999999 );

            $this->load->model('circle/CircleModel', 'circle');

            $circle_info = $this->circle->get_basic_info($circle_id);
            if (!$circle_info) {
                throw new OpException('圈子不存在', ERROR_CODE_NOT_EXIST);
            }
            $this->_circle_id = $circle_id;
            $this->load->model('article/ArticleLogicModel', 'articleLogic');
            $this->articleLogic->init($this->_user_id, $circle_id, $circle_info, $need_update_time);
        }

        $this->load->model( 'circle/CircleLogicModel', 'circleLogic' );

    }

}
