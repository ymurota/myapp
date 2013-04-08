<?php
App::uses('AppModel', 'Model');

class Department extends AppModel {
	public $name = 'Department';

	public $actsAs = array('Tree');
}