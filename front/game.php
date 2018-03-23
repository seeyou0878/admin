<?php

namespace Front;

class Game{
	
	public function allbet()
	{
		$data = $this->login('Allbet')['data'];
		$tpl = new \Yatp('views/game/allbet.html');
		
		//dd($data);
		if(!($_GET['app'] ?? 0)){
			$tpl = $tpl->assign(array('app' => ''));
		}else{
			// change a numeric password
			$account = substr($data['gcg_account'], 0, -3);
			$password = rand(100000, 999999);
			$api = \App::make('Game\Allbet')->password($account, $password);
			if($api['code'] ?? 0){
				// fail
			}else{
				$data['gcg_passwd'] = $password;
			}
		}
		
		return $tpl->assign(array(
			'url' => $data['gameLoginUrl'],
			'account' => $data['gcg_account'],
			'password' => $data['gcg_passwd'],
		))->render(false);
	}

	public function comebets()
	{
		$data = $this->login('ComeBets')['data'];
		$tpl = new \Yatp('views/game/comebets.html');
		
		//dd($data);
		return $tpl->assign(array(
			'url' => $data['url'],
		))->render(false);
	}

	public function microsova()
	{
		$data = $this->login('Microsova')['data'];
		$tpl = new \Yatp('views/game/microsova.html');
		
		//dd($data);
		return $tpl->assign(array(
			'game_id' => $data['game_id'],
			'game_path' => $data['game_path'],
			'game_name' => $data['game_name'],
			'game_server' => $data['game_server'],
			'game_token' => $data['game_token'],
			'game_url' => 'MSGame://menu.com?CustomerID=' . $data['customer_id'] . '&Name=' . $data['account'] . '&Password=' . $data['password'],
		))->render(false);
	}

	public function supersport()
	{
		$data = $this->login('SuperSport')['data'];
		$tpl = new \Yatp('views/game/supersport.html');
		
		//dd($data);
		return $tpl->assign(array(
			'url' => $data['url'],
			'account' => $data['account'],
			'password' => $data['passwd'],
		))->render(false);
	}

	public function globalgaming()
	{
		$data = $this->login('GlobalGaming')['data'];
		$tpl = new \Yatp('views/game/globalgaming.html');
		
		//dd($data);
		return $tpl->assign(array(
			'url' => $data['url'],
			'gameId' => $data['gameId'],
		))->render(false);
	}

	public function salon()
	{
		$data = $this->login('Salon')['data'];
		$tpl = new \Yatp('views/game/salon.html');
		
		//dd($data);
		return $tpl->assign(array(
			'url' => $data['url'],
			'username' => $data['username'],
			'token' => $data['token'],
			'lobby' => $data['lobby'],
			'lang' => $data['lang'],
			'mobile' => $data['mobile'],
		))->render(false);
	}

	public function xinxin()
	{
		$data = $this->login('XinXin')['data'];
		$tpl = new \Yatp('views/game/xinxin.html');
		
		//dd($data);
		return $tpl->assign(array(
			'url' => $data['url'],
			'h5' => $data['h5'],
		))->render(false);
	}

	public function ebet()
	{
		$data = $this->login('Ebet')['data'];
		$tpl = new \Yatp('views/game/ebet.html');
		
		//dd($data);
		return $tpl->assign(array(
			'url' => $data,
		))->render(false);
	}

	public function bingo()
	{
		$this->login('Bingo');
	}

	public function goldenasia()
	{
		$this->login('GoldenAsia');
	}

	public function dreamgame()
	{
		$data = $this->login('DreamGame')['data'];
		$tpl = new \Yatp('views/game/dreamgame.html');
		
		return $tpl->assign(array(
			'url' => $data['list'][0] . $data['token'],
			'h5' => $data['list'][1] . $data['token'],
		))->render(false);
	}

	public function orientalgame()
	{
		$data = $this->login('OrientalGame')['data'];
		$tpl = new \Yatp('views/game/orientalgame.html');
		
		return $tpl->assign(array(
			'url' => $data,
		))->render(false);
	}

	public function zhifubao()
	{
		$data = $this->login('ZhiFuBao')['data'];
		$tpl = new \Yatp('views/game/zhifubao.html');
		
		return $tpl->assign(array(
			'url' => $data,
		))->render(false);
	}

	public function ninetynine()
	{
		$data = $this->login('NinetyNine')['data'];
		$tpl = new \Yatp('views/game/ninetynine.html');

		//dd($data);
		return $tpl->assign(array(
			'url' => $data['data'],
		))->render(false);
	}

	public function playstar()
	{
		$data = $this->login('PlayStar')['data'];
		$tpl = new \Yatp('views/game/playstar.html');

		 dd($data);
		return $tpl->assign(array(
			'url' => $data['url'],
			'gameId' => $data['gameId'],
		))->render(false);
	}

	public function globebet()
	{
		$data = $this->login('GlobeBet')['data'];
		$tpl = new \Yatp('views/game/globebet.html');

		// dd($data);
		return $tpl->assign(array(
			'url' => $data['url'],
			'h5' => $data['h5'],
			'gameId' => $data['gameId'],
		))->render(false);
	}

	public function evoplay()
	{
		$data = $this->login('EvoPlay')['data'];
		$tpl = new \Yatp('views/game/evoplay.html');

		// dd($data);
		return $tpl->assign(array(
			'url' => $data['url'],
			'gameId' => $data['gameId'],
		))->render(false);
	}

	public function booongo()
	{
		$data = $this->login('BooonGo')['data'];
		$tpl = new \Yatp('views/game/booongo.html');

		// dd($data);
		return $tpl->assign(array(
			'url' => $data['url'],
			'gameId' => $data['gameId'],
		))->render(false);
	}

	public function frsport()
	{
		$this->login('');
	}

	private function login($game)
	{
		$account = '';
		$password = '';
		
		$data = \Config::get('branch.user-game');
		foreach($data as $v){
			if($v['game'] == $game){
				$account = $v['account'];
				$password = $v['password'];
				break;
			}
		}
		
		if($account && $password){
			// laravel way
			$password = decrypt($password);
			$result = \App::make('\Game\\' . $game)->login_game($account, $password);
		}else{
			$result = ['code' => 1, 'text' => '遊戲維護中'];
		}
		
		if($result['code'] ?? 1){
			$tpl = new \Yatp('views/close.html');
			$tpl->render();
			exit;
		}else{
			// update login_time for Game\Account::get_recent()
			\DB::table('t_game_account')->where('id', $v['game_account_id'])->update(['login_time' => time()]);
			return $result;
		}
	}
}
