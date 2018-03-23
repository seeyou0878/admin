<?php

namespace Front;

class Init
{
	function __construct()
	{
		$this->arr = \Config::get('branch');
		$this->_set_url();
		$this->_set_view();
		$this->_set_user();
		$this->_set_game();
		$this->_set_payment();
		
		\Config::set('branch', $this->arr);
	}

	private function _set_url()
	{
		$this->arr['url-teamviewer'] = 'http://download.teamviewer.com/download/TeamViewerQS_zhtw.exe';
	}

	private function _set_user()
	{
		if(\Session::get('auth')){
			$user = \DB::table('t_member')
				->select('t_member.*', 't_branch.name as branch', 't_branch.config_extra as config')
				->join('t_branch', 'branch_id', '=', 't_branch.id')
				->where('t_member.id', \Session::get('auth'))
				->first();
			$bank = \DB::table('t_member_bank')
				->select('t_member_bank.*', 't_bank.name')
				->join('t_bank', 'bank_id', '=', 't_bank.id')
				->where(['member_id' => \Session::get('auth'), 't_member_bank.status_id' => 1])
				->first();
			
			// user
			$this->arr['user-auth'] = \Config::get('xct.websocket') . '?auth=' . base64_encode(json_encode(['member_id' => $user->id, 'session_id' => \Session::getId()]));
			$this->arr['user-name'] = $user->name;
			$this->arr['user-account'] = $user->account;
			$this->arr['user-phone'] = $user->phone;
			$this->arr['user-valid'] = $user->valid_id;
			$this->arr['user-wallet'] = number_format($user->wallet);
			$this->arr['user-unread'] = (json_decode($user->notice)->unread ?? 0)?: '';
			
			// user bank account
			if($bank){
				$this->arr['user-bank'] = $bank->name;
				$this->arr['user-bank-account'] = $bank->account_no;
				$this->arr['user-bank-name'] = $bank->account_name;
			}
			
			// live
			$data = [
				'branch' => $user->branch,
				'account' => $user->account,
				'name' => $user->name,
				'phone' => $user->phone,
			];
			
			$newEncrypter = new \Illuminate\Encryption\Encrypter(base64_decode('KGN+j/LHOs+x/YfGy0CnI2v3cnvYLZ0f/nK+RPBdrQk='), 'AES-256-CBC');
			$data = $newEncrypter->encrypt(json_encode($data));
			$this->arr['url-live'] = (\Session::get('auth'))? 'http://live.jz88.tw/checkauth?data=' . $data: '';
		}
	}

	private function _set_view(){
		$agent = \DB::table('t_account')->where(['branch_id' => \Config::get('branch.branch_id'), 'account' => \Config::get('branch.agent')])->first();
		$master = \DB::table('t_account')->where(['branch_id' => \Config::get('branch.branch_id'), 'account_id' => ''])->first();
		if(!$agent){
			$agent = $master;
			// default agent for register
			$this->arr['agent'] = 'www';
		}
		
		// style
		$style_id = $agent->style_id?: $master->style_id;
		$style = \DB::table('t_style')->where('id', $style_id)->first();
		$this->arr['web-style'] = $style->library;
		
		// web base
		$this->arr['web-title'] = $agent->title?: $master->title;
		$this->arr['web-description'] = $agent->description?: $master->description;
		$this->arr['web-keywords'] = $agent->keyword?: $master->keyword;
		$this->arr['web-foot'] = $agent->foot?: $master->foot;
		$this->arr['web-script'] = $agent->script?: $master->script;
		$this->arr['url-chat'] = $agent->chat?: $master->chat; 
		
		$this->arr['background-color'] = $agent->color?: $master->color;
		
		
		function get_img($agent, $master = '[]'){
			$a = json_decode($agent, true);
			$m = json_decode($master, true);
			
			$arr = [];
			if(count($a)){
				foreach($a as $v){
					$arr[] = ['src' => $v['url']];
				}
			}else if(count($m)){
				foreach($m as $v){
					$arr[] = ['src' => $v['url']];
				}
			}
			
			return $arr;
		}
		
		// images
		$this->arr['background-image'] = [
			get_img($agent->index, $master->index)[0]['src'] ?? '',
			get_img($agent->sub, $master->sub)[0]['src'] ?? '',
		];
		$this->arr['web-logo'] = get_img($agent->logo, $master->logo)[0]['src'] ?? '';
		$this->arr['web-mlogo'] = get_img($agent->mlogo, $master->mlogo)[0]['src'] ?? '';
		$this->arr['web-qrcode-ico'] = get_img($agent->qrcode_icon, $master->qrcode_icon)[0]['src'] ?? '';
		$this->arr['web-qrcode'] = get_img($agent->qrcode, $master->qrcode) ?? [];
		$this->arr['web-banner'] = get_img($agent->banner, $master->banner) ?? [];
		
		$d = \DB::table('t_event')->where(['branch_id' => \Config::get('branch.branch_id'), 'status_id' => 1])->orderBy('id', 'DESC')->limit(5)->get()->toArray();
		$this->arr['web-event'] = array_map(function($v){
			$img = json_decode($v->icon, true)[0]['url'] ?? '';
			return ['src' => $img, 'content' => mb_substr(strip_tags($v->content), 0, 60)];
		}, $d);
		
		$d = \DB::table('t_bulletin')->where(['branch_id' => \Config::get('branch.branch_id'), 'status_id' => 1])->orderBy('id', 'DESC')->limit(5)->get()->toArray();
		$this->arr['web-bulletin'] = array_map(function($v){
			return ['title' => $v->title, 'content' => mb_substr(strip_tags($v->content), 0, 60)];
		}, $d);
		
	}

