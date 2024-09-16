<?= $this->partial('common/header') ?>
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= $this->csrf_token ?>">
	<h1>建立新會議</h1>
	<div class="mb-3">
		<label for="exampleFormControlInput1" class="form-label">會議名稱</label>
		<input name="name" type="text" class="form-control" id="exampleFormControlInput1" value="" required>
	</div>
	<div class="mb-3">
		<label for="exampleFormControlTextarea1" class="form-label">會議簡介</label>
		<textarea name="intro" class="form-control" id="exampleFormControlTextarea1" rows="3" required></textarea>
	</div>
	<div class="mb-3">
		<label for="exampleFormControlInput1" class="form-label">會議日期時間</label>
		<input name="time" type="text" class="form-control" id="exampleFormControlInput1" value="2024年7月28日13:00至17:00" required>
	</div>
	<div class="mb-3">
		<label for="exampleFormControlInput1" class="form-label">會議議程(請用換行分開)</label>
		<textarea name="agenda" class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
	</div>
	<button type="submit" class="btn btn-primary">建立會議</button>
</form>

<?= $this->partial('common/footer') ?>
