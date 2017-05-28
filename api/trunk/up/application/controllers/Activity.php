<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'Base_Controller.php';

class Activity extends Base_Controller {

    /**
     * 发起活动，完成
     */
    public function create()
    {
        try{

            $user_id = $this->get_user();

            $title = $this->get_pos_string( 'title', '活动标题', false, 2, 40 );
            $desc = $this->get_pos_string( 'desc', '活动描述', false, LIMIT_SIMPLE_ARTICLE_TEXT_MIN, LIMIT_SIMPLE_ARTICLE_TEXT_MAX );
            $type = $this->get_pos_in( 'type', '活动类型', array( ACTIVITY_TYPE_SIMPLE ) );
            $pic_json_str = $this->get_pos_string( 'pics', '照片列表', false, 2, LIMIT_SIMPLE_ARTICLE_TEXT_MAX );
            $pic_list = json_decode( $pic_json_str );
            $count = count( $pic_list );
            if( is_array( $pic_list ) && $count > 0 ){
                foreach( $pic_list as $pic ){
                    if( !filter_var( $pic, FILTER_VALIDATE_URL )){
                        throw new OpException( '照片URL格式不合法', ERROR_CODE_PARAM_INVALID );
                    }
                }
            }

            $search = $this->get_pos_if( 'search', '是否可被搜索' );
            $join_verify = $this->get_pos_if( 'join_verify', '加入活动是否需要审核' );
            $verify_request = $this->get_pos_string( 'verify_request', '加入活动信息要求', true, 0, 200 );
            $member_begin = $this->get_pos_int( 'member_begin', '最少人数' );
            $member_end = $this->get_pos_int( 'member_begin', '最多人数' );
            $action_date = $this->get_pos_string( 'action_date', '活动日期', false, 10, 10 );
            $action_time = $this->get_pos_string( 'action_time', '活动时间', false, 5, 5 );
            $address = $this->get_pos_string( 'address', '活动地址', false, 10, 200 );
            $address_detail = $this->get_pos_string( 'address_detail', '地址详情', false, 0, 200 );
            $latitude = $this->get_pos_float( 'latitude', '经度', false, 0, 9999999999 );
            $longitude = $this->get_pos_float( 'longitude', '纬度', false, 0, 9999999999 );
            $pay_type = $this->get_pos_in( 'pay_type', '付款方式', array( PAY_TYPE_AA, PAY_TYPE_I_PAY ) );
            $price_begin = $this->get_pos_int( 'price_begin', '人均消费', false, 0, 999999 );
            $price_end = $this->get_pos_int( 'price_end', '人均消费', false, 0, 999999 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $activity_id = $this->logic->create( $user_id, array(
                'title' => $title,
                'desc' => $desc,
                'type' => $type ,
                'pic_list' => $pic_json_str,
                'search' => $search,
                'join_verify' => $join_verify,
                'verify_request' => $verify_request,
                'member_begin' => $member_begin,
                'member_end' => $member_end,
                'action_date' => $action_date,
                'action_time' => $action_time,
                'address' => $address,
                'address_detail' => $address_detail,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'pay_type' => $pay_type,
                'price_begin' => $price_begin,
                'price_end' => $price_end
            ) );

            $this->output_data( array( 'activity_id' => $activity_id ) );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 用户加入活动
     */
    public function join()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动ID', false, 1, 99999999 );
            $remark = $this->get_pos_string( 'remark', '申请备注', true, 0, 60 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $result = $this->logic->join( $activity_id, $user_id, $remark);

            $this->output_data( array( 'result' => $result ) );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    /**
     * 我创建的活动列表
     */
    public function my_create()
    {
        try{

            $user_id = $this->get_user();

            $page = $this->get_pos_int( 'page', '页码', false, 1, 100 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $result = $this->logic->my_create( $user_id, $page );

            $this->output_data( array( 'list' => $result ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }



    public function kick()
    {
        try{

            $admin_id = $this->get_user();

            $circle_id = $this->get_pos_int( 'circle_id', '圈子id', false, 10000,  9999999999);
            $user_id = $this->get_pos_int( 'user_id', '用户id', false, 10000,  9999999999);

            $this->load->model( 'circle/CircleLogicModel', 'logic' );

            $this->logic->kick( $user_id, $circle_id, $admin_id );

            $this->output_data_success( );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    public function quit()
    {
        try{

            $user_id = $this->get_user();

            $circle_id = $this->get_pos_int( 'circle_id', '圈子id', false, 10000,  9999999999);

            $this->load->model( 'circle/CircleLogicModel', 'logic' );

            $this->logic->quit( $user_id, $circle_id );

            $this->output_data_success( );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    public function notice()
    {
        try{

            $user_id = $this->get_user();

            $page = $this->get_pos_int( 'page', '时间戳', false, 1,  10000);

            $this->load->model( 'circle/CircleLogicModel', 'logic' );

            $res = $this->logic->notice_list( $user_id, $page );

            $this->output_data( array( 'list' => $res ) );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    public function member()
    {
        try{

            $creator = $this->get_user();

            $circle_id = $this->get_pos_int( 'circle_id', '圈子id', false, 10000, 9999999999 );
            $page = $this->get_pos_int( 'page', '时间戳', false, 1,  10000);

            $this->load->model( 'circle/CircleLogicModel', 'logic' );

            $res = $this->logic->member_list( $circle_id, $page );

            $this->output_data( $res );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    public function verify()
    {
        try{

            $user_id = $this->get_user();

            $remark = $this->get_pos_string( 'remark', '审核备注', true, 0, 64 );
            $verify_id = $this->get_pos_int( 'verify_id', '审核id', false, 1, 9999999999 );
            $result = $this->get_pos_in( 'result', '审核结果', array( VERIFY_STATUS_PASS, VERIFY_STATUS_REJECT) );

            $this->load->model( 'circle/CircleLogicModel', 'logic' );

            $this->logic->do_verify( $user_id, $verify_id, $result, $remark );

            $this->output_data_success();
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }



    public function profile()
    {
        try{
            $user_id = $this->get_user();

            $circle_id = $this->get_pos_int( 'circle_id', '圈子ID', false, 10001, 99999999 );

            $this->load->model( 'circle/CircleLogicModel', 'logic' );

            $data = $this->logic->get_basic_info( $circle_id, $user_id );

            $this->output_data( $data );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

}
