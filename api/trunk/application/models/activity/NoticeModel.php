<?php

require_once APPPATH . '/models/BaseModel.php';

class NoticeModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'notice';
    }

    public function count( $user_id, $last_time ){
        return $this->redis->zcount( REDIS_KEY_NOTICE . $user_id, $last_time, time() );
    }

    public function get_list( $user_id, $page=1 ){
        $start = ($page-1) * NOTICE_COUNT_PAGE;
        $end = $start + NOTICE_COUNT_PAGE - 1;
        $id_list = $this->redis->zrevrange( REDIS_KEY_NOTICE . $user_id, $start, $end );
        $res = array();
        if( is_array( $id_list ) && count( $id_list ) > 0 ){
            foreach( $id_list as $id ){
                $info = $this->redis->hmget( REDIS_KEY_NOTICE_INFO . $id, array( 'activity_id', 'user_id', 'verify_id', 'remark', 'content', 'result', 'create_time', 'type' ) );
                if( $info ){
                    //$info['id'] = $id;
                    $res[] = $info;
                }
            }
        }
        return $res;
    }

    public function kick( $data ){
        $in_data = $data;
        $now = time();

        $in_data['type'] = NOTICE_TYPE_KICK;
        $in_data['create_time'] = $now;
        $in_data['result'] = 0;

        $notice_id = $this->insert( $in_data );

        //设置缓存
        $this->redis->hmset( REDIS_KEY_NOTICE_INFO . $notice_id, $in_data );
        return $notice_id;
    }

    public function quit( $data ){
        $in_data = $data;
        $now = time();

        $in_data['type'] = NOTICE_TYPE_QUIT;
        $in_data['create_time'] = $now;
        $in_data['result'] = 0;

        $notice_id = $this->insert( $in_data );

        //设置缓存
        $this->redis->hmset( REDIS_KEY_NOTICE_INFO . $notice_id, $in_data );
        return $notice_id;
    }

    public function add_join( $data ){
        $in_data = $data;
        $now = time();

        $in_data['type'] = NOTICE_TYPE_JOIN;
        $in_data['create_time'] = $now;
        $in_data['result'] = VERIFY_STATUS_WAIT;

        $notice_id = $this->insert( $in_data );

        //设置缓存
        $this->redis->hmset( REDIS_KEY_NOTICE_INFO . $notice_id, $in_data );
        return $notice_id;
    }

    /**
     * @param $data
     * 新增申请人的通知
     */
    public function add_verify( $user_id, $data ){
        $in_data = $data;
        $now = time();

        $in_data['type'] = NOTICE_TYPE_VERIFY;
        $in_data['create_time'] = $now;

        $notice_id = $this->insert( $in_data );

        //设置缓存
        $this->redis->hmset( REDIS_KEY_NOTICE_INFO . $notice_id, $in_data );

        $this->assign_notice( $notice_id, $user_id );
        return $notice_id;
    }

    /**
     * @param $data
     * 更新管理员的通知
     */
    public function update_verify( $notice_id, $data ){
        $this->update( $notice_id, $data );
        $this->redis->hmset( REDIS_KEY_NOTICE_INFO . $notice_id, $data );
    }

    public function assign_notice( $notice_id, $user_id ) {
        $this->redis->zAdd( REDIS_KEY_NOTICE . $user_id, time(), $notice_id );
    }


}