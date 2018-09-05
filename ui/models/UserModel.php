<?php
/**
 * @登录model
 */
class UserModel extends CI_Model{
	const EXPIRE_SESSION = 3600;

	public function __construct(){
		parent::__construct();
		session_start();
	}

	public function doLogin($arrParams){
		$where = array(
			'select' => '*',
			'where' => 'username="'.$arrParams['userName'].'" AND password="'.md5($arrParams['passWord']).'"',
		);

		if($arrParams['platform'] == 'client'){
			$where['where'] .= ' AND type = 2';
		}else{
			$where['where'] .= ' AND type = 1';
		}
		$this->load->library('DbUtil');
		$res = $this->dbutil->getUser($where);

		/*没有账号*/
		if(!$res){
			return [
				'status' => 'error',
				'type' => $arrParams['type'],
			];
		}

		if($arrParams['platform'] == 'client'){
			$_SESSION['login_time'] = time();
			$_SESSION['account_id'] = $res[0]['id'];
			$_SESSION['name'] = $res[0]['username'];
		}else{
			$_SESSION['bg_login_time'] = time();
			$_SESSION['bg_account_id'] = $res[0]['id'];
			$_SESSION['bg_name'] = $res[0]['username'];
		}

		/** 登录成功 */
		return [
			'status' => 'ok',
			'type' => $arrParams['type'],
			'currentAuthority' => (string)1,
			'name' => ($arrParams['platform'] == 'client') ? $_SESSION['name'] : $_SESSION['bg_name'],
			'avatar' => 'https://gw.alipayobjects.com/zos/rmsportal/BiazfanxmamNRoxxVxka.png',
		];
	}

	public function checkLogin($type){
		if($type == 'client'){
			if (isset($_SESSION['login_time'])
				&& isset($_SESSION['account_id'])
				&& isset($_SESSION['name'])
				&& (time() - $_SESSION['login_time']) <= self::EXPIRE_SESSION) {
				/* 更新session时间 */
				$_SESSION['login_time'] = time();

				return [
					'account_id' => $_SESSION['account_id'],
					'name' => $_SESSION['name'],
					'avatar' => 'https://gw.alipayobjects.com/zos/rmsportal/BiazfanxmamNRoxxVxka.png',
				];
			}
		
		}else{
			if (isset($_SESSION['bg_login_time'])
				&& isset($_SESSION['bg_account_id'])
				&& isset($_SESSION['bg_name'])
				&& (time() - $_SESSION['bg_login_time']) <= self::EXPIRE_SESSION) {
				/* 更新session时间 */
				$_SESSION['bg_login_time'] = time();

				return [
					'account_id' => $_SESSION['bg_account_id'],
					'name' => $_SESSION['bg_name'],
					'avatar' => 'https://gw.alipayobjects.com/zos/rmsportal/BiazfanxmamNRoxxVxka.png',
				];
			}
		
		}
		return [];
	}

	public function clearLogin(){
		setcookie('PHPSESSID', '', time()-1, '/');
		$_SESSION = [];
		return true;
	}
}
?>
