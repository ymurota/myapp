<?php
App::uses('AppController', 'Controller');
App::uses('Hash', 'Utility');

class AdminsController extends AppController {
	public $helpers = array('Form', 'Html');
	public $uses = array('Department', 'KamokuData', 'AvailableClass');

	public function index() {
		$day = array(1 => "月", 2 => "火", 3 => "水", 4 => "木", 5 => "金", 6 => "土");
		$period = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7);
		$this->set("day", $day);
		$this->set("period", $period);
	}

	public function form_d($type="new") {
		if ($type == "new") {
		} else {
			$data = $this->Department->find('all', array('conditions' => array('Department.parent_id' => null)));
			$this->set('data', $data);
		}

		if (!empty($this->data)) {
			$data = $this->data;
			if ($this->Department->save($data)) {
				$this->redirect(array('action' => 'index'));
			} else {

			}
		}
	}

	public function form_s($type="new", $id = null) {
		$select = $this->Department->find('list', array('conditions' => array('Department.parent_id' => null), 'fields' => array('Department.name')));

		$this->set('select', $select);

		if ($type == "modify" && $id != null) {
			$data = $this->Department->find('all', array('condition' => array('Department.parent_id' => $id)));
			$this->set('data', $data);
		}
		
		if (!empty($this->data)) {
			$data = $this->data;
			if ($this->Department->save($data)) {
				$this->redirect(array('action' => 'index'));
			} else {

			}
		}
	}

	public function save() {
		$select = $this->Department->find('list', array('conditions' => array('Department.parent_id' => null)));
		$this->set('select', $select);

		if (!empty($this->data)) {
			$id = $this->data['Department']['id'];
			if ($this->KamokuData->updateByDeprt($id)) {
				$this->redirect(array('action' => 'index'));
			} else {
				trigger_error("すべてのデータが正常に保存されていない可能性があります。もう一度登録しなおして下さい。", E_USER_NOTICE);
			}
		}
	}

	public function show() {
		$this->autoRender = false;
		pr($this->Department->generateTreeList());
	}

	public function search() {
		if (!empty($this->data)) {
			$period = $this->data['Department']['period'];
			$day = $this->data['Department']['day'];

			/* 検索シークエンスはAvailableClassに記述 */
			$data = $this->KamokuData->find('all', array(
					'conditions' => array(
						'KamokuData.day' => $day,
						'KamokuData.period' => $period
					),
					'fields' => array('KamokuData.building', 'KamokuData.class'),
					'group' => array('KamokuData.building', 'KamokuData.class')
				));
			$class = $this->AvailableClass->find('all', array(
					'fields' => array('AvailableClass.building', 'AvailableClass.class')
				));

			$data = array_reduce($data, function($memo, $item){
	$memo[] = $item['KamokuData'];
	return $memo; }, array());
			$class = array_reduce($class, function($memo, $item){
	$memo[] = $item['AvailableClass'];
	return $memo; }, array());

			$availables = $this->KamokuData->diff($class, $data);
			$this->set("data", $availables);

		}
		
	}
	
	public function test() {
		$this->autoRender = false;
		//var_dump($this->KamokuData->update(262006,2601012));
	}
		
}

