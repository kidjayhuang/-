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
        $this->load->model( 'activity/ReplyModel', 'reply' );
        $this->load->model( 'lbs/LbsLogicModel', 'lbs' );

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
        $real_data['create_time'] = time();
        $real_data['member_count'] = 1;
        $real_data['status'] = ACTIVITY_STATUS_NORMAL;

        $activity_id = $this->activity->create( $user_id, $real_data );

        $real_data['id'] = $activity_id;
        $this->lbs->create( $real_data );

        //设置群成员信息
        $this->member->create( $activity_id, $user_id );

        $this->user->set_activity_count( $user_id, $user_activity_count+1 );

        return $activity_id;
    }

    /**
     * @param $circle_i
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

        //判断活动状态
        if( !$activity_info['status'] == ACTIVITY_STATUS_NORMAL ){
            throw new OpException( '无法加入活动', ERROR_CODE_IN_ACTIVITY );
        }

        if( $activity_info['join_verify'] == '0' ){

            //直接加入成员
            $this->member->join( $activity_id, $user_id );

            //设置成员总数
            $this->activity->member_join( $activity_id );

            //写通知
            $notice = array(
                'activity_id' => $activity_id,
                'user_id' => $user_id,
                'verify_id' => 0,
                'result' => 0,
                'remark' => '',
                'content' =>  '加入了 ' . $activity_info['title']
            );
            $notice_id = $this->notice->add_join( $notice );

            $this->notice->assign_notice( $notice_id, $activity_info['creator'] );

            return JOIN_RESULT_IN;
        }

        //
        if( $this->verify->my_wait_count($user_id) >= MEMBER_COUNT_PAGE ){
            throw new OpException( '审核中的活动超出限制', ERROR_CODE_IN_ACTIVITY );
        }

        //插入审核记录
        $verify_id = $this->verify->add( $activity_id, $user_id, $remark );

        //通知
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
                $res[] = $this->_get_create_list( $activity_id, $user_id );
            }
        }

        return $res;
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function my_join( $user_id, $page ){
        $vc = 0;
        $res = array();

        if( $page <= 1 ) {
            $verify_list = $this->verify->my_wait_list($user_id);

            if (is_array($verify_list) && count($verify_list) > 0) {
                foreach ($verify_list as $activity_id) {
                    $res_info = $this->_get_join_list($activity_id, $user_id);
                    $res_info['join_status'] = JOIN_RESULT_VERIFY;
                    $res[] = $res_info;
                }
                $vc = count($verify_list);
            }
        }

        $list = $this->member->my_join( $user_id, $page );

        if( is_array( $list ) && count( $list ) > 0 ){
            array_slice( $list, $vc-1, MEMBER_COUNT_PAGE-$vc );
            foreach( $list as $activity_id ){
                $res_info = $this->_get_join_list( $activity_id, $user_id );
                $res_info['join_status'] = JOIN_RESULT_IN;
                $res[] = $res_info;
            }
        }

        return $res;
    }

    /**
     * @param $activity_id
     * @param $user_id
     * @return array
     * 活动详情
     */
    public function rich_profile( $user_id, $activity_id ){
        $activity_info = $this->activity->get_rich_info( $activity_id );
        $activity_info['is_creator'] = 0;
        if( $activity_info['creator'] == $user_id ){
            $activity_info['is_creator'] = 1;
        }
        $activity_info['action_time'] = timestamp_trans( $activity_info['action_time'] );
        $activity_info['create_time'] = timestamp_trans( $activity_info['create_time'] );
        $activity_info['address'] = $activity_info['address'] . $activity_info['address_detail'];
        $activity_info['pic_list'] = json_decode($activity_info['pic_list']);
        unset( $activity_info['address_detail']);

        $activity_info['creator_info'] = $this->user->simple_info( $activity_info['creator'] );

        return $activity_info;
    }

    /**
     *用户成员,只返回id和头像
     */
    public function member_list( $user_id, $activity_id ){
        $activity_info = $this->activity->get_rich_info( $activity_id );
        $member_id_list = $this->member->member_list( $activity_id );

        $in = 0;
        $inv = 0;

        $res = array();
        $member_list = array();

        $info = array(
            'user_id' => $activity_info['creator'],
            'head' => $this->user->get_head( $activity_info['creator'] )
        );
        $member_list[] = $info;

        if( is_array( $member_id_list ) && count( $member_id_list )>0 ){
            foreach( $member_id_list  as $uid ){
                $info = array(
                    'user_id' => $uid,
                    'head' => $this->user->get_head( $uid )
                );
                $member_list[] = $info;
                if( !$in && $uid == $user_id ){
                    $in = 1;
                }
            }
        }

        $verify_list = array();
        $verify_ori_list = $this->verify->wait_list( $activity_id );
        if( is_array( $verify_ori_list ) && count( $verify_ori_list )>0 ){
            foreach( $verify_ori_list  as $vinfo ) {
                $info = array(
                    'user_id' => $vinfo['user_id'],
                    'head' => $this->user->get_head( $vinfo['user_id'] )
                );
                $verify_list[] = $info;
                if( !$inv && $vinfo['user_id'] == $user_id ){
                    $inv = 1;
                }
            }
        }

        //查询审核列表

        $res['member'] = $member_list;
        $res['verify'] = $verify_list;
        $res['in_activity'] = $in;
        $res['in_verify'] = $inv;
        return $res;
    }

    /**
     *用户成员,只返回id和头像
     */
    public function member_list_rich( $user_id, $activity_id ){
        $activity_info = $this->activity->get_rich_info( $activity_id );
        $member_id_list = $this->member->member_list( $activity_id );

        $res = array();
        $member_list = array();

        $info = array(
            'user_id' => $activity_info['creator'],
            'profile' => $this->user->simple_info( $activity_info['creator'] )
        );
        $member_list[] = $info;

        if( is_array( $member_id_list ) && count( $member_id_list )>0 ){
            foreach( $member_id_list  as $uid ){
                $info = array(
                    'user_id' => $uid,
                    'profile' => $this->user->simple_info( $uid )
                );
                $member_list[] = $info;
            }
        }

        $verify_list = array();
        $verify_ori_list = $this->verify->wait_list( $activity_id );
        if( is_array( $verify_ori_list ) && count( $verify_ori_list )>0 ){
            foreach( $verify_ori_list  as $vinfo ) {
                $info = array(
                    'user_id' => $vinfo['user_id'],
                    'verify_id' => $vinfo['verify_id'],
                    'profile' => $this->user->simple_info( $vinfo['user_id'] )
                );
                $verify_list[] = $info;
            }
        }

        //查询审核列表
        $res['member'] = $member_list;
        $res['verify'] = $verify_list;
        return $res;
    }

    /**
     * @param $activity_id
     * @param $user_id
     * @return array
     * 设置状态
     */
    public function set_status( $user_id, $activity_id, $status ){
        $activity_info = $this->activity->get_basic_info( $activity_id );
        if( $activity_info['creator'] != $user_id ){
            throw new OpException( '不是发起人，不能修改状态', ERROR_CODE_NOT_ADMIN );
        }

        switch( $activity_info['status'] ){
            case ACTIVITY_STATUS_NORMAL:
                if( !in_array( $status, array( ACTIVITY_STATUS_CANCEL, ACTIVITY_STATUS_FINISH, ACTIVITY_STATUS_STOP ) ) ){
                    throw new OpException( '状态错误', ERROR_CODE_PARAM_INVALID );
                }
                break;
            case ACTIVITY_STATUS_STOP:
                if( !in_array( $status, array( ACTIVITY_STATUS_CANCEL, ACTIVITY_STATUS_FINISH, ACTIVITY_STATUS_NORMAL ) ) ){
                    throw new OpException( '状态错误', ERROR_CODE_PARAM_INVALID );
                }
                break;

            case ACTIVITY_STATUS_FINISH:
            case ACTIVITY_STATUS_CANCEL:
                throw new OpException( '状态错误', ERROR_CODE_PARAM_INVALID );
                break;
            default:
                break;
        }

        $activity_info['id'] = $activity_id;
        if( $status == ACTIVITY_STATUS_NORMAL ){
            $this->lbs->create( $activity_info );
        }
        else{
            $this->lbs->do_delete( $activity_id );
        }

        $this->activity->set_attr( $activity_id, 'status', $status );
        $this->activity->set_attr( $activity_id, 'update_time', time() );
    }

    /**
     * @param $activity_id
     * @param $user_id
     * @return array
     * 查询审核信息
     */
    public function verify_info( $user_id, $verify_id  )
    {
        $vinfo = $this->verify->get( $verify_id );

        $activity_info = $this->activity->get_basic_info( $vinfo['activity_id'] );
        if ($activity_info['creator'] != $user_id) {
            throw new OpException('不是发起人，不能查看审核信息', ERROR_CODE_NOT_ADMIN);
        }

        $res = array();
        $res['apply_time'] = timestamp_trans( $vinfo['apply_time'] );
        $res['user_info'] = $this->user->simple_info( $vinfo['user_id'] );
        $res['activity_title'] = $activity_info['title'];
        $res['remark'] = $vinfo['remark'];
        $res['result'] = $vinfo['result'];

        return $res;

    }

    /**
     * @param $activity_id
     * @param $user_id
     * @return array
     * 查询审核信息
     */
    public function verify_remark( $user_id, $search_user_id, $activity_id )
    {
        $activity_info = $this->activity->get_basic_info( $activity_id );
        if ($activity_info['creator'] != $user_id) {
            throw new OpException('不是发起人，不能查看审核信息', ERROR_CODE_NOT_ADMIN);
        }

        $res = array();
        $res['user_info'] = $this->user->simple_info( $search_user_id );
        $res['activity_title'] = $activity_info['title'];
        $remark = $this->verify->get_remark( $activity_id, $search_user_id );
        if( !$remark ){
            $remark = '';
        }
        $res['remark'] = $remark;
        return $res;

    }


    /**
     * @param $activity_id
     * @param $user_id
     * @return array
     * 查询活动状态
     */
    public function get_status( $user_id, $activity_id )
    {
        return $this->activity->get_attr( $activity_id, 'status' );
    }

    /**
     * @param $user_id
     * @param $verify_id
     * @param $result
     * @param $remark
     * @return bool
     * @throws OpException
     * 审核
     */
    public function do_verify( $user_id, $verify_id, $result, $remark ){
        $verify_info = $this->verify->get( $verify_id );
        if( !$verify_info ){
            throw new OpException( '审核信息不存在', ERROR_CODE_NOT_EXIST );
        }

        if( $verify_info['result'] != VERIFY_STATUS_WAIT ){
            throw new OpException( '该申请已经审核', ERROR_CODE_NOT_EXIST );
        }

        $activity_info = $this->activity->get_basic_info( $verify_info['activity_id'] );
        if ($activity_info['creator'] != $user_id) {
            throw new OpException('不是发起人，不能审核', ERROR_CODE_NOT_ADMIN);
        }

        //设置审核结果
        $this->verify->verify( $verify_info['activity_id'], $verify_id, $result, $remark, $verify_info['user_id'] );

        if( $result == VERIFY_STATUS_REJECT ){

            //写通知
            $add_notice = array(
                'activity_id' => $verify_info['activity_id'],
                'user_id' => $user_id,
                'verify_id' => $verify_id,
                'result' => VERIFY_STATUS_REJECT,
                'remark' => '拒绝理由:' . $remark,
                'content' =>  '拒绝了你加入 ' . $activity_info['title'] . ' 的申请'
            );

            $this->notice->add_verify( $verify_info['user_id'], $add_notice );

            //更新管理员的通知
            $up_notice = array(
                'remark' =>  '申请留言:' . $verify_info['remark'] ,
                'content' => '加入 ' . $activity_info['title'] . ' 的申请被拒绝' . '理由:' . $remark,
                'result' => VERIFY_STATUS_REJECT
            );
            $this->notice->update_verify( $verify_info['notice_id'], $up_notice );

            return true;
        }

        //通过的话，需要加入活动，再写通知
        $this->member->join( $verify_info['activity_id'], $verify_info['user_id'] );

        //设置成员总数
        $this->activity->member_join( $verify_info['activity_id'] );

        //新增申请人的通知
        $add_notice = array(
            'activity_id' => $verify_info['activity_id'],
            'user_id' => $user_id,
            'verify_id' => 0,
            'result' => VERIFY_STATUS_PASS,
            'remark' => $remark,
            'content' => '通过了你加入 ' . $activity_info['title'] . ' 的申请'
        );

        $this->notice->add_verify( $verify_info['user_id'], $add_notice );

        //更新管理员的通知
        $up_notice = array(
            'remark' => '申请留言:' . $verify_info['remark'] ,
            'content' => '加入 ' . $activity_info['title'] . ' 的申请已通过',
            'result' => VERIFY_STATUS_PASS
        );

        $this->notice->update_verify( $verify_info['notice_id'], $up_notice );
        return true;
    }

    /**
     * @param $user_id
     * @param $kick_user_id
     * @param $activity_id
     * @throws OpException
     * 移出活动
     */
    public function kick( $user_id, $kick_user_id, $activity_id ){
        $activity_info = $this->activity->get_basic_info( $activity_id );
        if ($activity_info['creator'] != $user_id) {
            throw new OpException('不是发起人，没有权限', ERROR_CODE_NOT_ADMIN);
        }

        if( $user_id == $kick_user_id ){
            throw new OpException('发起人无法移除', ERROR_CODE_NOT_ADMIN);
        }

        if( !$this->in_activity( $activity_id, $kick_user_id, $user_id )){
            throw new OpException( '用户还没有加入', ERROR_CODE_IN_ACTIVITY );
        }

        $this->member->del( $activity_id, $kick_user_id );

        //设置成员总数
        $this->activity->member_quit( $activity_id );

        //增加退群通知
        $quit_notice = array(
            'activity_id' => $activity_id,
            'user_id' => $user_id,
            'verify_id' => 0,
            'result' => 0,
            'remark' => '',
            'content' =>  '把你移出了 ' . $activity_info['title']
        );
        $notice_id = $this->notice->kick( $quit_notice );
        $this->notice->assign_notice( $notice_id, $kick_user_id );

    }

    /**
     * @param $user_id
     * @param $activity_id
     * @throws OpException
     * 退出活动
     */
    public function quit( $user_id, $activity_id ){
        $activity_info = $this->activity->get_basic_info( $activity_id );

        if( !$this->in_activity( $activity_id, $user_id, $activity_info['creator'])){
            throw new OpException( '你还没有加入活动', ERROR_CODE_IN_ACTIVITY );
        }

        if( $activity_info['creator'] == $user_id ){
            throw new OpException( '发起人不能退出', ERROR_CODE_IN_ACTIVITY );
        }

        $this->member->del( $activity_id, $user_id );
        //设置成员总数
        $this->activity->member_quit( $activity_id );

        //增加退群通知
        //写通知
        $quit_notice = array(
            'activity_id' => $activity_id,
            'user_id' => $user_id,
            'verify_id' => 0,
            'result' => 0,
            'remark' => '',
            'content' =>  '退出了 ' . $activity_info['title']
        );
        $notice_id = $this->notice->quit( $quit_notice );


        $this->notice->assign_notice( $notice_id, $activity_info['creator'] );

    }

    /**
     * @param $user_id
     * @param int $page
     * @return mixed
     * 查询通知列表
     */
    public function notice_list( $user_id, $page=1 ){
        $notice_list = $this->notice->get_list( $user_id, $page );
        if( is_array( $notice_list) && count($notice_list) > 0 ){
            foreach( $notice_list as &$notice ){
                $user_info = $this->user->simple_info( $notice['user_id']);
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

    /**
     * @param $user_id
     * @return mixed
     * 查询通知数量
     */
    public function notice_count( $user_id ){
        $last_time = $this->user->get_notice_time( $user_id );
        return $this->notice->count( $user_id, $last_time );
    }

    /**
     * @param $user_id
     * @param $to_user_id
     * @param $activity_id
     * @param $type
     * @param $content
     * @return mixed
     * @throws OpException
     * 评论回复
     */
    public function do_reply( $user_id, $to_user_id, $activity_id, $type, $content ){
        if( $type == REPLY_TYPE_REPLY && !$to_user_id ){
            throw new OpException( '回复的用户不能为空', ERROR_CODE_PARAM_INVALID );
        }

        if( $to_user_id > 0 ) {
            $to_user_info = $this->user->simple_info($to_user_id);
            if (!$to_user_info){
                throw new OpException( '回复的用户不存在', ERROR_CODE_PARAM_INVALID );
            }
        }

        $reply_id = $this->reply->add( $activity_id, $user_id, $to_user_id, $type, $content );
        return $reply_id;
    }

    /**
     * @param $activity_id
     * @param $page
     * @return array
     * 查询回复列表
     */
    public function reply_list( $user_id, $activity_id, $page ){
        $id_list = $this->reply->get_list( $activity_id, $page );
        $res = array();
        if( !$id_list ){
            return $res;
        }

        foreach( $id_list as $reply_id ){
            $reply_info = $this->reply->get_info( $reply_id );
            $info = array();
            $info['activity_id'] = $reply_info['activity_id'];
            $info['reply_id'] = $reply_info['id'];
            $info['content'] = $reply_info['content'];
            $info['create_time'] = timestamp_trans($reply_info['create_time']);
            $info['user_id'] = $reply_info['user_id'];
            $info['to_user_id'] = $reply_info['to_user_id'];
            $info['type'] = $reply_info['type'];
            $info['user_info'] = $this->user->simple_info( $info['user_id'] );
            if( $info['to_user_id'] > 0 ){
                $info['to_user_info'] = $this->user->simple_info( $info['to_user_id'] );
            }

            $info['is_author'] = 0;
            if( $info['user_id'] == $user_id ){
                $info['is_author'] = 1;
            }
            $res[] = $info;
        }

        return $res;
    }

    /**
     * @param $activity_id
     */
    public function reply_count( $activity_id ){
        return $this->reply->count( $activity_id );
    }

    /**
     * @param $user_id
     * @param $reply_id
     * 删除回复
     */
    public function reply_del( $activity_id, $user_id, $reply_id ){
        $reply_info = $this->reply->get_info( $reply_id );
        if( $user_id != $reply_info['user_id'] ){
            throw new OpException( '只能删除自己的回复', ERROR_CODE_PARAM_INVALID );
        }

        $this->reply->del( $activity_id, $reply_id );
    }

    private function _get_join_list( $activity_id, $user_id ){
        $activity_info = $this->activity->get_basic_info( $activity_id );
        $res_info = array();
        $res_info['id'] = $activity_id;
        $res_info['title'] = $activity_info['title'];
        $res_info['action_time'] = timestamp_trans( $activity_info['action_time'] );
        $res_info['address'] = $activity_info['address'] . $activity_info['address_detail'];
        $res_info['latitude'] = $activity_info['latitude'];
        $res_info['longitude'] = $activity_info['longitude'];
        $res_info['member_count'] = $activity_info['member_count'];
        $res_info['sum_count'] = $activity_info['member_begin'];
        $res_info['pay_type'] = $activity_info['pay_type'];
        $res_info['status'] = $activity_info['status'];
        $res_info['price_begin'] = $activity_info['price_begin'];
        $res_info['price_end'] = $activity_info['price_end'];

        $member_id_list = $this->member->index_list( $activity_id );
        $member_list = array();
        $member_info = array(
            'user_id' => $activity_info['creator'],
            'head' => $this->user->get_head( $activity_info['creator'] )
        );
        $res_info['creator'] = $activity_info['creator'];
        $res_info['creator_info'] = $this->user->simple_info( $activity_info['creator'] );
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
        return $res_info;
    }

    private function _get_create_list( $activity_id, $user_id ){

        $activity_info = $this->activity->get_basic_info( $activity_id );
        $res_info = array();
        $res_info['id'] = $activity_id;
        $res_info['month'] = date( 'm', $activity_info['create_time'] );
        $res_info['day'] = date( 'd', $activity_info['create_time'] );
        $res_info['title'] = $activity_info['title'];
        $res_info['action_time'] = timestamp_trans( $activity_info['action_time'] );
        $res_info['address'] = $activity_info['address'] . $activity_info['address_detail'];
        $res_info['latitude'] = $activity_info['latitude'];
        $res_info['longitude'] = $activity_info['longitude'];
        $res_info['member_count'] = $activity_info['member_count'];
        $res_info['sum_count'] = $activity_info['member_begin'];
        $res_info['pay_type'] = $activity_info['pay_type'];
        $res_info['price_begin'] = $activity_info['price_begin'];
        $res_info['price_end'] = $activity_info['price_end'];
        $res_info['status'] = $activity_info['status'];
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

        return $res_info;
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