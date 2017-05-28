<?php

require_once APPPATH . '/models/BaseModel.php';

class UserModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'users';
    }

    public function simple_info( $user_id ){
        $user_info = $this->redis->hmget( REDIS_KEY_USER . $user_id, array( 'nick_name', 'gender', 'avatar_url') );
        if( !$user_info ){
            $user_db = $this->getById( $user_id );
            if( !$user_db ){
                throw new OpException( '用户不存在', ERROR_CODE_NOT_EXIST );
            }

            $user_info = array(
                'nick_name' => $user_db['nick_name'],
                'gender' => $user_db['gender'],
                'avatar_url' => $user_db['avatar_url'],
                'notice_time' => $user_db['notice_time']
            );
            $this->redis->hmset( REDIS_KEY_USER . $user_id, $user_info );
        }

        return $user_info;
    }

    public function set_notice_time( $user_id ){
        $this->update( $user_id, array( 'notice_time' => time() ));
        $this->redis->hset( REDIS_KEY_USER . $user_id, 'notice_time', time());
    }

    public function  get_notice_time( $user_id ){
        $time = $this->redis->hget( REDIS_KEY_USER . $user_id, 'notice_time' );
        if( $time === false  ){
            $user_info = $this->getById( $user_id );
            if( $user_info ){
                $time = $user_info['notice_time'];
                $this->redis->hset( REDIS_KEY_USER. $user_id, 'notice_time', $user_info['notice_time'] );
            }
        }

        return $time;
    }

    public function set_circle_count( $user_id, $count ){
        $this->update( $user_id, array( 'circle_count' => $count ));
        $this->redis->hset( REDIS_KEY_USER . $user_id, 'circle_count', $count );
    }

    public function  get_circle_count( $user_id ){
        $cnt = $this->redis->hget( REDIS_KEY_USER . $user_id, 'circle_count' );
        if( $cnt === false  ){
            $user_info = $this->getById( $user_id );
            if( $user_info ){
                $cnt = $user_info['circle_count'];
                $this->redis->hset( REDIS_KEY_USER. $user_id, 'circle_count', $user_info['circle_count'] );
            }
        }

        return $cnt;
    }

}