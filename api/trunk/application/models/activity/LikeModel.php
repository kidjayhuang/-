<?php

require_once APPPATH . '/models/BaseModel.php';

class ArticleLikeModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'article_like';
    }

    public function count( $article_id ){
        return $this->redis->zcard( REDIS_KEY_LIKE_LIST . $article_id );
    }

    public function time( $article_id, $user_id ){
        return $this->redis->zscore( REDIS_KEY_LIKE_LIST . $article_id, $user_id );
    }

    public function add( $circle_id, $article_id, $user_id, $to_user_id )
    {
        $now = time();
        if( $this->has_like( $article_id, $user_id )){
            throw new OpException( '不能重复点赞', ERROR_CODE_PARAM_INVALID );
        }

        $in_data = array(
            'article_id' => $article_id,
            'user_id' => $user_id,
            'create_time' => $now
        );

        if( !$this->insert($in_data) ){
            throw new OpException( $this->db->error()['message'], ERROR_CODE_MYSQL_FAIL );
        }

        //设置文章的点赞列表
        $this->redis->zAdd( REDIS_KEY_LIKE_LIST . $article_id, $now, $user_id );

        //设置to 我的回复
        $keys = json_encode(array('id' => $article_id, 'user_id' => $user_id, 'type' => REPLY_TYPE_LIKE));
        $this->redis->zAdd( REDIS_KEY_REPLY_TO_ME . $circle_id . REDIS_SEPARATOR . $to_user_id, $now, $keys);
    }


    public function get_list( $article_id, $page=1, $cnt=0 ){
        $start = ($page-1) * REPLY_COUNT_PAGE;
        $stop = $start + REPLY_COUNT_PAGE - 1;

        if( $cnt > 0 ){
            $start = 0;
            $stop = $cnt - 1;
        }

        return $this->redis->zrevrange( REDIS_KEY_LIKE_LIST . $article_id, $start, $stop );
    }

    public function get_all( $article_id ){
        return $this->redis->zrevrange( REDIS_KEY_LIKE_LIST . $article_id, 0, 99999999 );
    }

    public function clear( $circle_id, $article_id, $to_user_id ){
        $user_list = $this->get_all( $article_id );
        if( is_array( $user_list ) && count( $user_list ) > 0  ){
            foreach( $user_list as $user_id ){
                $this->cancel( $circle_id, $article_id, $user_id, $to_user_id );
            }
        }
    }

    public function cancel($circle_id, $article_id, $user_id, $to_user_id)
    {
        if( !$this->has_like( $article_id, $user_id )){
            throw new OpException( '还没有点赞，无法取消', ERROR_CODE_PARAM_INVALID );
        }

        $del_data = array(
            'article_id' => $article_id,
            'user_Id' => $user_id
        );

        $this->delete( $del_data);

        $this->redis->zrem(REDIS_KEY_LIKE_LIST . $article_id, $user_id);

        $keys = json_encode(array('id' => $user_id, 'type' => REPLY_TYPE_LIKE ));
        $this->redis->zrem(REDIS_KEY_REPLY_TO_ME . $circle_id . REDIS_SEPARATOR . $to_user_id, $keys);

    }

    public function has_like( $article_id, $user_id ){
        $score = $this->redis->zscore( REDIS_KEY_LIKE_LIST . $article_id, $user_id );

        if( $score ){
            return true;
        }
        return false;
    }

}