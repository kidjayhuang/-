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
            $pay_type = $this->get_pos_in( 'pay_type', '付款方式', array( PAY_TYPE_AA, PAY_TYPE_I_PAY, PAY_TYPE_FREE ) );
            $price_begin = $this->get_pos_int( 'price_begin', '最低人均消费', true, 0, 999999 );
            $price_end = $this->get_pos_int( 'price_end', '最高人均消费', true, 0, 999999 );

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

    /**
     * 我加入的活动列表
     */
    public function my_join()
    {
        try{

            $user_id = $this->get_user();

            $page = $this->get_pos_int( 'page', '页码', false, 1, 100 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $result = $this->logic->my_join( $user_id, $page );

            $this->output_data( array( 'list' => $result ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    /**
     * 活动基础信息
     */
    public function rich_profile()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $result = $this->logic->rich_profile( $user_id, $activity_id );

            $this->output_data(  $result  );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 活动成员信息，只返回头像
     */
    public function member()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->member_list( $user_id, $activity_id );

            $this->output_data( $res );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 活动成员信息
     */
    public function member_rich()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->member_list_rich( $user_id, $activity_id );

            $this->output_data( $res );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    /**
     * 设置状态
     */
    public function set_status()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );
            $status = $this->get_pos_in( 'status', '活动状态', array( ACTIVITY_STATUS_NORMAL, ACTIVITY_STATUS_STOP, ACTIVITY_STATUS_FINISH, ACTIVITY_STATUS_CANCEL ) );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $this->logic->set_status( $user_id, $activity_id, $status );

            $this->output_data_success();

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    /**
     * 查询审核信息
     */
    public function verify_info()
    {
        try{

            $user_id = $this->get_user();

            $verify_id = $this->get_pos_int( 'verify_id', '审核信息id', false, 1, 99999999 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->verify_info( $user_id, $verify_id );

            $this->output_data( $res );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }


    /**
     * 查询审核信息
     */
    public function verify_remark()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );
            $search_user_id = $this->get_pos_int( 'user_id', '用户id', false, 1, 99999999 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->verify_remark( $user_id, $search_user_id, $activity_id);

            $this->output_data( $res );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 查询活动状态
     */
    public function get_status()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->get_status( $user_id, $activity_id);

            $this->output_data( array( 'status' => $res ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 审核
     */
    public function verify()
    {
        try{

            $user_id = $this->get_user();

            $remark = $this->get_pos_string( 'remark', '审核备注', true, 0, 64 );
            $verify_id = $this->get_pos_int( 'verify_id', '审核id', false, 1, 9999999999 );
            $result = $this->get_pos_in( 'result', '审核结果', array( VERIFY_STATUS_PASS, VERIFY_STATUS_REJECT) );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $this->logic->do_verify( $user_id, $verify_id, $result, $remark );

            $this->output_data_success();

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 移出活动
     */
    public function kick()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );
            $kick_user_id = $this->get_pos_int( 'user_id', '用户id', false, 1,  9999999999);

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $this->logic->kick( $user_id, $kick_user_id, $activity_id);

            $this->output_data_success();

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 退出活动
     */
    public function quit()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->quit( $user_id, $activity_id);

            $this->output_data( array( 'status' => $res ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 消息列表
     */
    public function notice()
    {
        try{

            $user_id = $this->get_user();

            $page = $this->get_pos_int( 'page', '页码', false, 1,  10000);

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->notice_list( $user_id, $page );

            $this->output_data( array( 'list' => $res ) );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 消息列表
     */
    public function notice_count()
    {
        try{

            $user_id = $this->get_user();

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->notice_count( $user_id );

            $this->output_data( array( 'count' => $res ) );
        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 消息列表
     */
    public function reply()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );
            $type = $this->get_pos_in( 'type', '回复类型', array( REPLY_TYPE_ACTIVITY, REPLY_TYPE_REPLY ) );
            $to_user_id = $this->get_pos_int( 'to_user_id', '回复的用户ID', true, 0, 99999999 );
            $content = $this->get_pos_string( 'content', '回复内容', false, 2, 200 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->do_reply( $user_id, $to_user_id, $activity_id, $type, $content );

            $this->output_data( array( 'reply_id' => $res ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 回复列表
     */
    public function reply_list()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );
            $page = $this->get_pos_int( 'page', '页码', false, 1,  10000);

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->reply_list( $user_id, $activity_id, $page );

            $this->output_data( $res );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 回复数量
     */
    public function reply_count()
    {
        try{

            $user_id = $this->get_user();

            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $res = $this->logic->reply_count(  $activity_id );

            $this->output_data( array( 'count' => $res ) );

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }

    /**
     * 回复数量
     */
    public function reply_del()
    {
        try{

            $user_id = $this->get_user();
            $reply_id = $this->get_pos_int( 'reply_id', '回复id', false, 1, 99999999 );
            $activity_id = $this->get_pos_int( 'activity_id', '活动id', false, 1, 99999999 );

            $this->load->model( 'activity/ActivityLogicModel', 'logic' );

            $this->logic->reply_del( $activity_id, $user_id, $reply_id );

            $this->output_data_success();

        } catch ( OpException $e) {
            $this->deal_with_opexception($e);
        }
    }
}
