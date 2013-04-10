<h1>管理ページ</h1>

<table>
<tr>
<td>学部</td>
<td><?php echo $this->Html->link('新規登録', array('action' => 'form_d')); ?></td>
<td><?php echo $this->Html->link('編集', array('action' => 'form_d/modify')); ?></td>
<td><?php echo $this->Html->link('表示', array('action' => 'show')); ?></td>
</tr>
<tr>
<tr>
<td>学科</td>
<td><?php echo $this->Html->link('新規登録', array('action' => 'form_s')); ?></td>
<td colspan="2"><?php echo $this->Html->link('編集', array('action' => 'form_s/modify')); ?></td>
</tr>
<tr>
<td>データ登録</td>
<td><?php echo $this->Html->link('新規登録', array('action' => 'save')); ?></td>
<td colspan="2"><?php echo $this->Html->link('編集', array('action' => 'form_d/modify')); ?></td>
</tr>
<tr>
<td>教室</td>
<td><?php echo $this->Html->link('ユーザー登録', array('action' => 'form_c')); ?></td>
<td colspan="2"><?php echo $this->Html->link('表示', array('action' => 'show_c')); ?></td>
</tr>
<tr>
<td>テスト検索</td>
<td>
	<?php echo $this->Form->create(null, array('url' => array('controller' => 'admins', 'action' => 'search'))); ?>
	<?php 
	echo $this->Form->input('day', array('type' => 'select', 'options' => $day, 'label' => false, 'div' => false));
	?>曜日
</td>
<td>
	<?php
	echo $this->Form->input('period', array('type' => 'select', 'options' => $period, 'label' => false, 'div' => false));
	?>時限目
</td>
<td>
	<?php echo $this->Form->end('検索'); ?>
</td>
</tr>
</table>

