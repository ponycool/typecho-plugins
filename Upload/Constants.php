<?php

/**
 * Created by PhpStorm
 * User: Pony
 * Date: 2022/11/4
 * Time: 3:16 PM
 */

namespace Typecho\Plugin\Upload;

defined('UPLOAD_PLUGIN_PATH') || define('UPLOAD_PLUGIN_PATH', __DIR__ . DIRECTORY_SEPARATOR);
defined('SDK_PATH') || define('SDK_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'SDK');
// OSS
defined('OSS') || define('OSS', 'aliyun-oss-php-sdk');
defined('OSS_VERSION') || define('OSS_VERSION', '2.6.0');
defined('SDK_OSS') || define('SDK_OSS', OSS . '-' . OSS_VERSION);
// COS
defined('COS') || define('COS', 'cos-sdk');
defined('COS_VERSION') || define('COS_VERSION', 'v5-7');
defined('SDK_COS') || define('SDK_COS', COS . COS_VERSION);
// OBS
defined('OBS') || define('OBS', 'huaweicloud-sdk-php-obs');
defined('OBS_VERSION') || define('OBS_VERSION', 'v3.22.6');
defined('SDK_OBS') || define('SDK_OBS', OBS . OBS_VERSION);

defined('PLUGIN_NAME') || define('PLUGIN_NAME', 'Upload_Plugin');
// 上传文件目录，相对网站根目录
defined('UPLOAD_DIR') || define('UPLOAD_DIR', 'usr/uploads/');
// 上传文件根目录，绝对路径
defined('UPLOAD_ROOT') || define('UPLOAD_ROOT', __TYPECHO_ROOT_DIR__ . DIRECTORY_SEPARATOR . UPLOAD_DIR);

// 日志目录
defined('UPLOAD_LOG_DIR') || define('UPLOAD_LOG_DIR', 'usr/logs');
defined('UPLOAD_LOG_ROOT') || define('UPLOAD_LOG_ROOT', __TYPECHO_ROOT_DIR__ . DIRECTORY_SEPARATOR . UPLOAD_LOG_DIR);
// 日志前缀
defined('UPLOAD_LOG_PREFIX') || define('UPLOAD_LOG_PREFIX', 'upload');

defined('OBJECT_STORAGE') || define('OBJECT_STORAGE', [
    'oss' => 'OSS',
    'cos' => 'COS',
    'obs' => 'OBS'
]);

defined('ENDPOINT_TYPE') || define('ENDPOINT_TYPE', [
    'external' => '外网',
    'intranet' => '内网'
]);

defined('OSS_ENDPOINT') || define('OSS_ENDPOINT', [
    "oss-cn-hangzhou" => "华东1（杭州）oss-cn-hangzhou",
    "oss-cn-shanghai" => "华东2（上海）oss-cn-shanghai",
    "oss-cn-qingdao" => "华北1（青岛）oss-cn-qingdao",
    "oss-cn-beijing" => "华北2（北京）oss-cn-beijing",
    "oss-cn-zhangjiakou" => "华北3（张家口）oss-cn-zhangjiakou",
    "oss-cn-huhehaote" => "华北5（呼和浩特）oss-cn-huhehaote",
    "oss-cn-wulanchabu" => "华北6（乌兰察布）oss-cn-wulanchabu",
    "oss-cn-shenzhen" => "华南1（深圳）oss-cn-shenzhen",
    "oss-cn-heyuan" => "华南2（河源）oss-cn-heyuan",
    "oss-cn-chengdu" => "西南1（成都）oss-cn-chengdu",
    "oss-cn-hongkong" => "中国（香港）oss-cn-hongkong",
    "oss-us-west-1" => "美国西部1（硅谷）oss-us-west-1",
    "oss-us-east-1" => "美国东部1（弗吉尼亚）oss-us-east-1",
    "oss-ap-southeast-1" => "亚太东南1（新加坡）oss-ap-southeast-1",
    "oss-ap-southeast-2" => "亚太东南2（悉尼）oss-ap-southeast-2",
    "oss-ap-southeast-3" => "亚太东南3（吉隆坡）oss-ap-southeast-3",
    "oss-ap-southeast-5" => "亚太东南5（雅加达）oss-ap-southeast-5",
    "oss-ap-northeast-1" => "亚太东北1（日本）oss-ap-northeast-1",
    "oss-ap-south-1" => "亚太南部1（孟买）oss-ap-south-1",
    "oss-eu-central-1" => "欧洲中部1（法兰克福）oss-eu-central-1",
    "oss-eu-west-1" => "英国（伦敦）oss-eu-west-1",
    "oss-me-east-1" => "中东东部1（迪拜）oss-me-east-1",
]);

defined('OBS_ENDPOINT') || define('OBS_ENDPOINT', [
    "obs.cn-north-4" => "华北（北京四）obs.cn-north-4",
    "obs.cn-north-1" => "华北（北京一）obs.cn-north-1",
    "obs.cn-north-9" => "华北（乌兰察布一）obs.cn-north-9",
    "obs.cn-east-2" => "华东（上海二）obs.cn-east-2",
    "obs.cn-east-3" => "华东（上海一）obs.cn-east-3",
    "obs.cn-south-1" => "华南（广州）obs.cn-south-1",
    "obs.cn-southwest-2" => "西南（贵阳一）obs.cn-southwest-2",
    "obs.ap-southeast-1" => "中国（香港）obs.ap-southeast-1",
    "obs.ap-southeast-2" => "亚太（曼谷）obs.ap-southeast-2",
    "obs.ap-southeast-3" => "亚太（新加坡）obs.ap-southeast-3",
    "obs.la-north-2" => "拉美（墨西哥城二）obs.la-north-2",
    "obs.na-mexico-1" => "拉美（墨西哥城一）obs.na-mexico-1",
    "obs.sa-brazil-1" => "拉美（圣保罗一）obs.sa-brazil-1",
    "obs.la-south-2" => "拉美（圣地亚哥）obs.la-south-2",
    "obs.af-south-1" => "非洲（约翰内斯堡）obs.af-south-1",
]);

defined('COS_ENDPOINT') || define('COS_ENDPOINT', [
    "cos.ap-beijing-1" => "中国大陆（北京一区）cos.ap-beijing-1",
    "cos.ap-beijing" => "中国大陆（北京）cos.ap-beijing",
    "cos.ap-nanjing" => "中国大陆（南京）cos.ap-nanjing",
    "cos.ap-shanghai" => "中国大陆（上海）cos.ap-shanghai",
    "cos.ap-guangzhou" => "中国大陆（广州）cos.ap-guangzhou",
    "cos.ap-chengdu" => "中国大陆（成都）cos.ap-chengdu",
    "cos.ap-chongqing" => "中国大陆（重庆）cos.ap-chongqing",
    "cos.ap-shenzhen-fsi" => "中国大陆（深圳金融）cos.ap-shenzhen-fsi",
    "cos.ap-shanghai-fsi" => "中国大陆（上海金融）cos.ap-shanghai-fsi",
    "cos.ap-beijing-fsi" => "中国大陆（北京金融）cos.ap-beijing-fsi",
    "cos.ap-hongkong" => "亚太（中国香港）cos.ap-hongkong",
    "cos.ap-singapore" => "亚太（新加坡）cos.ap-singapore",
    "cos.ap-mumbai" => "亚太（孟买）cos.ap-mumbai",
    "cos.ap-jakarta" => "亚太（雅加达）cos.ap-jakarta",
    "cos.ap-seoul" => "亚太（首尔）cos.ap-seoul",
    "cos.ap-bangkok" => "亚太（曼谷）cos.ap-bangkok",
    "cos.ap-tokyo" => "亚太（东京）cos.ap-tokyo",
    "cos.na-siliconvalley" => "北美（硅谷）cos.na-siliconvalley",
    "cos.na-ashburn" => "北美（弗吉尼亚）cos.na-ashburn",
    "cos.na-toronto" => "北美（多伦多）cos.na-toronto",
    "cos.sa-saopaulo" => "南美（圣保罗）cos.sa-saopaulo",
    "cos.eu-frankfurt" => "欧洲（法兰克福）cos.eu-frankfurt",
    "cos.eu-moscow" => "欧洲（莫斯科）cos.eu-moscow",
]);

defined('COS_ENDPOINT') || define('COS_ENDPOINT', []);
defined('OBS_ENDPOINT') || define('OBS_ENDPOINT', []);