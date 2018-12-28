<?php
use yii\helpers\Html;
?>
<div class="container">
	<?= Html::a("Назад", "/table/$name") ?>
	<form method="post" action="/read/<?=$name?>/<?=$id?>">
		<input type="hidden" name="_csrf">
		<input type="submit" value="Применить">
		<table class="table">
			<thead>
				<tr>
					<?php
					$colCount = count($data);
					$columns = array_keys($data); 
					foreach ($columns as $key => $value): ?>
						<th scope="col"><?= $value ?><input type="checkbox" name="columns[<?= $value ?>]" <?= (in_array($value, $encrypted)?"checked":"")?>></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<?php foreach ($data as $numb => $column): ?>
						<td><input name="<?=$numb?>" value="<?= $column ?>"></td>
					<?php endforeach; ?>
				</tr>
			</tbody>
		</table>
	</form>
</div