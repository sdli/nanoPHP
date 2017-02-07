<?php 
include_once("nano.common.php");

//1，为云东家，2为云东购，3为小程序
define("program_type",2);
define("key","ydongj");

//微信配置文件
define('appId','wx352d72a89696b3e2');
define('appSecret','13bf27348efc82434779f716021ae2c7');
define("appIdG","wx3cfcc5b62001e7da");
define("appSecretG","7c7379e5aa5bc1eabda61d33a41f52b2");
define("appIdX","wx06431bcdd598b713");
define("appSecretX","");

define('api_url','http://120.76.194.75:8080');
define('real_url','http://120.76.25.41:8080');
define('wx_token_url',real_url.'/yunpos/api/v1/weixin/getWeixinAccessToken.do');
define('user_login',api_url.'/yunpos/api/v1/person/login.do');
define('user_shop_list',api_url.'/yunpos/api/v1/channel/findChannelStore.do');
define("get_access_token_url",api_url."/yunpos/api/v1/weixin/getWeixinAccessToken.do");
define("get_map",api_url."/yunpos/api/v1/channel/channelAreaList.do");
define("check_bank_branches",api_url."/yunpos/api/v1/channel/bankList.do");
define("save_location",api_url."/yunpos/api/v1/channel/addChannelArea.do");
define("save_step2_fuyou",api_url."/yunpos/api/v1/channel/addChannelFuyou.do");
define("save_step2_sanweidu",api_url."/yunpos/api/v1/channel/addChannelSwd.do");
define("save_step3_fuyou",api_url."/yunpos/api/v1/channel/addChannelFuyou.do");
define("save_step3_sanweidu",api_url."/yunpos/api/v1/channel/addChannelSwd.do");
define("get_saved_data",api_url."/yunpos/api/v1/channel/getInfo.do");
define("get_img_list",api_url."/yunpos/api/v1/channel/uploadPicInfo.do");
define("img_group",json_encode(array(
	"shenfen" => array(
		"name"=> "身份证照片",
		"list"=> "身份证正面,身份证反面"
	),
	"sanzheng"=> array(
		"name"=> "三证合一",
		"list"=> "企业营业执照"
	),
	"zhizhao" => array(
		"name"=> "营业执照、组织和税务证",
		"list" => "企业营业执照,组织机构代码证,税务登记证"
	),
	"bank"=> array(
		"name"=> "银行卡照片",
		"list"=> "入账银行卡正面，入账银行卡反面"
	),
	"shop"=>array(
		"name"=> "商户实景",
		"list"=> "门头照（包括门牌号及营业名称）,收银台,内部全景"
	),
	"agreement"=>array(
		"name"=> "协议书",
		"list" => "协议书(左侧),协议书(右侧)"
	),
	"nonlegal" => array(
		"name" => "非法人身份证照片",
		"list" => "非法人身份证正面,非法人身份证反面"
	),
	"geti" => array(
		"name" => "个体营业执照",
		"list" => "个体营业执照"
	),
	"others" => array(
		"name" => "其他证明",
		"list" => "特约商户审核表,关系证明,协议书"
	)
)));
