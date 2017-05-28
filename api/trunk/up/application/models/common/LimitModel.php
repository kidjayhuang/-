<?php

require_once APPPATH . '/models/BaseModel.php';

class LimitModel extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('RedisCliModel', 'redis');
        $this->redis->connect();

        $this->_user_id = 0;
    }

    public function set_user_id( $user_id ){
        $this->_user_id = $user_id;
    }

    public function call_write( ){
        $now = time();
        $key = 'WR_' . $this->_user_id;

        //echo $key;

        $max_times = 3;
        $seconds = 60;

        //先清理掉之前的操作记录
        $list = $this->redis->zrangebyscore( $key, 0, $now - $seconds );
        if( $list ){
            foreach( $list as $field ){
                $this->redis->zrem( $key, $field );
            }
        }

        $count = $this->redis->zcount( $key, $now - $seconds, $now );
        if( $count >= $max_times ){
            throw new OpException( '你的操作太快，请稍后重试',  ERROR_CODE_FREQUENCY_EXCEED);
        }

        $this->redis->zAdd( $key, $now, getMillisecond() );
    }
}