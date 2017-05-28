<?php

require_once APPPATH . '/models/BaseModel.php';

class ReplyModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'reply';
    }

    public function count( $activity_id ){
        return $this->redis->zcard( REDIS_KEY_REPLY_LIST . $activity_id );
    }


    public function get_list( $activity_id, $page=1, $cnt=0 ){
        $start = ($page-1) * REPLY_COUNT_PAGE;
        $stop = $start + REPLY_COUNT_PAGE - 1;

        if( $cnt > 0 ){
            $start = 0;
            $stop = $cnt - 1;
        }

        return $this->redis->zrevrange( REDIS_KEY_REPLY_LIST . $activity_id, $start, $stop );
    }


    public function add( $activity_id, $user_id, $to_user_id, $type, $content ){
        $now = time();
        $in_data = array(
            'activity_id' => $activity_id,
            'user_id' => $user_id,
            'to_user_id' => $to_user_id,
            'content' => $content,
            'create_time' => $now,
            'status' => REPLY_STATUS_NORMAL,
            'type' => $type
        );

        $reply_id = $this->insert( $in_data );

        $in_data['id'] = $reply_id;

        //设置Reply hash
        $this->redis->hmset( REDIS_KEY_REPLY . $reply_id, $in_data );

        //设置活动的回复列表
        $this->redis->zAdd( REDIS_KEY_REPLY_LIST . $activity_id, $now, $reply_id );

        return $reply_id;
    }

    public function del( $activity_id, $reply_id ){
        $up_data = array(
            'oper_time' => time(),
            'status' => REPLY_STATUS_DELETE
        );

        $this->update( $reply_id, $up_data );

        $this->redis->del( REDIS_KEY_REPLY . $reply_id );

        $this->redis->zrem( REDIS_KEY_REPLY_LIST . $activity_id, $reply_id );

    }

    public function get_info( $reply_id ){
        $reply_info = $this->redis->hgetall( REDIS_KEY_REPLY . $reply_id );
        if( !$reply_info ){
            $reply_info = $this->getById( $reply_id );
            if( !$reply_info || $reply_info['status'] == ARTICLE_STATUS_DELETE ){
                throw new OpException( '回复不存在', ERROR_CODE_NOT_EXIST );
            }
        }

        return $reply_info;
    }
}