<?php echo $this->Form->create('Department', array('url' => array('controller' => 'admins', 'action' => 'form_d'))); ?>

<?php
if (!empty($data)) {
	foreach ($data as $k => $item) {
?>
<table>
<tr>
<th width="60%">学部名</th><th>POST　ID</th>
</tr>
<tr>
<td><?php echo $this->Form->input("Department.$k.name", array('value' => $item['Department']['name'], 'label' => false)); ?></td>
<td><?php echo $this->Form->input("Department.$k.post_id", array('type' => 'text', 'value' => $item['Department']['post_id'], 'label' => false)); ?></td>
</tr>
</table>
<?php
	}
 } else {
	
?>
<table>
<tr>
<th width="60%">学部名</th><th>POST　ID</th>
</tr>
<tr>
<td><?php echo $this->Form->input('name', array('label' => false)); ?></td>
<td><?php echo $this->Form->input('post_id', array('type' => 'text', 'label' => false)); ?></td>
</tr>
</table>
<?php
 }
?>
<?php echo $this->Form->end('登録'); ?>