<?php

/**
 * 检测login状态，通过tenantid和access_token检测登陆状态
 * @return [type] [description]
 */
function checkLoginStatus(){
	if(!empty($_SESSION["tenantId"]) && !empty($_SESSION["access_token"])){
		return true;
	}else{
		return false;
	}
}
/**
 * 刷新access_token方法
 * @return [string] [access_token]
 */
function get_access_token(){
	if(empty($_SESSION["access_expire"]) || time() > $_SESSION["access_expire"]){
		$postdata = array(
			'userName'=>$_SESSION['userName'],
			'password'=>$_SESSION['password']
		);
		$method = "post";

		//建立http请求，并将结果回调于方法
		$loginReq = new httpRequest($method,user_login,$postdata,function($result){
			$tmpdata = json_decode($result,true);
			if($tmpdata['code'] == 1){
				$_SESSION['access_token'] = $tmpdata['datas']['access_token'];
				$_SESSION["access_expire"] = time() + 180;
			}
			// echo $result;
			return $_SESSION['access_token'];
		});
		$token = $loginReq -> exec_request();
		return $token;
	}else{
		return $_SESSION['access_token'];
	}
}

/**
 * 获取删除不可作为进件区域的地理位置
 * @return [json] [后端java服务器返回的json数据]
 */
function getDeletedArea(){
	$postData = array(
		"alType" => 2,
		"storeId" => v("storeId"),
		"access_token" => get_access_token()
	);
	$req = new httpRequest("post",get_map,$postData,function($result){
		return $result;
	});
	$result = json_decode($req->exec_request(),true);
	return $result;
}

/**
 * [save_location description]
 * @return [type] [description]
 */
function save_location($arr){
	$postData = array(
		"merchantId" => $_SESSION["tenantId"],
		"merchantBranchId" => $arr["merchantBranchId"],
		"merchantBranchPro" => $arr["merchantBranchPro"],
		"merchantBranchCity" => $arr["merchantBranchCity"],
		"merchantBranchArea" => $arr["merchantBranchArea"],
		'access_token'=>get_access_token()
	);
	$_SESSION["cityCode"] = $arr["merchantBranchCity"];
	$req = new httpRequest("post",save_location,$postData,function($result){return $result;});
	return $req -> exec_request();
}

/**
 * 传入富有账户信息并保存，返回保存结果
 * @param  [array] $arr [由前端post数据的数组，注意posSnNumber为逗号隔开]
 * @return [json]      [后端java服务器返回的json数据]
 */
function save_step2_fuyou($arr){
	$postData = array(
		"accountCardName" => $arr["accountCardName"],
		"accountBankName" => $arr["accountBankName"],
		"accountNumber" => $arr["accountNumber"],
		"accountCardNum" => $arr["accountCardNum"],
		"businessNature" => $arr["businessNature"],
		"accountType" => ($arr["accountType"] != "empty")?$arr["accountType"]:"",
		"statementsType" => ($arr["statementsType"] !="empty")?$arr["statementsType"]: "",
		"fuyouType" => $arr["fuyouType"],
		"storeId" => $arr["storeId"],
		"posSnNumber" => $arr["POS"],
		"tenantId" => $_SESSION["tenantId"],
		"access_token" => get_access_token()
	);
	$req = new httpRequest("post",save_step2_fuyou,$postData,function($result){return $result;});
	$result = $req -> exec_request();
	$arr = json_decode($result,true);
	if($arr["code"] == 1){
		$_SESSION["infoId"] = $arr["datas"]["fyInfoId"];
	}
	return $result;
}
/**
 * 传入三维度账户信息，并保存
 * @param  [array] $arr [由前端post数据的数组，注意posSnNumber为逗号隔开]
 * @return [json]      [后端java服务器返回的json数据]
 */
function save_step2_sanweidu($arr){
	$postData = array(
		"accountCardName" => $arr["accountCardName"],
		"accountBankName" => $arr["accountBankName"],
		"accountBankNumber" => $arr["accountNumber"],
		"accountCardNum" => $arr["accountCardNum"],
		"businessNature" => $arr["businessNature"],
		"accountType" => $arr["accountType"],
		"swdType" => $arr["swdType"],
		"storeId" => $arr["storeId"],
		"tenantId" => $_SESSION["tenantId"],
		"access_token" => get_access_token()
	);
	$req = new httpRequest("post",save_step2_sanweidu,$postData,function($result){return $result;});
	$result = $req -> exec_request();
	$arr = json_decode($result,true);
	if($arr["code"] == 1){
		$_SESSION["infoId"] = $arr["datas"]["swdInfoId"];
	}
	return $result;
}

/**
 * 传入富友营业执照信息，并保存
 * @param  [array] $arr [由前端post数据的数组，注意posSnNumber为逗号隔开]
 * @return [json]      [后端java服务器返回的json数据]
 */
