<?php

use yii\helpers\Url;
use yii\helpers\Html;


?>

<div class="container">
	<h3>Данные</h3>
	<?= Html::a("Добавить строку", '/add/'.$name) ?><br>
	<?php if (count($data)): ?>
		<form method="post" action="/table/<?=$name?>">
			<input type="hidden" name="_csrf">
			<input type="submit" value="Применить">
			<table class="table">
				<thead>
					<tr>
						<?php
						$colCount = count($data[0]);
						$columns = array_keys($data[0]); 
						foreach ($columns as $key => $value): ?>
							<?php if ($key >= $colCount - 2): ?>
								<th></th>
							<?php else: ?>
								<th scope="col"><?= $value ?><input type="checkbox" name="columns[<?= $value ?>]" <?= (in_array($value, $encrypted)?"checked":"")?>></th>
							<?php endif; ?>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($data as $key => $table): ?>
					<tr>
						<?php foreach ($table as $numb => $column): ?>
							<?php if (in_array($column, ['Редактировать', 'Удалить'])): ?>
								<?php if ($column == "Редактировать"): ?>
									<td><?= Html::a("Редактировать", '/read/'.$name.'/'.$table["id"]) ?></td>
								<?php else: ?>
									<td><?= Html::a("Удалить", '/delete/'.$name.'/'.$table["id"]) ?></td>
								<?php endif; ?>
							<?php else: ?>
								<td><?= $column ?></td>
							<?php endif; ?>
						<?php endforeach; ?>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</form>
	<?php else: ?>
		Список пуст
	<?php endif; ?>
</div>