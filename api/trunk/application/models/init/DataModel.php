<?php

require_once APPPATH . '/models/BaseModel.php';

class DataModel extends BaseModel
{

    private $_userid;
    private $_shopid;

    public function __construct()
    {
        parent::__construct();
        $this->_userid = 10001;
    }



    public function init_test_shop()
    {

        for( $i = 10001; $i<= 10010; $i++){
            $this->_init_test_shop( $i );
        }
        $key = "SHOP_10001";

        $res = $this->redis->hgetall( $key );
        $res['pic_list']=json_decode( $res['pic_list'] );
        $res['promotion']=json_decode( $res['promotion'] );

        return $res;

    }


    /*
     * 测试zsort的操作
     */
    public function test_zsort(){

        $key =  'CART_' . $this->_userid;

        $goods = array(
            "10001" => array(
                array(
                    "goods_id" => 10001,
                    "goods_num" => 2,
                    "price" => 10000,
                    "package" => "1:1-2-3|2:4"
                ),
                array(
                    "goods_id" => 10002,
                    "goods_num" => 4,
                    "price" => 12000,
                    "package" => ""
                )
            ),
            "10003" => array(
                array(
                    "goods_id" => 10006,
                    "goods_num" => 2,
                    "price" => 10000,
                    "package" => "1:1-2-3|2:4"
                ),
                array(
                    "goods_id" => 10007,
                    "goods_num" => 4,
                    "price" => 12000,
                    "package" => ""
                )
            )
        );

        $member = json_encode( $goods );
        $score = time();
        $this->redis->zAdd( $key, $score, $member );

        $goods = array(
            "10005" => array(
                array(
                    "goods_id" => 10001,
                    "goods_num" => 2,
                    "price" => 10000,
                    "package" => "1:1-2-3|2:4"
                ),
                array(
                    "goods_id" => 10002,
                    "goods_num" => 4,
                    "price" => 12000,
                    "package" => ""
                )
            ),
            "10003" => array(
                array(
                    "goods_id" => 10006,
                    "goods_num" => 2,
                    "price" => 10000,
                    "package" => "1:1-2-3|2:4"
                ),
                array(
                    "goods_id" => 10007,
                    "goods_num" => 4,
                    "price" => 12000,
                    "package" => ""
                )
            )
        );

        $member = json_encode( $goods );
        $score = time() + 1000;

        $this->redis->zAdd( $key, $score, $member );

        $res = array();
        $list = $this->redis->zrevrange( $key, 0, 5 );
        if( $list ){
            foreach( $list as &$mem) {
                $res[] = array(
                    'member' => $mem,
                    'score' => $this->redis->zscore( $key, $mem )
                );
            }
        }

        return $res;
    }

    /*初始化优惠信息
     * 从后台写入缓存的时候，需要直接设置key的过期时间，以优惠活动的过期时间为准
     *
     */
    public function init_promotion(){
        $key = REDIS_KEY_PROMOTION . '10001';

        $p = array(
            array(
                'threshold' => 2000,
                'discount' => 500,
            ),
            array(
                'threshold' => 5000,
                'discount' => 1000,
            ),
            array(
                'threshold' => 10000,
                'discount' => 2000,
            ),
        );

        $data = array(
            "name" => "满减优惠",
            "type" => "1",
            "begin_time" => time(),
            "end_time" => time() + 86400*365,
            "price" => json_encode( $p)
        );

        $this->redis->hmset( $key, $data );

        $key = REDIS_KEY_PROMOTION . '10002';

        $p = array(
            array(
                'threshold' => 20,
                'discount' => 2,
            ),
            array(
                'threshold' => 50,
                'discount' => 5,
            ),
            array(
                'threshold' => 100,
                'discount' => 10,
            ),
        );

        $data = array(
            "name" => "折扣优惠",
            "type" => "2",
            "begin_time" => time(),
            "end_time" => time() + 86400*365,
            "price" => json_encode( $p)
        );

        $this->redis->hmset( $key, $data );

        return $this->redis->hgetall( $key );

    }

    public function init_test_vip(){
        $key = REDIS_KEY_VIP . '10001';

        $list = array();
        $list[] = array(
            'shop_id' => 10001,
            'credit' => 10 );
        $list[] = array(
            'shop_id' => 10002,
            'credit' => 100 );
        $list[] = array(
            'shop_id' => 10003,
            'credit' => 1000 );
        $list[] = array(
            'shop_id' => 10004,
            'credit' => 10000 );

        foreach( $list as $l ){
            $this->redis->zAdd( $key, time(), json_encode($l) );
        }

        return $this->redis->zrange( $key, 0, 10 );


    }


