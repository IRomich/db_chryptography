<form method="post" action="/key">
	<input type="hidden" name="_csrf">
	Ключ шифрования<input name="key" value="<?= Yii::$app->session->get('key') ?>">
	<input type="submit" value="Сохранить">
</form>