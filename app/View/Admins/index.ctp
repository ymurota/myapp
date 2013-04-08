<h1>管理ページ</h1>

<table>
<tr>
<td>学部</td>
<td><? echo $this->Html->link('新規登録', array('action' => 'form_d')); ?></td>
<td><? echo $this->Html->link('編集', array('action' => 'form_d/modify')); ?></td>
<td><? echo $this->Html->link('表示', array('action' => 'show')); ?></td>
</tr>
<tr>
<tr>
<td>学科</td>
<td><? echo $this->Html->link('新規登録', array('action' => 'form_s')); ?></td>
<td colspan="2"><? echo $this->Html->link('編集', array('action' => 'form_s/modify')); ?></td>
</tr>

<td>データ登録</td>
<td><? echo $this->Html->link('新規登録', array('action' => 'save')); ?></td>
<td colspan="2"><? echo $this->Html->link('編集', array('action' => 'form_d/modify')); ?></td>
</tr>
</table>

