<?php

require_once APPPATH . '/models/BaseModel.php';

class ActivityModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'activity';
    }

    public function get_list( $btime, $etime, $status ){
        $sql = "SELECT id, title, latitude, longitude, action_time
                FROM $this->table_name
                WHERE action_time >= ? and action_time <= ? and status=? ";

        $query = $this->db->query( $sql, array( $btime, $etime, $status ) );
        return $query->result_array();
    }

    public function create( $user_id, $data ){

        //先写数据库
        $in_data = $data;
        $in_data['creator'] = $user_id;
        $activity_id = $this->insert( $in_data );

        //写缓存
        //活动信息hash
        $this->redis->hmset( REDIS_KEY_ACTIVITY_HASH . $activity_id, $in_data );

        return $activity_id;
    }

    public function get_attr( $activity_id, $field ){
        return $this->redis->hget( REDIS_KEY_ACTIVITY_HASH . $activity_id, $field );
    }

    public function set_attr( $activity_id, $field, $val ){

        $this->update( $activity_id, array( $field => $val ));
        $this->redis->hset( REDIS_KEY_ACTIVITY_HASH . $activity_id, $field, $val );
    }

    public function get_user_count( $user_id ){
        $sql = "SELECT count(1) as cnt
                FROM $this->table_name
                WHERE creator = ?";

        $query = $this->db->query( $sql, array( $user_id ) );
        $res = $query->row_array();
        if( $res ){
            return $res['cnt'];
        }
        return 0;
    }


    public function get_basic_info( $activity_id ){
        $activity_info = $this->redis->hmget( REDIS_KEY_ACTIVITY_HASH . $activity_id, array(
            'creator',
            'title',
            'join_verify',
            'search',
            'action_time',
            'address',
            'type',
            'pay_type',
            'address_detail',
            'latitude',
            'longitude',
            'create_time',
            'update_time',
            'member_count',
            'status',
            'member_begin',
            'member_end',
            'create_time',
            'price_begin',
            'price_end'
        ) );

        if( !$activity_info['creator'] ){
            $activity_db = $this->getById( $activity_id );
            if( !$activity_db ){
                throw new OpException( '查找的圈子不存在', ERROR_CODE_NOT_EXIST );
            }

            unset( $activity_db['id'] );
            $this->redis->hmset( REDIS_KEY_ACTIVITY_HASH . $activity_id, $activity_db );

            $activity_info['creator'] = $activity_db['creator'];
            $activity_info['title'] = $activity_db['title'];
            $activity_info['join_verify'] = $activity_db['join_verify'];
            $activity_info['search'] = $activity_db['search'];
            $activity_info['action_time'] = $activity_db['action_time'];
            $activity_info['address'] = $activity_db['address'];
            $activity_info['type'] = $activity_db['type'];
            $activity_info['pay_type'] = $activity_db['pay_type'];
            $activity_info['address_detail'] = $activity_db['address_detail'];
            $activity_info['latitude'] = $activity_db['latitude'];
            $activity_info['longitude'] = $activity_db['longitude'];
            $activity_info['create_time'] = $activity_db['create_time'];
            $activity_info['update_time'] = $activity_db['update_time'];
            $activity_info['member_count'] = $activity_db['member_count'];
            $activity_info['status'] = $activity_db['status'];
            $activity_info['member_begin'] = $activity_db['member_begin'];
            $activity_info['member_end'] = $activity_db['member_end'];
            $activity_info['create_time'] = $activity_db['create_time'];
            $activity_info['price_begin'] = $activity_db['price_begin'];
            $activity_info['price_end'] = $activity_db['price_end'];
        }
        return $activity_info;
    }

    public function get_rich_info( $activity_id ){
        $activity_info = $this->redis->hgetall( REDIS_KEY_ACTIVITY_HASH . $activity_id );

        if( !$activity_info ){
            $activity_db = $this->getById( $activity_id );
            if( !$activity_db ){
                throw new OpException( '查找的圈子不存在', ERROR_CODE_NOT_EXIST );
            }

            unset( $activity_db['id'] );
            $this->redis->hmset( REDIS_KEY_ACTIVITY_HASH . $activity_id, $activity_db );
            $activity_info = $activity_db;
            unset( $activity_info['id'] );

        }
        return $activity_info;
    }

    public function member_join( $activity_id ){
        $member_count = $this->redis->hget( REDIS_KEY_ACTIVITY_HASH . $activity_id, 'member_count' );
        if( $member_count ){
            $this->redis->hset( REDIS_KEY_ACTIVITY_HASH . $activity_id, 'member_count', ++$member_count );
            $this->update( $activity_id, array( 'member_count' => $member_count ));
        }
    }

    public function member_quit( $activity_id )
    {
        $member_count = $this->redis->hget(REDIS_KEY_ACTIVITY_HASH . $activity_id, 'member_count');
        if ($member_count) {
            $this->redis->hset(REDIS_KEY_ACTIVITY_HASH . $activity_id, 'member_count', --$member_count);
            $this->update($activity_id, array('member_count' => $member_count));
        }
    }

}