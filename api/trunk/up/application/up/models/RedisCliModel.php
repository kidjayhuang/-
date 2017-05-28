<?php

/*
 * ray 2016.12.06 created
 * redis操作方法model
 * $this->load->model('RedisCliModel', 'redis');
 * $this->redis->connect();
 * $this->redis->set('test1', 'rayqin test');
 *
 * 添加connect方法是为了可以在同一个项目中连接多个redis
 *
 * 使用方式
 */

class RedisCliModel extends CI_Model{

    protected $_redis;

    public function __construct( ){
        if ( !$this->is_supported()){
            throw new OpException('没有安装redis扩展', ERROR_CODE_REDIS_FAIL);
        }
    }

    public function connect( $config = 'redis' ){
        if ($this->config->load( $config, TRUE, TRUE)){
            $config = $this->config->item( $config );
        }
        else{
            throw new OpException('没有配置redis连接', ERROR_CODE_REDIS_FAIL);
        }

        $this->_redis = new Redis();
        if ($this->_redis->connect($config['redis_host'], $config['redis_port']) == false) {
            throw new OpException( $this->_redis->getLastError(), ERROR_CODE_REDIS_FAIL);
        }

        //鉴权
        if ($this->_redis->auth( $config['redis_password']) == false) {
            throw new OpException($this->_redis->getLastError(), ERROR_CODE_REDIS_FAIL);
        }
    }

    /*
     * 直接调用扩展支持的所有的方法
     * 后面考虑是否要加入白名单或者黑名单的功能
     *
     */
    public function __call( $method, $params ){

        if( !$this->_redis ){
            throw new OpException('还未连接redis', ERROR_CODE_REDIS_FAIL);
        }

        $params_cnt = count( $params );
        switch ( $params_cnt ){
            case 1:
                return $this->_redis->$method( $params[0] );
                break;
            case 2:
                return $this->_redis->$method( $params[0], $params[1] );
                break;
            case 3:
                return $this->_redis->$method( $params[0], $params[1], $params[2] );
                break;
            default:
                throw new OpException( "redis不支持{$method}操作", ERROR_CODE_REDIS_FAIL);
                break;

        }
    }


    /**
     * Check if Redis driver is supported
     *
     * @return	bool
     */
    public function is_supported()
    {
        return extension_loaded('redis');
    }

    // ------------------------------------------------------------------------

    /**
     * Class destructor
     *
     * Closes the connection to Redis if present.
     *
     * @return	void
     */
    public function __destruct()
    {
        if ($this->_redis)
        {
            $this->_redis->close();
        }
    }




} 