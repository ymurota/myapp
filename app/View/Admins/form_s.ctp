<?php
echo $this->Form->create('Department', array('url' => array('controller' => 'admins', 'action' => 'form_s')));
?>

<?php
if (isset($data)) {
	foreach ($data as $k => $item) {
?>
		
<table>
<tr>
<th width="60%">学科名</th><th> POST ID</th>
</tr>
<tr>
<td><?php echo $this->Form->input("Depertment.$k.name", array('type' => 'text', 'label' => false)); ?></td>
<td><?php echo $this->Form->input("Depertment.$k.post_id", array('type' => 'text', 'label' => false)); ?> </td>
</tr>
</table>
		
<?php
	}
 } else {
?>

<?php
	echo $this->Form->input('parent_id', array('type' => 'select', 'options' => $select, 'label' => '学部'));
?>
	
<table>
<tr>
<th width="60%">学科名</th><th> POST ID</th>
</tr>
<tr>
<td><?php echo $this->Form->input('name', array('type' => 'text', 'label' => false)); ?></td>
<td><?php echo $this->Form->input('post_id', array('type' => 'text', 'label' => false)); ?> </td>
</tr>
</table>
	
<?php
 }
?>


<?php echo $this->Form->end('登録'); ?>