<?php
/**
 *@数据报告model
 *@author yuzhou
 *@email ******
 *@date 2018-09-01
 */
class ReportModel extends CI_Model{
	public function __construct(){
		parent::__construct();
		$this->load->library("DbUtil");
	}

	/* 查询用户列表 */
	public function queryUserList(){
		$where = array(
			'select' => 'id,username',
			'where' => 'type="2"',
		);
		$res = $this->dbutil->getUser($where);
		if(!$res){
			return [];
		}
		$result['user'] = $res;
		return $result;
	}

	/* 查询游戏数据 */
	public function getGameData($arrParams){
		$totalWhere = array(
			'select' => 'count(*)',
			'where' => 'date >= '.$arrParams['startDate'].' AND date <= '.$arrParams['endDate'].' AND is_del = "2"',
		);

		$where = array(
			'select' => 'id,game_name,activate_num,price,money,date,account_id',
			'where' => 'date >= '.$arrParams['startDate'].' AND date <= '.$arrParams['endDate'].' AND is_del = "2"',
			'order_by' => 'create_time DESC',
			'limit' => ($arrParams['current']-1)*$arrParams['pageSize'].','.$arrParams['pageSize'],
		);
		
		if($arrParams['platform'] == 'client'){
			$totalWhere['where'] .= ' AND account_id = '.$_SESSION['account_id'];
			$where['where'].= ' AND account_id = '.$_SESSION['account_id'];
		}

		$total = $this->dbutil->getReport($totalWhere);
		if(!$total[0]['count(*)']){
			return [];
		}
		
		$res = $this->dbutil->getReport($where);
		$userList = $this->queryUserList()['user'];
		foreach($res as $key => $val){
			foreach($userList as $k => $v){
				if($val['account_id'] == $v['id']){
					$res[$key]['username'] = $v['username'];
				}
			}
		}
		if(!$res){
			return [];
		}

		return [
			'list' => $res,
			'pagination' => [
				'total' => (int)$total[0]['count(*)'],
				'pageSize' => (int)$arrParams['pageSize'],
				'current' => (int)$arrParams['current'],
				'startDate' => date("Y-m-d",$arrParams['startDate']),
				'endDate' => date("Y-m-d",$arrParams['endDate']),
			],
		];
	}

	/* 添加游戏数据 */
	public function addGameData($arrParams){
		$where = array(
			'game_name' => $arrParams['game_name'],
			'activate_num' => $arrParams['activate_num'],
			'price' => $arrParams['price'],
			'money' => $arrParams['activate_num'] * $arrParams['price'],
			'date' => strtotime($arrParams['date']),
			'account_id' => $arrParams['acc_id']
		);
		$res = $this->dbutil->setReport($where);
		if($res['code'] != 0){
			return false;
		}

		return true;
	}

	/* 修改游戏数据 只能修改activate_num、price的值 */
	public function udpGameData($arrParams){
		$where = array(
			'activate_num' => $arrParams['activate_num'],
			'price' => $arrParams['price'],
			'money' => $arrParams['activate_num'] * $arrParams['price'],
			'where' => 'id = '.$arrParams['gid'].' AND game_name = "'.$arrParams['game_name'].'" AND account_id = '.$arrParams['acc_id'],
		);
		$res = $this->dbutil->udpReport($where);
		if($res['code'] != 0){
			return false;
		}

		return true;
	}

	/* 删除游戏数据 改变is_del字段值 */
	public function delGameData($arrParams){
		$where = array(
			'is_del' => '1',
			'where' => 'id = '.$arrParams['gid'].' AND account_id = "'.$arrParams['accId'].'"',
		);

		$res = $this->dbutil->udpReport($where);
		if($res['code'] != 0){
			return false;
		}

		return true;
	}

	/* 查询单条游戏数据 */
	public function queryItemGameData($arrParams){
		$where = array(
			'select' => 'id,game_name,activate_num,price,money,date,account_id',
			'where' => 'id = '.$arrParams['gid'].' AND account_id = '.$arrParams['accId'],
		);

		$res = $this->dbutil->getReport($where);
		if(!$res){
			return [];
		}

		return [
			'modify' => $res[0],
			'user' => $this->queryUserList()['user'],
			'pagination' => [],
		];

	}
}
