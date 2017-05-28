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
define('ERROR_CODE_NOT_LOGIN', 100002);

//2开头为存储类错误
define('ERROR_CODE_REDIS_FAIL', 200001);
define('ERROR_CODE_MYSQL_FAIL', 200002);

//3为安全类错误
define('ERROR_CODE_SIGNATURE_FAIL', 300001);
define('ERROR_CODE_DECRPT_FAIL', 300002);
define('ERROR_CODE_FREQUENCY_EXCEED', 300003);

//4业务类错误
define('ERROR_CODE_NOT_EXIST', 400001);
define('ERROR_CODE_ACTIVITY_EXCEED', 400002);
define('ERROR_CODE_IN_ACTIVITY', 400003);
define('ERROR_CODE_NOT_ADMIN', 400004);
define('ERROR_CODE_USER_IDENTIFY', 400005);
define('ERROR_CODE_EXCEED', 400006);

//5云存储
    define('ERROR_CODE_BAIDU_LBS', 500001);

//定义redis的key前缀
define( 'REDIS_SEPARATOR', '_');
define( 'REDIS_KEY_USER', 'USER_');

define( 'REDIS_KEY_ACTIVITY_HASH', 'A_I_' );

define( 'REDIS_KEY_MY_CREATE_ACTIVITY', 'MY_C_' );
define( 'REDIS_KEY_MY_JOIN_ACTIVITY', 'MY_J_' );

define( 'REDIS_KEY_MEMBER', 'M_' );
define( 'REDIS_KEY_MEMBER_INFO', 'M_I_' );

define( 'REDIS_KEY_VERIFY_INFO', 'V_I_' );
define( 'REDIS_KEY_VERIFY', 'V_' );
define( 'REDIS_KEY_MY_VERIFY', 'MY_V_' );
define( 'REDIS_KEY_VERIFY_REMARK', 'V_R_' );

define( 'REDIS_KEY_NOTICE_INFO', 'N_I_' );
define( 'REDIS_KEY_NOTICE', 'N_' );

define( 'REDIS_KEY_REPLY', 'R_I_' );
define( 'REDIS_KEY_REPLY_LIST', 'R_' );


//定义活动状态
define( 'ACTIVITY_STATUS_NORMAL', 101 );
define( 'ACTIVITY_STATUS_STOP', 110);
define( 'ACTIVITY_STATUS_FINISH', 120 );
define( 'ACTIVITY_STATUS_CANCEL', 200 );

//定义用户身份
define( 'USER_TYPE_CREATOR', 1 );
define( 'USER_TYPE_MEMBER', 2 );


//定义用户数据限制
define( 'LIMIT_CREATE_ACTIVITY', 1000 );
define( 'LIMIT_ACTIVITY_MEMBER', 2000 );
define( 'LIMIT_JOIN_ACTIVITY', 10000 );
define( 'LIMIT_INDEX_MEMBER', 12 );


define( 'LIMIT_SIMPLE_ARTICLE_TEXT_MIN', 2 );
define( 'LIMIT_SIMPLE_ARTICLE_TEXT_MAX', 1000 );
define( 'LIMIT_SIMPLE_ARTICLE_PIC', 9 );
define( 'LIMIT_TOPIC_LENGTH', 60 );
define( 'LIMIT_TOPIC_RECOMMEND', 10 );
define( 'LIMIT_TOPIC_HOT', 20 );
define( 'LIMIT_TOP_ARTICLE', 5 );
define( 'LIMIT_REPLY_COUNT', 10000 );


//定义通知类型
define( 'NOTICE_TYPE_JOIN', 101 );
define( 'NOTICE_TYPE_VERIFY', 102 );
define( 'NOTICE_TYPE_QUIT', 103 );
define( 'NOTICE_TYPE_KICK', 104 );

//定义加入活动结果状态
define( 'JOIN_RESULT_IN', 1 );
define( 'JOIN_RESULT_VERIFY', 2 );

//定义加入活动审核结果
define( 'VERIFY_STATUS_WAIT', 100 );
define( 'VERIFY_STATUS_PASS', 101 );
define( 'VERIFY_STATUS_REJECT', 102 );

//定义显示数量
define( 'MEMBER_COUNT_PAGE', 20 );
define( 'NOTICE_COUNT_PAGE', 20 );
define( 'ARTICLE_COUNT_PAGE', 20 );
define( 'REPLY_COUNT_PAGE', 20 );
define( 'RECOMMEND_CIRCLE_COUNT', 3 );

//定义活动类型
define( 'ACTIVITY_TYPE_SIMPLE', 101); //即时活动
define( 'ACTIVITY_TYPE_RICH', 102); //长线活动


//定义付款方式
define( 'PAY_TYPE_FREE', 100); //AA
define( 'PAY_TYPE_AA', 101); //AA
define( 'PAY_TYPE_I_PAY', 102); //我付


//定义回复类型
define( 'REPLY_TYPE_ACTIVITY', 101 );
define( 'REPLY_TYPE_REPLY', 102 );

//定义回复状态
define( 'REPLY_STATUS_NORMAL', 100 );
define( 'REPLY_STATUS_DELETE', 101 );










