<?php
echo $this->Form->create(null, array('url' => array('controller' => 'admins', 'action' => 'save')));
?>

<?php
echo $this->Form->input('id', array('type' => 'select', 'options' => $select, 'label' => '学部'));
?>

<?php
echo $this->Form->end('登録開始');
?>

<?php
if (isset($data)) {
	pr($data);
 }
?>

