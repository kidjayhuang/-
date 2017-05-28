<?php

require_once APPPATH . '/models/BaseModel.php';

class BaiduModel extends CI_Model
{
    public function __construct()
    {
        $this->_ak = '8ZPklKyaudWghG2AzrKNyC2yMGSj817z';
        $this->_sk = 'hIPk3CAKq2PVG3Gg8udE8qmxgBj7GqmU';
        $this->_geotable_id = 162191;
    }

    public function delete( $aid ){
        $url = "http://api.map.baidu.com/geodata/v3/poi/delete";
        $uri = '/geodata/v3/poi/delete';

        $querystring_arrays = array (
            'ak' => $this->_ak,
            'geotable_id' => $this->_geotable_id,
            'activity_id' => $aid
        );

        $sn = $this->_caculateAKSN( $uri, $querystring_arrays, 'POST');
        $querystring_arrays['sn'] = $sn;

        $retString = $this->curl->simple_post( $url, $querystring_arrays);

        $res = object2array( json_decode( $retString ) );
        if( $res['status'] == 0 ){
            return true;
        }

        throw new OpException( '删除LBS云失败, message:' . $res['message'], ERROR_CODE_BAIDU_LBS );
    }


    public function get_poi( $aid ){
        $url = "http://api.map.baidu.com/geodata/v3/poi/list";
        $uri = '/geodata/v3/poi/list';

        $querystring_arrays = array (
            'ak' => $this->_ak,
            'geotable_id' => $this->_geotable_id,
            'activity_id' => $aid
        );

        $sn = $this->_caculateAKSN( $uri, $querystring_arrays );
        $querystring_arrays['sn'] = $sn;

        $retString = $this->curl->simple_get( $url, $querystring_arrays);

        $res = object2array( json_decode( $retString ) );
        return $res;
    }

    public function create( $ainfo ){
        $url = "http://api.map.baidu.com/geodata/v3/poi/create";
        $uri = '/geodata/v3/poi/create';

        $querystring_arrays = array (
            'ak' => $this->_ak,
            'geotable_id' => $this->_geotable_id,
            'latitude' => $ainfo['latitude'],
            'longitude' => $ainfo['longitude'],
            'coord_type' => 2,
            'action_time' => $ainfo['action_time'],
            'activity_id' => $ainfo['id'],
            'title' => $ainfo['title']
        );

        $sn = $this->_caculateAKSN( $uri, $querystring_arrays, 'POST');
        $querystring_arrays['sn'] = $sn;

        $retString = $this->curl->simple_post( $url, $querystring_arrays);

        $res = object2array( json_decode( $retString ) );
        if( $res['status'] == 0 ){
            return true;
        }

        throw new OpException( '写入百度LBS云失败, message:' . $res['message'], ERROR_CODE_BAIDU_LBS );
    }

    public function nearby( $location, $page=1 ){

        //http://api.map.baidu.com/geosearch/v3/nearby?ak=您的ak&geotable_id=****
        //&location=116.395884,39.932154&radius=1000&tags=酒店&sortby=distance:1|price:1&filter=price:200,300

        $url = "http://api.map.baidu.com/geosearch/v3/nearby";
        $uri = '/geosearch/v3/nearby';

        $location = $this->geoconv( $location, 3, 5 );

        $querystring_arrays = array (
            'ak' => $this->_ak,
            'geotable_id' => $this->_geotable_id,
            'location' => $location,
            'coord_type' => 3,
            'radius' => 10000000,
            'sortby' => 'distance:1',
            'page_index' => $page-1
        );

        $sn = $this->_caculateAKSN( $uri, $querystring_arrays);
        $querystring_arrays['sn'] = $sn;

        $retString = $this->curl->simple_get( $url , $querystring_arrays);

        return object2array(json_decode( $retString ) );

    }

    public function geoconv( $location, $from, $to ){
        $url = "http://api.map.baidu.com/geoconv/v1/";
        $uri = '/geoconv/v1/';

        $querystring_arrays = array (
            'ak' => $this->_ak,
            'coords' => $location,
            'from' => $from,
            'to' => $to
        );

        $sn = $this->_caculateAKSN( $uri, $querystring_arrays );
        $querystring_arrays['sn'] = $sn;

        $retString = $this->curl->simple_get( $url, $querystring_arrays);
        $res = object2array( json_decode( $retString ) );

        //var_dump($res);
        if( $res['status'] == 0 ){
            return $res['result'][0]->x . ',' . $res['result'][0]->y;
        }

    }

    public function list_column(  ){
        $url = "http://api.map.baidu.com/geodata/v3/column/list";
        $uri = '/geodata/v3/column/list';

        $querystring_arrays = array (
            'ak' => $this->_ak,
            'geotable_id' => $this->_geotable_id
        );

        $sn = $this->_caculateAKSN( $uri, $querystring_arrays );
        $querystring_arrays['sn'] = $sn;

        $retString = $this->curl->simple_get( $url, $querystring_arrays);

        $res = object2array( json_decode( $retString ) );
        return $res;
    }


    public function modify_column(  ){
        $url = "http://api.map.baidu.com/geodata/v3/column/update";
        $uri = '/geodata/v3/column/update';

        $querystring_arrays = array (
            'ak' => $this->_ak,
            'geotable_id' => $this->_geotable_id,
            'id' => 283521,
            'is_index_field' => 1,
            'is_unique_field' => 1
        );

        $sn = $this->_caculateAKSN( $uri, $querystring_arrays, 'POST' );
        $querystring_arrays['sn'] = $sn;

        $retString = $this->curl->simple_post( $url, $querystring_arrays);

        $res = object2array( json_decode( $retString ) );
        return $res;
    }


    function _caculateAKSN( $url, $querystring_arrays, $method = 'GET')
    {
        if ($method === 'POST'){
            ksort($querystring_arrays);
        }
        $querystring = http_build_query($querystring_arrays);
        return md5(urlencode($url.'?'.$querystring.$this->_sk));
    }

}