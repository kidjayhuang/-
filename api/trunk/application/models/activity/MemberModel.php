<?php

require_once APPPATH . '/models/BaseModel.php';

class MemberModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'member';
    }

    //建群时调用，写数据库即可 done
    public function create( $activity_id, $user_id ){
        $now = time();
        $in_data = array(
            'activity_id' => $activity_id,
            'user_id' => $user_id,
            'join_time' => $now,
            'type' => USER_TYPE_CREATOR
        );

        $this->insert( $in_data );

        //设置我创建的活动缓存
        if( !$this->redis->zAdd( REDIS_KEY_MY_CREATE_ACTIVITY . $user_id, $now, $activity_id )){
            throw new OpException( '设置缓存错误', ERROR_CODE_REDIS_FAIL );
        }

        return true;
    }

    //加入
    public function join( $activity_id, $user_id ){
        $now = time();

        $my_join_count = $this->redis->zcard( REDIS_KEY_MY_JOIN_ACTIVITY . $user_id );
        if( $my_join_count >= LIMIT_JOIN_ACTIVITY ){
            throw new OpException( '加入的活动数量超过了限制', ERROR_CODE_EXCEED );
        }

        $in_data = array(
            'activity_id' => $activity_id,
            'user_id' => $user_id,
            'join_time' => $now,
            'type' => USER_TYPE_MEMBER
        );

        $this->insert( $in_data );

        //设置成员列表缓存
        if( !$this->redis->zAdd( REDIS_KEY_MEMBER . $activity_id, $now, $user_id )){
            throw new OpException( '设置圈子成员缓存错误', ERROR_CODE_REDIS_FAIL );
        }

        //设置我加入的活动列表
        if( !$this->redis->zAdd( REDIS_KEY_MY_JOIN_ACTIVITY . $user_id, $now, $activity_id )){
            throw new OpException( '设置我的圈子缓存错误', ERROR_CODE_REDIS_FAIL );
        }

        return true;
    }

    public function get_last_time( $activity_id, $user_id ){
        $last_time = $this->redis->hget( REDIS_KEY_MEMBER_INFO . $activity_id . REDIS_SEPARATOR . $user_id, 'last_time' );
        if( $last_time ){
            $member = $this->getByFields(
                                array('activity_id', 'user_id', 'status'),
                                array($activity_id, $user_id, 0 ),
                                array('remark', 'last_time') );
            $this->redis->hset( REDIS_KEY_MEMBER_INFO . $activity_id . REDIS_SEPARATOR . $user_id, 'last_time', $member['last_time'] );
            if( $member['remark'] != '' ){
                $this->redis->hset( REDIS_KEY_MEMBER_INFO . $activity_id . REDIS_SEPARATOR . $user_id, 'remark', $member['remark'] );
            }
        }
        return $last_time;
    }

    public function  set_last_time( $activity_id, $user_id ){
        $now = time();
        $this->update_where( array( 'last_time' => $now),
            array( 'activity_id' => $activity_id,
                   'user_id' => $user_id,
                   'status' => 0 )
        );

        $this->redis->hset( REDIS_KEY_MEMBER_INFO . $activity_id . REDIS_SEPARATOR . $user_id, 'last_time', $now );
    }

    public function del( $activity_id, $user_id ){
        $this->update_where( array( 'status' => 1,
                                    'quit_time' => time()),
                             array( 'activity_id' => $activity_id,
                                    'user_id' => $user_id,
                                    'status' => 0 )
        );

        //群成员缓存
        $this->redis->zrem( REDIS_KEY_MEMBER . $activity_id, $user_id);

        //我加入的活动
        $this->redis->zrem( REDIS_KEY_MY_JOIN_ACTIVITY . $user_id, $activity_id );

    }


    public function member_list( $activity_id ){
        return $this->redis->zrevrange(REDIS_KEY_MEMBER . $activity_id, 0, LIMIT_ACTIVITY_MEMBER - 1);
    }


    public function index_list( $activity_id ){
        return $this->redis->zrevrange( REDIS_KEY_MEMBER . $activity_id, 0, LIMIT_INDEX_MEMBER-1);
    }


    public function is_member( $activity_id, $user_id ){
        $score = $this->redis->zscore( REDIS_KEY_MEMBER . $activity_id, $user_id );
        if( !$score ){
            return false;
        }
        return true;
    }


    public function my_join( $user_id, $page ){
        if( $page==0 ) {
            return $this->redis->zrevrange( REDIS_KEY_MY_JOIN_ACTIVITY . $user_id, 0, MEMBER_COUNT_PAGE );
        }
        else{
            $start = ($page-1) * MEMBER_COUNT_PAGE;
            $stop = $start + MEMBER_COUNT_PAGE - 1;
            return $this->redis->zrevrange( REDIS_KEY_MY_JOIN_ACTIVITY . $user_id, $start, $stop );
        }
    }


    public function my_create( $user_id, $page ){
        if( $page==0 ) {
            return $this->redis->zrevrange( REDIS_KEY_MY_CREATE_ACTIVITY . $user_id, 0, MEMBER_COUNT_PAGE );
        }
        else{
            $start = ($page-1) * MEMBER_COUNT_PAGE;
            $stop = $start + MEMBER_COUNT_PAGE - 1;
            return $this->redis->zrevrange( REDIS_KEY_MY_CREATE_ACTIVITY . $user_id, $start, $stop );
        }
    }

}