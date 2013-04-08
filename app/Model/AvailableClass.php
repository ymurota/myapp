<?php
/* ---------------------- */
/*
/* ToDo:ユーザー編集できるようにする。名称とか。
/*      => こっちで編集したらKamokuDataとも同期
/*
/* ---------------------- */

App::uses('AppModel', 'Model');

class AvailableClass extends AppModel {
	public $name = 'AvailableClass';

	/* KamokuDataが更新された後そのデータを使ってclassデータを更新 */
	public function updateClass() {
		$KamokuData = ClassRegistry::init("KamokuData");
		$yData = $KamokuData->find('all', array(
				'fields' => array('KamokuData.building', 'KamokuData.class'),
				'group' => array('KamokuData.building', 'KamokuData.class')
			));

		$dData = $this->find('all', array(
				'fields' => array('AvailableClass.building', 'AvailableClass.class'),
			));
		
		$yData = array_reduce($yData, function($memo, $item){
 $memo[] = $item['KamokuData'];
 return $memo; }, array());
		$dData = array_reduce($dData, function($memo, $item){
 $memo[] = $item['AvailableClass'];
 return $memo; }, array());

		// diffメソッドをAppModelに書こうかしら。
		$diff = $KamokuData->diff($yData, $dData);

		if (empty($diff)) return true;
		return $this->saveAll($diff);
	}
}