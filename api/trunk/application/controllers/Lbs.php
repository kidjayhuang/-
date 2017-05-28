<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'Base_Controller.php';

class Lbs extends Base_Controller {


	public function nearby()
	{
        try{

            $this->load->model( 'lbs/lbsLogicModel', 'lbs' );
            $latitude = $this->get_pos_string( 'latitude', '纬度', false, 2, 20 );
            $longitude = $this->get_pos_string( 'longitude', '经度', false, 2, 20 );
            //$btime = $this->get_pos_string( 'btime', '开始时间', true, 10, 10 );
            //$etime = $this->get_pos_string( 'etime', '结束时间', true, 10, 10 );
            $page = $this->get_pos_int( 'page', '页码', false, 1, 1000 );

            $res = $this->lbs->nearby( "$longitude,$latitude", $page );

            $this->output_data( $res );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
	}

    public function batch_create()
    {
        try{

            $this->load->model( 'lbs/lbsLogicModel', 'lbs' );

            $btime = $this->get_pos_string( 'btime', '开始时间', false, 10, 10 );
            $etime = $this->get_pos_string( 'etime', '结束时间', false, 10, 10 );

            $res = $this->lbs->batch_create( strtotime($btime), strtotime($etime) );


            //$this->output_data( $res );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    public function list_column()
    {
        try{

            $this->load->model( 'lbs/BaiduModel', 'lbs' );

            $res = $this->lbs->list_column( );
            $this->output_data( $res );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    public function modify_column()
    {
        try{

            $this->load->model( 'lbs/BaiduModel', 'lbs' );

            $res = $this->lbs->modify_column( );
            $this->output_data( $res );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    public function delete_poi()
    {
        try{

            $this->load->model( 'lbs/BaiduModel', 'lbs' );

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );
            $res = $this->lbs->delete( $activity_id );
            //$this->output_data( $res );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /*
     * "location": [
          113.861783413,
          22.5763098416
        ],
     */

    public function get_poi()
    {
        try{

            $this->load->model( 'lbs/BaiduModel', 'lbs' );

            $activity_id = $this->get_pos_string( 'activity_id', '活动id', false, 1, 99999999 );
            $res = $this->lbs->get_poi( $activity_id );
            $this->output_data( $res );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

}