    //初始化用户常去信息
    public function init_test_bonus(){
        /*
         *
         * { "id":"100001", //红包id
  "type":"1", //类型，1是全站红包，2是店铺红包
  "shop_id":"10001", //红包适用的商家id，全站红包为0
  "threshold":"1000", //满多少可减，不限的话设置为0即可
  "discount":"10",  //减少的金额
  "begin_time":"11234343434"， //开始生效时间戳
  "end_time":"11234343434" //有效期结束时间戳
}

         */

        $key =  REDIS_KEY_BONUS . '10001';
        $now = time();

        $list = array();
        $list[] = array( 'id' => 1,
                         'type' => 1,
                         'shop_id' => 10001,
                         'threshold' => 10000,
                         'discount' => 100,
                         'begin_time' => $now,
                         'end_time' => $now + 86400*30);
        $list[] = array( 'id' => 2,
            'type' => 2,
            'shop_id' => 10001,
            'threshold' => 10000,
            'discount' => 100,
            'begin_time' => $now,
            'end_time' => $now + 86400*30);
        $list[] = array( 'id' => 3,
            'type' => 2,
            'shop_id' => 10002,
            'threshold' => 20000,
            'discount' => 300,
            'begin_time' => $now,
            'end_time' => $now + 86400*30);
        $list[] = array( 'id' => 4,
            'type' => 2,
            'shop_id' => 10001,
            'threshold' => 5000,
            'discount' => 100,
            'begin_time' => $now,
            'end_time' => $now + 86400*90);
        $list[] = array( 'id' => 1,
            'type' => 1,
            'shop_id' => 10001,
            'threshold' => 10000,
            'discount' => 100,
            'begin_time' => $now,
            'end_time' => $now + 100);


        foreach( $list as $l ){
            $this->redis->zAdd( $key, $l['end_time'], json_encode($l) );
        }

        return $this->redis->zrange( $key, 0, 10 );

    }


    //初始化用户常去信息
    public function init_test_fav(){
        $key =  'OFTENGO_' . $this->_userid;

        $list = array();
        $list[] = array( 'id' => 10001, 'times' => 2 );
        $list[] = array( 'id' => 10002, 'times' => 5 );
        $list[] = array( 'id' => 10004, 'times' => 3 );
        $list[] = array( 'id' => 10006, 'times' => 7 );
        $list[] = array( 'id' => 10007, 'times' => 4 );
        $list[] = array( 'id' => 10009, 'times' => 1 );
        $list[] = array( 'id' => 10010, 'times' => 8 );


        foreach( $list as $l ){
            $this->redis->zAdd( $key, $l['times'], $l['id'] );
        }

        return $this->redis->zrange( $key, 0, 10 );

    }

    public function init_test_fav_eat(){
        $key =  REDIS_KEY_OFTEN_BUY . 10001 ;

        $list = array();
        $list[] = array( 'id' => 6, 'times' => 2 );
        $list[] = array( 'id' => 8, 'times' => 5 );
        $list[] = array( 'id' => 1, 'times' => 3 );
        $list[] = array( 'id' => 3, 'times' => 7 );
        $list[] = array( 'id' => 10, 'times' => 4 );
        $list[] = array( 'id' => 4, 'times' => 1 );
        $list[] = array( 'id' => 9, 'times' => 8 );


        foreach( $list as $l ){
            $this->redis->zAdd( $key, $l['times'], $l['id'] );
        }

        return $this->redis->zrange( $key, 0, 10 );

    }

