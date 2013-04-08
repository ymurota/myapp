<?php
if (isset($data)) {
?>
<table>
<tr><th>号館</th><th>教室</th></tr>
<?php
	foreach ($data as $val) {
?>
<tr>		
<td><?php echo $val['building']; ?></td>
<td><?php echo $val['class']; ?></td>
</tr>		
<?php	
	}
?>
</table>
<?php	
 }
?>