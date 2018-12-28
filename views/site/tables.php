<?php
use yii\helpers\Url;
use yii\helpers\Html;

?>

<div class="container">
	<h3>Список таблиц</h3>
	<?php if (count($data)): ?>
		<table class="table">
			<thead>
				<tr>
					<th scope="col">#</th>
					<th scope="col">Название таблицы</th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data as $key => $table): ?>
				<tr>
					<td><?= $key + 1 ?></td>
					<td><a href="<?= Url::to(['table', 'name' => $table['TABLE_NAME']])?>"><?= $table['TABLE_NAME'] ?></a></td>
					<td><?= Html::a("Удалить", '/deletetable/'.$table['TABLE_NAME'])?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else: ?>
		Список пуст
	<?php endif; ?>
</div>