<?php

require_once APPPATH . '/models/BaseModel.php';

class ActivityLogicModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model( 'activity/ActivityModel', 'activity' );
        $this->load->model( 'activity/MemberModel', 'member' );
        $this->load->model( 'auth/UserModel', 'user' );
        $this->load->model( 'activity/VerifyModel', 'verify' );
        $this->load->model( 'activity/NoticeModel', 'notice' );

    }

    /**
     * @param $user_id
     * @param $data
     * @return mixed
     * @throws OpException
     * 发布活动，在使用
     */
    public function create( $user_id, $data ){

        $user_activity_count = $this->user->get_activity_count( $user_id );
        if( $user_activity_count >= LIMIT_CREATE_ACTIVITY ){
            throw new OpException( '创建活动数量超出限制', ERROR_CODE_ACTIVITY_EXCEED );
        }

        $real_data = $data;
        unset( $real_data['action_date'] );
        $real_data['action_time'] = strtotime( $data['action_date'] . ' ' . $data['action_time'] );
        $real_data['create_time	'] = time();
        $real_data['member_count'] = 1;
        $real_data['status'] = ACTIVITY_STATUS_NORMAL;

        $activity_id = $this->activity->create( $user_id, $real_data );

        //设置群成员信息
        $this->member->create( $activity_id, $user_id );

        $this->user->set_activity_count( $user_id, $user_activity_count+1 );

        return $activity_id;
    }

    /**
     * @param $circle_id
     * @param $user_id
     * @param $remark
     * @return int
     * @throws OpException
     * 加入活动
     */
    public function join( $activity_id, $user_id, $remark ){
        $activity_info = $this->activity->get_basic_info( $activity_id );

        //判断用户是否参加活动
        if( $this->in_activity( $activity_id, $user_id, $activity_info['creator'] ) ){
            throw new OpException( '用户已经在当前圈子', ERROR_CODE_IN_ACTIVITY );
        }

        //判断是否已经审核过
        if( $this->verify->in_wait_verify( $activity_id, $user_id ) ){
            throw new OpException( '你的申请已经在审核中', ERROR_CODE_IN_ACTIVITY );
        }

        if( $activity_info['join_verify'] == '0' ){

            //直接加入成员
            $this->member->join( $activity_id, $user_id );

            //设置成员总数
            $this->activity->member_join( $activity_id );

            return JOIN_RESULT_IN;
        }

        //插入审核记录
        $verify_id = $this->verify->add( $activity_id, $user_id, $remark );

        //记录消息
        $notice_info = array(
            'activity_id' => $activity_id,
            'user_id' => $user_id,
            'verify_id' => $verify_id,
            'remark' => $remark,
            'result' => VERIFY_STATUS_WAIT,
            'content' => '申请加入 ' . $activity_info['title']
        );

        $notice_id = $this->notice->add_join( $notice_info );

        //设置审核信息对应的通知id
        $this->verify->set_notice_id( $verify_id, $notice_id );
        $this->notice->assign_notice( $notice_id, $activity_info['creator'] );

        return JOIN_RESULT_VERIFY;

    }


    /**
     * @param $user_id
     * @return mixed
     */
    public function my_create( $user_id, $page ){
        $list = $this->member->my_create( $user_id, $page );
        $res = array();

        if( is_array( $list ) && count( $list ) > 0 ){
            foreach( $list as $activity_id ){
                $activity_info = $this->activity->get_basic_info( $activity_id );
                $res_info = array();
                $res_info['id'] = $activity_id;
                $res_info['month'] = date( 'm', $activity_info['create_time'] ) . '月';
                $res_info['day'] = date( 'd', $activity_info['create_time'] ) . '日';
                $res_info['title'] = $activity_info['title'];
                $res_info['action_time'] = timestamp_trans( $activity_info['action_time'] );
                $res_info['address'] = $activity_info['address'] . $activity_info['address_detail'];
                $res_info['latitude'] = $activity_info['latitude'];
                $res_info['longitude'] = $activity_info['longitude'];
                $res_info['member_count'] = $activity_info['member_count'];
                $res_info['sum_count'] = $activity_info['member_begin'];
                $member_id_list = $this->member->index_list( $activity_id );
                $member_list = array();
                $member_info = array(
                    'user_id' => $user_id,
                    'head' => $this->user->get_head( $user_id )
                );
                $member_list[] = $member_info;

                if( is_array( $member_id_list) && count( $member_id_list ) > 0 ){
                    foreach( $member_id_list as $u_id ){
                        $member_info = array(
                            'user_id' => $u_id,
                            'head' => $this->user->get_head( $u_id )
                        );
                        $member_list[] = $member_info;
                    }
                }

                $res_info['member'] = $member_list;

                $res[] = $res_info;
            }
        }

        return $res;
    }

    public function notice_count( $user_id ){
        $last_time = $this->user->get_notice_time( $user_id );
        return $this->notice->count( $user_id, $last_time );
    }

    public function user_circle_list( $user_id ){
        $id_list = $this->member->my_circle( $user_id );
        $res = array();
        if( !$id_list ){
            return $res;
        }

        foreach( $id_list as $circle_id ){
            $circle_info = $this->circle->get_basic_info( $circle_id );
            $result_info = array(
                'circle_id' => $circle_id,
                'name' => $circle_info['name'],
                'thumb' => $circle_info['thumb'],
                'desc' => $circle_info['desc'],
                'times' => timestamp_trans( $circle_info['update_time'] )
            );
            $res[] = $result_info;
        }
        return $res;
    }

    public function set_update_time( $circle_id ){
        $this->circle->set_attr( $circle_id, 'update_time', time() );
    }

    public function set_attr( $circle_id, $admin_id, $field, $val ){
        $circle_info = $this->circle->get_basic_info( $circle_id );
        if( !$circle_info ){
            throw new OpException('圈子不存在', ERROR_CODE_NOT_EXIST);
        }

        if( $circle_info['creator'] != $admin_id ){
            throw new OpException( '不是圈主', ERROR_CODE_USER_IDENTIFY );
        }

        $this->circle->set_attr( $circle_id, $field, $val );

    }


    public function kick( $user_id, $circle_id, $admin_id ){
        $circle_info = $this->circle->get_basic_info( $circle_id );
        if( !$circle_info ){
            throw new OpException('圈子不存在', ERROR_CODE_NOT_EXIST);
        }

        if( !$this->in_activity( $circle_id, $user_id, $circle_info['creator'])){
            throw new OpException( '用户不在当前圈子', ERROR_CODE_IN_CIRCLE );
        }

        if( !$this->in_admin( $circle_id, $admin_id, $circle_info['creator']) ){
            throw new OpException( '不是管理员', ERROR_CODE_NOT_ADMIN );
        }

        $user_info = $this->user_simple_profile( $circle_id, $user_id );

        $this->member->del( $circle_id, $user_id );
        //设置成员总数
        $this->circle->member_quit( $circle_id );

        //增加退群通知
        //写通知
        $quit_notice = array(
            'circle_id' => $circle_id,
            'user_id' => $admin_id,
            'verify_id' => 0,
            'result' => 0,
            'remark' => '',
            'content' =>  '把用户 ' . $user_info['nick_name'] . ' 踢出去了' . $circle_info['name']
        );
        $notice_id = $this->notice->kick( $quit_notice );
        $this->_assign_notice( $notice_id, $circle_info['creator'], $circle_id );

    }

    public function quit( $user_id, $circle_id ){
        $circle_info = $this->circle->get_basic_info( $circle_id );
        if( !$circle_info ){
            throw new OpException('圈子不存在', ERROR_CODE_NOT_EXIST);
        }

        if( !$this->in_activity( $circle_id, $user_id, $circle_info['creator'])){
            throw new OpException( '用户不在当前圈子', ERROR_CODE_IN_CIRCLE );
        }

        if( $circle_info['creator'] == $user_id ){
            throw new OpException( '圈主不能退出', ERROR_CODE_IN_CIRCLE );
        }

        $this->member->del( $circle_id, $user_id );
        //设置成员总数
        $this->circle->member_quit( $circle_id );

        //增加退群通知
        //写通知
        $quit_notice = array(
            'circle_id' => $circle_id,
            'user_id' => $user_id,
            'verify_id' => 0,
            'result' => 0,
            'remark' => '',
            'content' =>  '退出了' . $circle_info['name']
        );
        $notice_id = $this->notice->quit( $quit_notice );


        $this->_assign_notice( $notice_id, $circle_info['creator'], $circle_id );

    }

    public function notice_list( $user_id, $page=1 ){
        $notice_list = $this->notice->get_list( $user_id, $page );
        if( is_array( $notice_list) && count($notice_list) > 0 ){
            foreach( $notice_list as &$notice ){
                $user_info = $this->user_simple_profile( $notice['circle_id'], $notice['user_id']);
                if( $user_info ){
                    $notice['user_head'] = $user_info['avatar_url'];
                    $notice['user_name'] = $user_info['nick_name'];
                    $notice['create_time'] = timestamp_trans( $notice['create_time']);
                }
                else{
                    unset( $notice );
                }
            }
        }

        //这里更新查看通知的时间
        $this->user->set_notice_time( $user_id );

        return $notice_list;
    }

    //分页的逻辑还需要更多用户的测试
    public function member_list( $circle_id, $page ){
        $circle_info = $this->circle->get_basic_info( $circle_id );
        if( !$circle_info ){
            throw new OpException('圈子不存在', ERROR_CODE_NOT_EXIST);
        }

        $res = array();
        if( $page == 1 ) {
            $res['creator'] = $this->user_simple_profile($circle_id, $circle_info['creator']);
            $res['creator']['user_id'] = $circle_info['creator'];

            $admin_list = $this->member->admin_list( $circle_id );
            $res['admin'] = [];

            if( is_array( $admin_list ) && count( $admin_list )>0 ){
                foreach( $admin_list as $admin_id ){
                    $admin_info = $this->user_simple_profile( $circle_id, $admin_id );
                    $admin_info['user_id'] = $admin_id;
                    $res['admin'][] = $admin_info;
                }
            }

        }

        $member_list = $this->member->member_list( $circle_id, $page );
        $res['member'] = [];

        if( is_array( $member_list ) && count( $member_list )>0 ){
            foreach( $member_list as $user_id ){
                $user_info = $this->user_simple_profile( $circle_id, $user_id );
                $user_info['user_id'] = $user_id;
                $res['member'][] = $user_info;
            }
        }

        return $res;
    }

    /*
     * 设置管理员
     */
    public function admin( $creator, $circle_id, $user_id, $oper ){

        $circle_info = $this->circle->get_basic_info( $circle_id );
        if( $creator != $circle_info['creator'] ) {
            throw new OpException('不是圈主，不能设置管理员', ERROR_CODE_NOT_ADMIN);
        }

        if( !$this->in_activity( $circle_id, $user_id, $creator ) ){
            throw new OpException( '用户不在当前圈子', ERROR_CODE_IN_CIRCLE );
        }

        if( $oper == '1' ){
            $this->member->set_admin( $circle_id, $user_id );
        }
        else{
            $this->member->cancel_admin( $circle_id, $user_id );
        }

    }

    public function do_verify( $user_id, $verify_id, $result, $remark ){
        $verify_info = $this->verify->get( $verify_id );
        if( !$verify_info ){
            throw new OpException( '审核信息不存在', ERROR_CODE_NOT_EXIST );
        }

        if( $verify_info['result'] != VERIFY_STATUS_WAIT ){
            throw new OpException( '该申请已经审核', ERROR_CODE_NOT_EXIST );
        }

        $circle_info = $this->circle->get_basic_info( $verify_info['circle_id'] );
        if( !$this->in_admin($verify_info['circle_id'], $user_id, $circle_info['creator'] )) {
            throw new OpException('不是管理员', ERROR_CODE_NOT_ADMIN);
        }

        //设置审核结果
        $this->verify->verify( $verify_info['circle_id'], $verify_id, $result, $remark, $user_id );
        $user_info = $this->user_simple_profile( $verify_info['circle_id'], $user_id );

        if( $result == VERIFY_STATUS_REJECT ){

            //写通知
            $add_notice = array(
                'circle_id' => $verify_info['circle_id'],
                'user_id' => $user_id,
                'verify_id' => 0,
                'result' => 2,
                'remark' => '拒绝理由:' . $remark,
                'content' =>  '拒绝了你加入 ' . $circle_info['name'] . ' 的申请'
            );

            $this->notice->add_verify( $verify_info['user_id'], $add_notice );

            //更新管理员的通知
            $up_notice = array(
                'remark' => '审核人:' . $user_info['nick_name'] . ';申请留言:' . $verify_info['remark'] ,
                'content' => '加入 ' . $circle_info['name'] . ' 的申请被拒绝' . '理由:' . $remark,
                'result' => VERIFY_STATUS_REJECT
            );
            $this->notice->update_verify( $verify_info['notice_id'], $up_notice );

            return true;
        }

        //通过的话，需要加入圈子，再写通知
        $this->member->join( $verify_info['circle_id'], $verify_info['user_id'] );

        //设置成员总数
        $this->circle->member_join( $verify_info['circle_id'] );

        //新增申请人的通知
        $add_notice = array(
            'circle_id' => $verify_info['circle_id'],
            'user_id' => $user_id,
            'verify_id' => 0,
            'result' => 1,
            'remark' => $remark,
            'content' => '通过了你加入 ' . $circle_info['name'] . ' 的申请'
        );

        $this->notice->add_verify( $verify_info['user_id'], $add_notice );

        //更新管理员的通知
        $up_notice = array(
            'remark' => '审核人:' . $user_info['nick_name'] . ';申请留言:' . $verify_info['remark'] ,
            'content' => '加入 ' . $circle_info['name'] . ' 的申请已通过',
            'result' => VERIFY_STATUS_PASS
        );

        $this->notice->update_verify( $verify_info['notice_id'], $up_notice );
        return true;

    }

    public function get_basic_info( $circle_id, $user_id ){
        $circle_info = $this->circle->get_basic_info( $circle_id );

        //获取创建人信息
        $user_info = $this->user_simple_profile( $circle_id, $circle_info['creator']);
        $circle_info['creator_nick'] = $user_info['nick_name'];
        $circle_info['creator_head'] = $user_info['avatar_url'];
        if( $circle_info['creator'] == $user_id ){
            $circle_info['is_creator'] = 1;
            $circle_info['is_admin'] = 1;
        }else{
            $circle_info['is_creator'] = 0;
            $is_admin = $this->member->is_admin( $circle_id, $user_id );
            $circle_info['is_admin'] = intval( $is_admin );

        }

        $circle_info['is_member'] = 0;
        if( $this->in_activity( $circle_id, $user_id, $circle_info['creator'])){
            $circle_info['is_member'] = 1;
        }

        return $circle_info;
    }

    public function user_simple_profile( $circle_id, $user_id ){
        $user_basic = $this->user->simple_info( $user_id );
        $remark = $this->member->get_remark( $circle_id, $user_id );
        if( $remark !== false ){
            $user_basic['nick_name'] = $remark;
        }
        return $user_basic;
    }

    public function  in_activity( $activity_id, $user_id, $creator ){
        if( $user_id == $creator ){
            return true;
        }

        if( $this->member->is_member( $activity_id, $user_id ) ){
            return true;
        }

        return false;
    }

}