<?php
namespace Admin;

class Api{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_branch',
			/*col*/
			array(
				'id',
				'name', 'domain', 'receive_id', 'limit',
				'config_extra', 'config_sms'
			),
			/*col_ch*/
			array(
				'id',
				'分站名稱',
				'分站網域,多組換行',
				'指定收款,收款設定優先順序:<br>1.會員設定<br>2.分站設定<br>3.金流設定',
				'註冊限制,手機可以註冊至多 N 個帳號<br>( 0 = 不限制)<br>此變更不溯及既往的帳號設定',
				'訂單設定,手續費計算方式<br>(原始金額 x 百分比)<br> + 固定値',
				'台灣簡訊',
			),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array(
				'', '', '', 
				($_SESSION['user']['cross'])? 't_receive,title,id': 't_receive,title,id,{"branch_id": ' . $_SESSION['user']['branch_id'] . '}', 
				'', '', '',
			),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'col-md-2 col-sm-2 col-xs-4',
				'col-md-8 col-sm-8 col-xs-4',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array('hidden', 'text,{"disabled": true}', 'text,{"disabled": true}', 'select', 'text', 'json', 'json'),
			/*authority check*/
			array(
				$_SESSION['auth']['branch_api'] ?? 0,
				0,
				$_SESSION['auth']['branch_api'] ?? 0,
				0,
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
					'config_sms' => array('Account' => '', 'Password' => ''),
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