<?php


/**
 * error code 说明.
 * <ul>

 *    <li>-41001: encodingAesKey 非法</li>
 *    <li>-41003: aes 解密失败</li>
 *    <li>-41004: 解密后得到的buffer非法</li>
 *    <li>-41005: base64加密失败</li>
 *    <li>-41016: base64解密失败</li>
 * </ul>
 */
class ErrorCode
{
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;
}

/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
    public static $block_size = 16;

    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    function encode( $text )
    {
        $block_size = PKCS7Encoder::$block_size;
        $text_length = strlen( $text );
        //计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ( $text_length % PKCS7Encoder::$block_size );
        if ( $amount_to_pad == 0 ) {
            $amount_to_pad = PKCS7Encoder::block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr( $amount_to_pad );
        $tmp = "";
        for ( $index = 0; $index < $amount_to_pad; $index++ ) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    function decode($text)
    {

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

}

/**
 * Prpcrypt class
 *
 *
 */
class Prpcrypt
{
    public $key;

    function __construct( $k )
    {
        $this->key = $k;
    }

    /**
     * 对密文进行解密
     * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
     * @return string 解密得到的明文
     */
    public function decrypt( $aesCipher, $aesIV )
    {

        try {

            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');

            mcrypt_generic_init($module, $this->key, $aesIV);

            //解密
            $decrypted = mdecrypt_generic($module, $aesCipher);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            return array(ErrorCode::$IllegalBuffer, null);
        }


        try {
            //去除补位字符
            $pkc_encoder = new PKCS7Encoder;
            $result = $pkc_encoder->decode($decrypted);

        } catch (Exception $e) {
            print $e;
            return array(ErrorCode::$IllegalBuffer, null);
        }
        return array(0, $result);
    }
}

class WXBizDataCrypt
{
    private $appid;
    private $sessionKey;

    /**
     * 构造函数
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     * @param $appid string 小程序的appid
     */
    public function __construct( $appid, $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        $this->appid = $appid;
    }


    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData( $encryptedData, $iv, &$data )
    {

        if (strlen($this->sessionKey) != 24) {
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey=base64_decode($this->sessionKey);


        if (strlen($iv) != 24) {
            return ErrorCode::$IllegalIv;
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $pc = new Prpcrypt($aesKey);
        $result = $pc->decrypt($aesCipher,$aesIV);

        if ($result[0] != 0) {
            return $result[0];
        }

        $dataObj=json_decode( $result[1] );
        if( $dataObj  == NULL )
        {
            return ErrorCode::$IllegalBuffer;
        }
        if( $dataObj->watermark->appid != $this->appid )
        {
            return ErrorCode::$IllegalBuffer;
        }
        $data = $result[1];
        return ErrorCode::$OK;
    }

}

require_once APPPATH . '/models/BaseModel.php';
class WxModel extends BaseModel
{
    const WX_APPID = 'wx370d4f0cc06d81ba';
    const WX_SECRET = '5a7ec7a5f6672abe5c3404bdb66746e9';
    const WX_SESSION_URL = 'https://api.weixin.qq.com/sns/jscode2session';
    const SESSION_SEED_STR = 'ffgg%%((yui@@1~``!++-ll,,<lmnxixingke<<kenb>>919\|xi^xing$ke#nb)!)LLL!w';
    const SESSION_EXPIRED_SECONDS = 604800;  //604800;//86400*7, 7天

    private $_code;
    private $_wx_session = array();


    public function __construct()
    {
        parent::__construct();
    }

    public function check( $session )
    {
        return !$this->redis->exists( $session );
    }

    public function get_user( $session ){
        return $this->redis->hgetall( $session );
    }

    public function login( $code, $raw_data, $signature, $encrypted_data, $my_iv )
    {
        $this->_code = $code;
        $this->_get_session_key();


        //进行校验
        $my_signature = sha1( $raw_data . $this->_wx_session['session_key']);
        if( $my_signature != $signature ){
            throw new OpException('微信用户信息校验失败', ERROR_CODE_SIGNATURE_FAIL);
        }

        //解密
        $appid = self::WX_APPID;
        $sessionKey = $this->_wx_session['session_key'];
        $encryptedData = $encrypted_data;
        $iv = $my_iv;

        $user_data_str = array();
        $pc = new WXBizDataCrypt( $appid, $sessionKey );
        $errCode = $pc->decryptData( $encryptedData, $iv, $user_data_str );

        if ($errCode == 0) {
            $user_data = json_decode( $user_data_str );
            //print_r( $user_data );
            //成功解密，根据openid去查询该用户是否存在
            $this->load->model( 'auth/UserModel' );
            $now = time();

            $user_existed_info = $this->UserModel->getByField( 'open_id', $user_data->openId );

            //var_dump($user_existed_info);
            if( $user_existed_info ){
                $user_id = $user_existed_info['id'];

                //更新微信相关信息
                $update_data = array(
                    'nick_name' => $user_data->nickName,
                    'gender' => $user_data->gender,
                    'country' => $user_data->country,
                    'province' => $user_data->province,
                    'city' => $user_data->city,
                    'language' => $user_data->language,
                    'avatar_url' => $user_data->avatarUrl,
                    'update_time' => $now
                );

                $rows = $this->UserModel->update( $user_id, $update_data );
                if( $rows <= 0 ){
                    throw new OpException('更新用户信息失败', ERROR_CODE_MYSQL_FAIL);
                }

                //更新缓存
                $cache_data = array(
                    'nick_name' => $user_data->nickName,
                    'gender' => $user_data->gender,
                    'avatar_url' => $user_data->avatarUrl,
                );
                $this->redis->hmset( REDIS_KEY_USER . $user_id, $cache_data );
            }
            else {
                $insert_data = array(
                    'open_id' => $user_data->openId,
                    'nick_name' => $user_data->nickName,
                    'union_id' => isset($user_data->unionId) ? $user_data->unionId : '',
                    'gender' => $user_data->gender,
                    'country' => $user_data->country,
                    'province' => $user_data->province,
                    'city' => $user_data->city,
                    'language' => $user_data->language,
                    'avatar_url' => $user_data->avatarUrl,
                    'create_time' => $now,
                    'update_time' => $now
                );

                $user_id = $this->UserModel->insert($insert_data);

                //创建缓存
                $cache_data = array(
                    'nick_name' => $user_data->nickName,
                    'gender' => $user_data->gender,
                    'avatar_url' => $user_data->avatarUrl,
                );
                $this->redis->hmset( REDIS_KEY_USER . $user_id, $cache_data );
            }

        } else {
            throw new OpException('解密数据失败', ERROR_CODE_DECRPT_FAIL);
        }

        //session中存入自己的用户id
        $my_session_key = 'SESSION_' . $this->_create_session_str();
        $this->_wx_session['userid'] = $user_id;

        if ($this->redis->exists($my_session_key)) {
            //生成的随机数重复，理论上不可能出现
        } else {
            //session_key 不存在，开始写入；
            if( $this->redis->hmset( $my_session_key, $this->_wx_session ) == FALSE ){
                throw new OpException('写入用户SESSION失败', ERROR_CODE_REDIS_FAIL);
            }

            if( $this->redis->expire( $my_session_key, self::SESSION_EXPIRED_SECONDS ) == FALSE ){
                throw new OpException('设置用户SESSION过期时间失败', ERROR_CODE_REDIS_FAIL);
            }
        }

        return $my_session_key;
    }

    private function _get_session_key()
    {
        $postParams = array(
            'js_code' => $this->_code,
            'grant_type' => 'authorization_code',
            'appid' => self::WX_APPID,
            'secret' => self::WX_SECRET
        );

        $this->curl->ssl(FALSE);
        $retString = $this->curl->simple_get(self::WX_SESSION_URL, $postParams);

        if (empty($retString)) {
            throw new OpException('微信返回结果异常', ERROR_CODE_SIGNATURE_FAIL);
        }

        $ret = json_decode($retString, TRUE);
        if (empty($ret) || !empty($ret['errcode'])) {
            throw new OpException('微信操作失败:' . $ret['errmsg'], ERROR_CODE_SIGNATURE_FAIL);
        }

        $this->_wx_session = $ret;

    }

    private function _create_session_str()
    {
        return md5(uniqid(md5(microtime(true)), true));
    }
}