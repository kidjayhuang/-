<?php

require_once APPPATH . '/models/BaseModel.php';

class VerifyModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'verify';
    }

    public function get( $verify_id ){
        $verify_info = $this->redis->hgetall( REDIS_KEY_VERIFY_INFO . $verify_id );
        if( $verify_info ){
            return $verify_info;
        }
        return false;
    }

    public function add( $activity_id, $user_id, $remark ){
        $now = time();
        $in_data = array(
            'activity_id' => $activity_id,
            'user_id' => $user_id,
            'remark' => $remark,
            'apply_time' => $now,
            'result' => VERIFY_STATUS_WAIT
        );

        $verify_id = $this->insert( $in_data );

        //成功后写缓存
        $this->redis->hmset( REDIS_KEY_VERIFY_INFO . $verify_id, $in_data );
        $this->redis->zAdd( REDIS_KEY_VERIFY . $activity_id, $now, $verify_id );

        return $verify_id;
    }

    public function verify( $activity_id, $verify_id, $result, $remark, $user_id ){
        $up_data = array(
            'result' => $result,
            'verify_time' => time(),
            'verify_user_id' => $user_id,
            'verify_desc' => $remark
        );

        $this->update( $verify_id, $up_data );

        $this->redis->del( REDIS_KEY_VERIFY_INFO . $verify_id );
        $this->redis->zrem( REDIS_KEY_VERIFY. $activity_id, $verify_id );

        //要从wait列表中删除，等待审核列表不做

    }

    public function set_notice_id( $verify_id, $notice_id ){
        $this->update( $verify_id, array( 'notice_id' => $notice_id ) );
        $this->redis->hset( REDIS_KEY_VERIFY_INFO . $verify_id, 'notice_id', $notice_id );
    }

    public function in_wait_verify( $activity_id, $user_id ){
        $wait_list = $this->wait_list( $activity_id );
        if( count($wait_list) > 0 ){
            foreach( $wait_list as $verify ){
                if( $verify['user_id'] == $user_id ){
                    return true;
                }
            }
        }
        return false;
    }

    public function wait_list( $activity_id ){
        $verify_list = $this->redis->zrevrangebyscore( REDIS_KEY_VERIFY . $activity_id, time(), 0 );

        $res = array();
        if( is_array( $verify_list) && count($verify_list)>0 ){
            foreach( $verify_list as $id ){
                $verify_info = $this->redis->hgetall( REDIS_KEY_VERIFY_INFO . $id );
                if( $verify_info ) {
                    $verify_info['verify_id'] = $id;
                    $res[] = $verify_info;
                }

            }
        }

        return $res;
    }
}