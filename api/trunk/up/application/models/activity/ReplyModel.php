<?php

require_once APPPATH . '/models/BaseModel.php';

class ReplyModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'reply';
    }

    public function count( $article_id ){
        return $this->redis->zcard( REDIS_KEY_REPLY_LIST . $article_id );
    }

    public function clear( $circle_id, $article_id ){
        $reply_list = $this->redis->zrevrange( REDIS_KEY_REPLY_LIST . $article_id, 0, LIMIT_REPLY_COUNT );
        if( is_array( $reply_list ) && count($reply_list) > 0 ) {
            foreach ($reply_list as $reply_id) {
                $reply_info = $this->get_info($reply_id);
                $this->del($circle_id, $reply_id, $article_id, $reply_info['to_user_id']);
            }
        }
    }
    public function get_list( $article_id, $page=1, $cnt=0 ){
        $start = ($page-1) * REPLY_COUNT_PAGE;
        $stop = $start + REPLY_COUNT_PAGE - 1;

        if( $cnt > 0 ){
            $start = 0;
            $stop = $cnt - 1;
        }

        return $this->redis->zrevrange( REDIS_KEY_REPLY_LIST . $article_id, $start, $stop );
    }
    public function to_me_list( $circle_id, $user_id, $page ){
        $start = ($page-1) * REPLY_COUNT_PAGE;
        $stop = $start + REPLY_COUNT_PAGE - 1;
        return $this->redis->zrevrange( REDIS_KEY_REPLY_TO_ME . $circle_id . REDIS_SEPARATOR . $user_id, $start, $stop );
    }

    public function count_to_me( $circle_id, $user_id, $last_time ){
        return $this->redis->zcount( REDIS_KEY_REPLY_TO_ME . $circle_id . REDIS_SEPARATOR . $user_id, $last_time, time() );

    }

    public function add( $circle_id, $article_id, $user_id, $to_user_id, $type, $content ){
        $now = time();
        $in_data = array(
            'article_id' => $article_id,
            'user_id' => $user_id,
            'to_user_id' => $to_user_id,
            'content' => $content,
            'create_time' => $now,
            'status' => ARTICLE_STATUS_NORMAL,
            'type' => $type
        );

        $reply_id = $this->insert( $in_data );

        $in_data['id'] = $reply_id;

        //设置Reply hash
        $this->redis->hmset( REDIS_KEY_REPLY . $reply_id, $in_data );

        //设置文章的回复列表
        $this->redis->zAdd( REDIS_KEY_REPLY_LIST . $article_id, $now, $reply_id );

        //设置to 我的回复
        $keys = json_encode( array( 'id' => $reply_id, 'type' => REPLY_TYPE_ARTICLE ) );
        $this->redis->zAdd( REDIS_KEY_REPLY_TO_ME . $circle_id . REDIS_SEPARATOR . $to_user_id, $now, $keys );

        //我的回复好像没啥用，先不做
        return $reply_id;
    }

    public function del( $circle_id, $reply_id, $article_id, $to_user_id ){
        $up_data = array(
            'oper_time' => time(),
            'status' => ARTICLE_STATUS_DELETE
        );

        $this->update( $reply_id, $up_data );

        $this->redis->del( REDIS_KEY_REPLY . $reply_id );

        $this->redis->zrem( REDIS_KEY_REPLY_LIST . $article_id, $reply_id );

        $keys = json_encode( array( 'id' => $reply_id, 'type' => REPLY_TYPE_ARTICLE ) );
        $this->redis->zrem( REDIS_KEY_REPLY_TO_ME . $circle_id . REDIS_SEPARATOR . $to_user_id, $keys );

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