	private function _set_game()
	{
		$game = \DB::table('t_game_member_config')
			->select('t_game.*', 't_game_account.account', 't_game_account.password', 't_game_account.id as game_account_id')
			->join('t_game', 'game_id', '=', 't_game.id')
			->join('t_game_account', 't_game_account.id', '=', 't_game_member_config.game_account_id')
			->where('t_game_member_config.member_id', \Session::get('auth'))
			->where('t_game_member_config.status_id', 1)
			->where('t_game.status_id', 1)
			->get();
		
		$w = \DB::table('t_game')
			->where('t_game.game', 'Wallet')
			->first();
		
		// 電子錢包
		$this->arr['user-wgame'][] = ['id' => $w->id, 'name' => $w->name, 'game' => $w->game];
		
		foreach($game as $v){
			$tmp = ['id' => $v->id, 'name' => $v->name, 'game' => $v->game, 'account' => $v->account, 'password' => $v->password, 'game_account_id' => $v->game_account_id];
			$this->arr['user-wgame'][] = $tmp;
			$this->arr['user-game'][] = $tmp;
		}
	}
	
	private function _set_payment()
	{	
		// credit card flow
		$card = \DB::table('t_payment')
			->where('type', 2)
			->where('branch_id', \Config::get('branch.branch_id'))
			->inRandomOrder()->first()->config ?? '[]';
		$card = json_decode($card, true)['_percent'] ?? 0;
		$percent['CARD'] = $card;
		
		// limit min and max
		$data = \DB::table('t_branch')
			->where('id', \Config::get('branch.branch_id'))
			->first()->config_extra ?? '[]';
		$data = json_decode($data, true);
		
		$store_src = \DB::table('t_order_store_src')->get();
		$limit = [];
		foreach($store_src as $v){
			$type = $v->name;
			$type = ($type == 'IBON')? 'CVS': $type;
			
			$min = ($data['store_' . strtolower($type) . '_min'] ?? 0)?: 200;
			$max = ($data['store_' . strtolower($type) . '_max'] ?? 0)?: 20000;
			$min = ($min > $max)? $max: $min;
			
			$limit[$v->name] = '儲值限額: ' . $min . '-' . $max;
			
			if($type == 'ATM'){
				$limit[$v->name] = 'ATM不接受無卡無摺存款, ' . $limit[$v->name];
			}
			if($type == 'CVS'){
				$limit[$v->name] .= ($data['store_weekly']? ', 每週超商限額' . $data['store_weekly'] . '元': '');
			}
		}
		
		// payment
		$payment = \DB::table('t_order_store_src')->where('id', '!=', 1)->get();
		foreach($payment as $v){
			$this->arr['user-payment'][] = ['id' => $v->id, 'name' => $v->name, 'text' => $v->alias, 'percent' => ($percent[$v->name] ?? 0), 'note' => ($limit[$v->name] ?? '')];
		}
	}
}
