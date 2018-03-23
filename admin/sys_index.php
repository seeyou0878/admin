<?php
namespace Admin;

class Index{
	
	function __construct(){
		
		$tpl = new \Yatp(__DIR__ . '/admin.tpl');
		
		$html = $tpl->block('index')->assign(array('title' => \Box::val('title')));
		$lang = $tpl->block('lang')->assign(array('option' => $tpl->block('lang.option')->nest(\Box::obj('Lang')->get())));
		
		$branch = \Box::obj('db')->select('t_branch', 'notice', ['id'=>$_SESSION['user']['branch_id'] ?? 0]);
		$notice = json_decode($branch[0] ?? '', true);
		
		if(isset($_SESSION['auth']) && isset($_SESSION['user'])){
			$html->assign(
				array(
					'header' => '',
					'nav'    => $tpl->block('nav')->assign(
						array(
							'user' => $_SESSION['user']['account'],
							'brand'=> \Box::val('brand'),
							'submenu' => $this->getSubMenu(\Box::val('nav')),
							'lang' => $lang,
							'notice' => $tpl->block('notice')->assign(array(
								'hidden' => ($_SESSION['auth']['order_review'] ?? 0)? '': 'hidden',
								'store' => ($notice['store'] ?? 0) != 0? $notice['store']: '',
								'transfer' => ($notice['transfer'] ?? 0) != 0? $notice['transfer']: '',
								'withdraw' => ($notice['withdraw'] ?? 0) != 0? $notice['withdraw']: '',
								'unread' => ($notice['unread'] ?? 0) != 0? $notice['unread']: '',
								'auth' =>  \Config::get('xct.websocket') . '?auth=' . base64_encode(json_encode(['branch_id' => $_SESSION['user']['branch_id']])),
							)),
						)
					),
					'main' => $tpl->block('intro'),
				)
			)->render();
			
		}else{
			$html->assign(
				array(
					'main' => '',
					'nav'    => '',
					'header'   => $tpl->block('login')->assign(
						array(
							'title' => \Box::val('title'),
							'brand' => \Box::val('brand'),
							'lang'  => $lang,
						)
					),
					'footer' => '',
					
				)
			)->render();
		}
		
		exit;
	}
	
	// bootstrap nav
	function getSubMenu($list, $sub=0){
		$html = '';
		
		$tpl = new \Yatp(__DIR__ . '/admin.tpl');
		$arr = [];
		foreach($list as $item){
			// skip first
			if($sub == 1){
				if($this->authCheck($item)){
					$sub = 2;
					continue;
				}else{
					break;
				}
			}
			
			if(is_array($item[0] ?? '')){
				// sub-menu case
				$menu = $this->getSubMenu($item, 1);
				if($menu){
					$arr[] = ['submenu-li' => $menu];
				}
				
			}else{
				if($this->authCheck($item)){
					$arr[] = [
						'submenu-li' => $tpl->block('submenu-li')->assign([
							'link' => ($item[1] ?? ''),
							'name' => ($item[0] ?? ''),
						])
					];
				}
			}
		}
		
		$html = $tpl->block('submenu-li')->nest($arr)->render(false);
		$cnt = count($arr);
		if($sub && $cnt){
			$html = $tpl->block('submenu')->assign([
				'name' => ($list[0][0] ?? ''),
				'submenu-li' => $html,
			])->render(false);
		}else if($sub && $cnt==0){
			// remove sub-menu
			$html = '';
		}
		
		return $html;
	}
	
	function authCheck($item, $offset=2){
		$auth = preg_split('/[\s|]+/', $item[$offset]);
		$pass = false;
		foreach($auth as $v){
			$pass = $pass || ($_SESSION['auth'][$v] ?? 0);
		}
		
		return ($item[$offset]=='') || $pass;
	}
}