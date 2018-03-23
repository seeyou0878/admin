<?php
namespace Admin;

class Branch{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_branch',
			/*col*/
			array(
				'id',
				'name', 'domain', 'code', 'front_alias', 'admin_alias', 'super_alias', 'receive_id', 'limit',
				'config_extra', 'config_sms', 'config_allbet', 'config_comebets', 'config_goldenasia', 'config_globalgaming', 'config_microsova', 
				'config_salon', 'config_supersport', 'config_xinxin', 'config_ebet', 'config_dreamgame', 'config_orientalgame', 'config_zhifubao',
				'config_ninetynine', 'config_globebet','config_playstar','config_evoplay', 'config_booongo',
				'status_id'),
			/*col_ch*/
			array(
				'id',
				'分站名稱',
				'分站網域,多組換行',
				'分站代碼,用於建立遊戲帳號使用',
				'前台域名別名,多組換行<br>設定方式: 代理帳號,網域<br>例abc,example.com',
				'後台域名別名,多組換行',
				'超級域名,多組換行<br>不受維修關站限制',
				'指定收款,收款設定優先順序:<br>1.會員設定<br>2.分站設定<br>3.金流設定',
				'註冊限制,手機可以註冊至多 N 個帳號<br>( 0 = 不限制)<br>此變更不溯及既往的帳號設定',
				'訂單設定,手續費計算方式<br>(原始金額 x 百分比)<br> + 固定値',
				'台灣簡訊',
				'Allbet',
				'ComeBets',
				'GoldenAsia',
				'GlobalGaming',
				'Microsova',
				'Salon',
				'SuperSport',
				'XinXin',
				'Ebet',
				'DreamGame',
				'OrientalGame',
				'ZhiFuBao',
				'NinetyNine',
				'GlobeBet',
				'PlayStar',
				'EvoPlay',
				'BooonGo',
				'狀態,關閉分站可以進入維修狀態'
			),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', '', '', '', '', '', '', 
				($_SESSION['user']['cross'])? 't_receive,title,id': 't_receive,title,id,{"branch_id": ' . $_SESSION['user']['branch_id'] . '}', 
				 '','', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 't_status,alias,id'),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'col-md-2 col-sm-2 col-xs-4',
				'col-md-8 col-sm-8 col-xs-4',
				'col-md-2 col-sm-2 col-xs-4',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array('hidden', 'text', 'textarea', 'text', 'textarea', 'textarea', 'textarea', 'select', 'text', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'json', 'select'),
			/*authority check*/
			array(
				$_SESSION['auth']['branch_review'] ?? 0,
				$_SESSION['auth']['branch_create'] ?? 0,
				$_SESSION['auth']['branch_modify'] ?? 0,
				$_SESSION['auth']['branch_delete'] ?? 0,
			),
			/*medoo*/
			\Box::obj('db'),
			/*config*/
			array(
				'preset' => array(
					'config_extra' => array(
						'fixed,提領手續固定値' => '',
						'percent,提領手續百分比' => '',
						'store_atm_min,ATM儲值下限(預設200 但不超過上限)' => '',
						'store_atm_max,ATM儲值上限(預設20000)' => '',
						'store_cvs_min,超商儲值下限(預設200 但不超過上限)' => '',
						'store_cvs_max,超商儲值上限(預設20000)' => '',
						'store_weekly,儲值(超商)每週上限(0=不限制)' => '',
						'store_card_min,信用卡儲值下限(預設200 但不超過上限)' => '',
						'store_card_max,信用卡儲值上限(預設20000)' => '',
					),
					'config_sms' => array('Account' => '', 'Password' =>''),
					'config_allbet' => array('_url,API 網址' => '', '_property_id,propertyId' => '', '_agent,代理用戶名' => '', '_des_key,DES key' => '', '_md5_key,MD5 key' => '', '_suffix,手機版後綴' => ''),
					'config_globalgaming' => array('_url,API網址' => '', '_url_report,報表API網址' => '', '_cagent,cagent' => '', '_des_key,DES key' => '', '_md5_key,MD5 key' => ''),
					'config_microsova' => array('_url,API網址' => '', '_server,GameServer' => '', '_customerid,CustomerId' => '', '_majorid,MajorId' => '', '_password,Password' => '', '_unit_key,Unit key' => '', '_vendor_key,Vendor key' => ''),
					'config_salon' => array('_url,API網址' => '', '_lobby_url,客戶端加載器' => '', '_lobby_code,大廳名稱' => '', '_secret_key,密鑰' => ''),
					'config_supersport' => array('_url,API網址' => '', '_agent,代理用戶名' => '', '_aes_key,AES key' => '', '_aes_iv,AES iv' => ''),
					'config_xinxin' => array('_url,API網址' => '', '_account_id,代理用戶名' => '', '_api_key,Api key' => ''),
					'config_ebet' => array('_url,API網址' => '', '_sub_channel_id,代理用戶ID' => ''),
					'config_dreamgame' => array('_url,API網址' => '', '_agent,代理用戶名' => '', '_api_key,Api key' => ''),
					'config_orientalgame' => array('_url,API網址' => '', '_cagent,代理用戶名' => '', '_des_key,DES key' => '', '_md5_key,MD5 key' => ''),
					'config_zhifubao' => array('_url,API網址' => '', '_cagent,代理用戶名' => '', '_des_key,DES key' => '', '_md5_key,MD5 key' => ''),
					'config_ninetynine' => array('_url,API網址' => '', '_station,站台代號' => ''),
					'config_globebet' => array('_url,API網址' => '', '_tpcode, TPCode' => '', '_generalkey, GeneralKey' => '', '_secretkey, SecretKey' => ''),
					'config_playstar' => array('_url,API網址' => '', '_host_id,HostID' => ''),
					'config_evoplay' => array('_url,API網址' => '', '_project_id,Project ID' => '', '_api_version,API Version' => '', '_secrete_key,Secrete Key' => ''),
					'config_booongo' => array('_url,API網址' => '', '_partner_id,代理用戶名' => '', '_secret,Secret Key' => '', '_vendor_key, Vendor Key' => ''),
				)
			)
		);
		
		$obj->decodeJson($_POST);
		
		if(!empty($obj->act)){
			//additional settings
			switch($obj->act){
				case 'review':
					if($_SESSION['user']['cross']){
						// pass
					}else{
						$obj->arg['where']['AND']['id'] = $_SESSION['user']['branch_id'];
					}
					break;
				default:
					break;
			}
			
			//do the work
			echo $obj->{$obj->act}($obj->arg);
		}else{
			$obj->render();
		}
		
		unset($obj);
		exit;
	}
}