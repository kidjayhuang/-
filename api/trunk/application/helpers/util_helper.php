<?php
/**
 * Created by PhpStorm.
 * User: dateng
 * Date: 5/9/15
 * Time: 10:44 PM
 */

function object2array($object) {
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    }
    else {
        $array = $object;
    }
    return $array;
}


function td_output_array_format($errorCode, $errorMsg, $data = array(), $options = NULL) {
    $ret = array();
    $ret['code'] = $errorCode;
    $ret['msg'] = $errorMsg;
    $ret['data'] = $data;

    if (isset($options)) {
        $data['result_json'] = json_encode($ret, $options);
    } else {
        $data['result_json'] = json_encode($ret);
    }

    return $data;
}


function getMillisecond() {
    list($misc, $sec) = explode(' ', microtime());
    return (float)sprintf('%.0f',(floatval($misc)+floatval($sec))*1000);
}

/**
 * 转换时间戳为文本
 */

function timestamp_trans( $time ){
    $now = time();
    $now_days = floor( $now / 86400 );
    $times_days = floor( $time / 86400 );

    $days = $now_days - $times_days;

    $res = '';

    $time_desc = ' ';
    $time_desc .= date('a', $time ) == 'am' ? '上午' : '下午';
    $time_desc .= date( 'h:i', $time );

    switch( $days ){
        case -1:
            $res = '明天' . $time_desc;
            break;
        case -2:
            $res = '后天' . $time_desc;
            break;
        case 0:
            $res = $time_desc;
            break;
        case 1:
            $res = '昨天' . $time_desc;
            break;
        case 2:
            $res = '前天' . $time_desc;
            break;
        default:
            $res = date( 'm-d', $time );
            $res .= $time_desc;
            break;
    }
    return $res;
}

/**
 * 计算两点地理坐标之间的距离
 * @param  Decimal $longitude1 起点经度
 * @param  Decimal $latitude1  起点纬度
 * @param  Decimal $longitude2 终点经度
 * @param  Decimal $latitude2  终点纬度
 * @param  Int     $unit       单位 1:米 2:公里
 * @param  Int     $decimal    精度 保留小数位数
 * @return Decimal
 */
function get_distance($longitude1, $latitude1, $longitude2, $latitude2, $unit=1, $decimal=0){

    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI = 3.1415926;

    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI /180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if($unit==2){
        $distance = $distance / 1000;
    }

    return round($distance, $decimal);
}



/**
 * 将以米为单位的数字转换为文本展示
 * @param int $length
 * @param string $chars
 * @return string
 */
function distance_desc( $meter ) {

    if( $meter < 1000 ){
        $desc = $meter . '米';
    }
    else{
        $desc = round( $meter / 1000, 1 ) . "公里";
    }

    return $desc;
}


/**
 * 产生指定长度的随机字符串
 * @param int $length
 * @param string $chars
 * @return string
 */
function generate_random_str($length = 6, $chars = '') {
    if (empty($chars)) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    }

    $ret = '';
    $count = strlen($chars) - 1;

    for ($i = 0; $i < $length; $i++) {
        $ret .= $chars[mt_rand(0, $count)];
    }

    return $ret;
}

/**
 * 检查手机号格式是否正确
 * @param $phone
 *
 * @return bool
 */
function check_phone($phone) {
    if (preg_match('/^(1[0-9]{10}|0[0-9]{2,3}[- ]?[0-9]{7,8})$/', $phone)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * 检查性别是否合法
 * @param $gender
 *
 * @return bool
 */
function check_gender($gender) {
    if (is_numeric($gender)) {
        $gender = intval($gender);

        if (in_array($gender, array(0, 1, 2))) {
            return TRUE;
        }
    }

    return FALSE;
}

/**
 * 检查身份证号码是否合法
 * @param $id_number
 *
 * @return bool
 */
function check_id_number($id_number) {
    if (preg_match('/^[0-9]{17}[0-9xX]$/', $id_number)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * 转义HTML特殊字符，支持数组
 * @param $val
 */
function escape_html_special_chars(& $val) {
    if (is_array($val)) {
        array_walk($val, __FUNCTION__);
    } else {
        $val = htmlspecialchars($val);
    }
}

/**
 * 请求是否来自微篇
 *
 * @param null $client
 * @return bool
 */
function is_weipian($client = NULL) {
    if ( ! isset($client)) {
        $client = isset($_COOKIE['client']) ? $_COOKIE['client'] : NULL;
    }

    if (is_numeric($client)) {
        $client = intval($client);

        if ((LOGIN_TYPE_WP_AND === $client) || (LOGIN_TYPE_WP_IOS === $client)) {
            return TRUE;
        }
    }

    return FALSE;
}

/**
 * 请求是否来自竹马旅行公众号
 *
 * @param null $client
 * @return bool
 */
function is_wx_mall($client = NULL) {
    if ( ! isset($client)) {
        $client = isset($_COOKIE['client']) ? $_COOKIE['client'] : NULL;
    }

    if (is_numeric($client)) {
        $client = intval($client);

        if (LOGIN_TYPE_WX_MALL === $client) {
            return TRUE;
        }
    }

    return FALSE;
}


function getHttpHeader($headerKey) {
    $headerKey = strtoupper($headerKey);
    $headerKey = str_replace('-', '_', $headerKey);
    $headerKey = 'HTTP_' . $headerKey;
    return isset($_SERVER[$headerKey]) ? $_SERVER[$headerKey] : '';
}


