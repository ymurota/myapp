<?php
App::uses('AppController', 'Controller');
App::uses('Hash', 'Utility');

class AdminsController extends AppController {
	public $helpers = array('Form', 'Html');
	public $uses = array('Department', 'KamokuData');

	public function index() {
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
		$select = $this->Department->find('list', array('conditions' => array('Department.parent_id' => null), 'fields' => array('Department.post_id', 'Department.name')));
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
}