function save_step3_fuyou($arr){
	$postData = array(
		"tenantName" => $arr["tenantName"],
		"tenantNameSimply" => $arr["tenantNameSimply"],
		"corporateMan" => $arr["corporateMan"],
		"corporateManIdcard" => $arr["corporateManIdcard"],
		"idcardExpired" => $arr["idcardExpired"],
		"idCardIsCredentials" => ($arr["idCardIsCredentials"] == "empty")?0:1,
		"credentialsExpired" => $arr["credentialsExpired"],
		"isCredentials" => ($arr["isCredentials"] == "empty")?0:1,
		"businessCode" => $arr["businessCode"],
		"businessScope" => $arr["businessScope"],
		"connectEmail" => $arr["connectEmail"],
		"connectMan" => $arr["connectMan"],
		"connectPhone" => $arr["connectPhone"],
		"connectCustomer" => $arr["connectCustomer"],
		"connectAddress" => $arr["connectAddress"],
		"businessScopeCode" => $arr["businessScopeCode"],
		"registAddress" => $arr["registAddress"],
		"certType" => $arr["certType"],
		"cityCode" => $_SESSION["cityCode"],
		"fuyouType" => $arr["fuyouType"],
		"storeId" => $arr["storeId"],
		"tenantId" => $_SESSION["tenantId"],
		"access_token" => get_access_token()
	);
	$req = new httpRequest("post",save_step3_fuyou,$postData,function($result){return $result;});
	$result = $req -> exec_request();
	$arr = json_decode($result,true);
	if($arr["code"] == 1){
		$_SESSION["infoId"] = $arr["datas"]["fyInfoId"];
	}
	return $result;
}

/**
 * 传入富友营业执照信息，并保存
 * @param  [array] $arr [由前端post数据的数组，注意posSnNumber为逗号隔开]
 * @return [json]      [后端java服务器返回的json数据]
 */
function save_step3_sanweidu($arr){
	$postData = array(
		"tenantName" => $arr["tenantName"],
		"tenantNameSimply" => $arr["tenantNameSimply"],
		"corporateMan" => $arr["corporateMan"],
		"corporateManIdcard" => $arr["corporateManIdcard"],
		"idcardExpired" => $arr["idcardExpired"],
		"idCardIsCredentials" => ($arr["idCardIsCredentials"] == "empty")?0:1,
		"businessScope" => $arr["businessScope"],
		"connectEmail" => $arr["connectEmail"],
		"connectMan" => $arr["connectMan"],
		"connectPhone" => $arr["connectPhone"],
		"connectCustomer" => $arr["connectCustomer"],
		"businessScopeCode" => $arr["businessScopeCode"],
		"tenantAddress" => $arr["tenantAddress"],
		"certType" => $arr["certType"],
		"cityCode" => $_SESSION["cityCode"],
		"swdType" => $arr["swdType"],
		"storeId" => $arr["storeId"],
		"tenantId" => $_SESSION["tenantId"],
		"access_token" => get_access_token()
	);
	$req = new httpRequest("post",save_step3_sanweidu,$postData,function($result){return $result;});
	$result = $req -> exec_request();
	$arr = json_decode($result,true);
	if($arr["code"] == 1){
		$_SESSION["infoId"] = $arr["datas"]["swdInfoId"];
	}
	return $result;
}

function get_saved($storeId){
	$postData = array(
		"storeId" => $storeId,
		"access_token" => get_access_token()
	);
	$req = new httpRequest("post",get_saved_data,$postData,function($result){
		return $result;
	});
	$result = $req -> exec_request();
	return $result;
}

function getImgList($code,$storeId){
	$postData = array(
		"infoId" => $_SESSION['infoId'],
		"channel" => $code,
		"storeId" => $storeId,
		"access_token" => get_access_token()
	);

	$req = new httpRequest("post",get_img_list,$postData,function($result){
		return $result;
	});

	return $req -> exec_request();
}

function groupImage($list){
	$arr = array();
	foreach($list as $key => $val){
		$content = checkName($val["materialName"]);
		if($content){
			$arr[$content["name"]] = !empty($arr[$content["name"]])?$arr[$content["name"]]:array();
			array_push($arr[$content["name"]],array("name"=>$val["materialName"],"materialId"=>$val["materialId"],"en"=>$content["en"]));
		}else{
			if($val["materialName"] != "其他"){
				$arr["其他"] = !empty($arr["其他"])?$arr["其他"]:array();
				array_push($arr["其他"],array("name"=>$val["materialName"],"materialId"=>$val["materialId"],"en"=> "others"));
			}
		}
	}
	return $arr;
}

function checkName($name){
	$group = json_decode(img_group,true);
	foreach($group as $key => $val){
		if(strpos($val["list"],$name) !== false){
			return array(
				"name" => $val['name'],
				"en" => $key
			);
		}
	}
	return false;
}