<?php

require_once APPPATH . '/models/BaseModel.php';

class LbsLogicModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model( 'activity/ActivityModel', 'activity' );
        $this->load->model( 'lbs/BaiduModel', 'baidu' );
        $this->load->model( 'auth/UserModel', 'user' );

    }

    public function nearby( $location, $page=1 ){
        $list = $this->baidu->nearby( $location, $page );
        $res = array();

        if( $list['status'] != '0' ){
            throw new OpException( '查询附近活动错误', ERROR_CODE_BAIDU_LBS );
        }
        $res['total'] = $list['total'];
        $res['size'] = $list['size'];
        $res['list'] = [];

        //return $list;

        if( $res['size'] > 0 ){
            foreach( $list['contents'] as $a ){
                $activity_id = $a->activity_id;
                $activity_info = $this->activity->get_basic_info( $activity_id );
                $item = array();

                $item['activity_id'] = $activity_id;
                $item['user_id'] = $activity_info['creator'];
                $item['user_info'] = $this->user->simple_info( $item['user_id'] );
                $item['title'] = $activity_info['title'];
                $item['address'] = $activity_info['address'] . $activity_info['address_detail'];
                $item['action_time'] = timestamp_trans( $activity_info['action_time'] );
                $item['latitude'] = $activity_info['latitude'];
                $item['longitude'] = $activity_info['longitude'];
                $item['member_count'] = $activity_info['member_count'];
                $item['count'] = $activity_info['member_begin'];
                $item['distance'] = $a->distance;
                $item['distance_desc'] = $this->trans_distance( intval($a->distance));

                $res['list'][] = $item;
            }
        }
        return $res;
    }

    /**
     * @param $btime
     * @param $etime
     * 将一个时间段内所有的正常报名活动全部写入
     */
    public function batch_create( $btime, $etime ){
        $activity_list = $this->activity->get_list( $btime, $etime, ACTIVITY_STATUS_NORMAL );
        if( is_array( $activity_list ) && count($activity_list) > 0 ){
            foreach( $activity_list as $activity_info ){
                $res = $this->baidu->create( $activity_info );
            }
        }
    }

    /**
     * @param $activity_info
     * @return mixed
     * 写入单个活动信息
     */
    public function create( $activity_info ){
        return $res = $this->baidu->create( $activity_info );
    }

    /**
     * @param $activity_info
     * @return mixed
     * 删除单个活动信息
     */
    public function do_delete( $activity_id ){
        return $res = $this->baidu->delete( $activity_id );
    }

    public function trans_distance( $meter ){
        if( $meter <= 50 ){
            return '<50m';
        }

        if( $meter <= 100 ){
            return '<100m';
        }

        if( $meter <= 200 ){
            return '<200m';
        }

        if( $meter <= 300 ){
            return '<300m';
        }

        if( $meter <= 400 ){
            return '<400m';
        }

        if( $meter <= 500 ){
            return '<500m';
        }

        if( $meter <= 600 ){
            return '<600m';
        }

        if( $meter <= 700 ){
            return '<700m';
        }

        if( $meter <= 800 ){
            return '<800m';
        }

        if( $meter <= 900 ){
            return '<900m';
        }

        if( $meter <= 1000 ){
            return '<1km';
        }

        return round( $meter/1000, 1 ) . 'km';
    }
}