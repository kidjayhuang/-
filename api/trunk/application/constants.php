<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*
|--------------------------------------------------------------------------
| Application Error Message Definition
|--------------------------------------------------------------------------
|
| Used for application logic
|
*/
define('ERROR_MSG_SUCCESS', 'success');
define('ERROR_MSG_PARAM_INVALID', '参数不合法');
define('ERROR_MSG_PARAM_MISSING', '缺失参数');
define('ERROR_MSG_ALREADY_COMMIT', '已经参与过');
define('ERROR_MSG_OPRATION_FAILED', '操作失败');
define('ERROR_MSG_PINCODE_TOO_FREQUENTLY', '验证码发送过于频繁');
define('ERROR_MSG_PINCODE_EXPIRED', '验证码超时');
define('ERROR_MSG_PINCODE_OR_PHONE_ERROR', '验证码错误');
define('ERROR_MSG_PHONE_OCCUPIED', '手机号已被注册过');
define('ERROR_MSG_PHONE_NOT_VERIFIED', '手机号未验证');
define('ERROR_MSG_REGISTER_FAILED', '注册失败');
define('ERROR_MSG_LOGIN_FAILED', '密码不正确');
define('ERROR_MSG_ISSUE_TOKEN_FAILED', '创建Token失败');
define('ERROR_MSG_NOT_HTTPS', '不支持非HTTPS方式访问');

/*
|--------------------------------------------------------------------------
| Application Error Code Definition
|--------------------------------------------------------------------------
|
| Used for application logic
|
*/
define('ERROR_CODE_SUCCESS', 0);


//1开头为输入类错误
define('ERROR_CODE_PARAM_INVALID', 100001);

//2开头为存储类错误
define('ERROR_CODE_REDIS_FAIL', 200001);
define('ERROR_CODE_MYSQL_FAIL', 200002);

//3为安全类错误
define('ERROR_CODE_SIGNATURE_FAIL', 300001);
define('ERROR_CODE_DECRPT_FAIL', 300002);

//4业务类错误
define('ERROR_CODE_NOT_EXIST', 400001);
define('ERROR_CODE_SET_FAIL', 400002);
define('ERROR_CODE_CONFIG_FAIL', 400003);
define('ERROR_CODE_SELL_OUT', 400004);
define('ERROR_CODE_CART_NULL', 400005);
define('ERROR_CODE_C', 400005);


//定义redis的key前缀

define( 'REDIS_KEY_SHOP', 'SHOP_');

define( 'REDIS_KEY_OFTEN_BUY', 'OFTENBUY_');

define( 'REDIS_KEY_OFTEN_GO', 'OFTENGO_');

define( 'REDIS_KEY_GOODS', 'GOODS_');

define( 'REDIS_KEY_PROMOTION', 'PROMOTION_');

define( 'REDIS_KEY_ORDER', 'ORDER_');

define( 'REDIS_KEY_USER_ORDER', 'USERORDER_');

define( 'REDIS_KEY_CART', 'CART_');

define( 'REDIS_KEY_STORE_SHOP', 'STORESHOP_');

define( 'REDIS_KEY_STORE_GOODS', 'STOREGOODS_');

define( 'REDIS_KEY_BONUS', 'BONUS_');

define( 'REDIS_KEY_VIP', 'VIP_');


//定义一些数据常量
define( 'USER_OFTEN_GO_NUM', 5 );

define( 'USER_OFTEN_EAT_NUM', 5 );

define( 'USER_STORE_LIST_NUM', 5 );

define( 'USER_STORE_ALL_NUM', 100 );

define( 'USER_CART_MAX_NUM', 100 );

define( 'USER_BONUS_MAX_NUM', 100 );

define( 'USER_VIP_MAX_NUM', 100 );

define( 'PAGE_ORDER_NUM', 10 );

//类型定义

//实体类型，暂时只用于收藏
define( 'TYPE_ENTITY_SHOP', 1 );

define( 'TYPE_ENTITY_GOODS', 2 );

define( 'STATUS_SELL_OUT', 0 );

//定义订单状态

define( 'STATUS_ORDER_CREATE', 100 );
define( 'STATUS_ORDER_PAYED', 101 );
define( 'STATUS_ORDER_PROCESS', 102 );
define( 'STATUS_ORDER_FINISH', 103 );


define( 'STATUS_BONUS_NOT_BEGIN', 1 );

define( 'STATUS_BONUS_END', 2 );

define( 'STATUS_BONUS_IN_USE', 3 );


//定义优惠类型
define( 'TYPE_PROMOTION_MINUS', 1 );  //满减

define( 'TYPE_PROMOTION_DISCOUNT', 2 );  //满折扣

//定义红包类型
define( 'TYPE_BONUS_ALL', 1 ); //全站红包

define( 'TYPE_BONUS_SHOP', 2 ); //商铺红包





