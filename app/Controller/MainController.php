<?php
App::uses('AppController', 'Controller');

class MainController extends AppController {
	public $uses = array('KamokuData');
	
	public function index() {
		$this->autoRender = false;

		$time_start = microtime(true);
		$http = $this->KamokuData->getHTTP();
		$data = $this->KamokuData->fetch();
		$time_end = microtime(true);
		$time = $time_end - $time_start;

		$data = $this->KamokuData->parseData($data);

		echo "処理時間：".$time;
		pr($data);

		//echo "データ数：".count($data)."<br/>";

}


?>