    private function _init_test_shop( $id )
    {

        $key = "SHOP_" . $id;

        $this->redis->del($key);

        $data = array();
        $data['banner'] = 'https://img.4008823823.com.cn/kfcios/Version/218_82429.jpg';
        $data['name'] = '3W咖啡软件产业基地店_' . $id;
        $data['intro'] = '3W咖啡是创业者的咖啡';
        $data['thumbnail'] = 'https://img.4008823823.com.cn/kfcios/Version/190_67612.jpg';
        $data['phone'] = '17722690399';
        $data['full_address'] = '深圳市南山区软件产业基地4A首层';
        $data['longitude'] = '22.523740';
        $data['latitude'] = '113.937380';
        $data['open_time'] = '112321312321';
        $data['status'] = '1';
        $data['use_vip'] = '1';

        $prom = array( 10001, 10002 );

        $data['promotion'] = json_encode( $prom );
        $data['use_bonus'] = '1';

        $pic = array(
                            'https://img.4008823823.com.cn/kfcios/Version/190_67612.jpg',
                            'https://img.4008823823.com.cn/kfcios/Version/218_82451.jpg',
                            'https://img.4008823823.com.cn/kfcios/Version/218_82429.jpg'
                         );

        $data['pic_list'] =  json_encode( $pic );

        $data['reserve'] = '1';

        $vip = array(
            'desc' => '3W的会员卡',
            'color' => '#cccccc',
            'degree' => array(
                array(
                    'level' => 1,
                    'name' => '铁牌',
                    'score' => 1000,
                    'discount' => 2
                ),

                array(
                    'level' => 2,
                    'name' => '铜牌',
                    'score' => 3000,
                    'discount' => 2
                ),

                array(
                    'level' => 3,
                    'name' => '银牌',
                    'score' => 10000,
                    'discount' => 10
                )
            )

        );
        $data['vip_config'] =  json_encode( $vip );

        $goods = array(
            array(
                'group' => '热卖',
                'goods' => array(
                    1,2,3
                )
            ),
            array(
                'group' => '招牌',
                'goods' => array(
                    4,5,6
                )
            ),
            array(
                'group' => '套餐',
                'goods' => array(
                    7,8,9
                )
            ),
            array(
                'group' => '主食',
                'goods' => array(
                    10,11,12
                )
            )
        );

        $data['goods_list'] = json_encode( $goods );

        $this->redis->hmset( $key, $data );


    }

    /*
{ 'name': '十元换购',
require': 1 // 是否必选
       'multiple': 1 // 是否多选
       'options': [ {
            "id":"1", //optionid
            'name': '提拉米苏'，
            'price': 10,
            'status': "1"
            }，{
            "id":"1", //optionid
            'name': '冰淇淋'，
            'price': 10,
            'status': "1"
            } ]
     }
*/
    public function init_test_goods()
    {
        for( $i =1; $i<= 12; $i++ ){
            $this->_init_test_goods( $i );
        }

        return $this->redis->hgetall( REDIS_KEY_GOODS . 8);
    }


    private function _init_test_goods( $id )
    {

        $key = REDIS_KEY_GOODS . $id;
        $this->redis->del($key);

        $data = array();
        $data['shop_id'] = 10001;
        $data['name'] = '菜式1';
        $data['intro'] = '这里是菜品的介绍——' . $id;
        $data['thumbnail'] = 'https://img.4008823823.com.cn/kfcios/Version/218_82429.jpg';
        $data['price'] = 1000 * (1 + $id%3 );
        $data['stock'] = $id - 1;

        if( $id >= 7 and $id <= 9 ) {
            $data['package'] = array(
                array(
                    'name' => '杯型',
                    'id' => 1,
                    'require' => 1,
                    'multiple' => 0,
                    'options' => array(
                        array(
                            'id' => 1,
                            'name' => '小杯',
                            'price' => 0,
                            'status' => 1
                        ),
                        array(
                            'id' => 2,
                            'name' => '中杯',
                            'price' => 200,
                            'status' => 1
                        ),
                        array(
                            'id' => 3,
                            'name' => '大杯',
                            'price' => 400,
                            'status' => 0
                        )
                    )
                ),
                array(
                    'name' => '10元换购',
                    'id' => 2,
                    'require' => 0,
                    'multiple' => 1,
                    'options' => array(
                        array(
                            'id' => 4,
                            'name' => '提拉米苏',
                            'price' => 1000,
                            'status' => 1
                        ),
                        array(
                            'id' => 5,
                            'name' => '经典奶茶',
                            'price' => 1000,
                            'status' => 0
                        ),
                        array(
                            'id' => 6,
                            'name' => '香辣鸡翅',
                            'price' => 1000,
                            'status' => 1
                        )
                    )
                )

            );
        }
        else{
            $data['package'] = array();
        }

        $data['package'] = json_encode( $data['package'] );

       $this->redis->hmset( $key, $data );

    }

}