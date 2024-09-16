<?= $this->partial('common/header') ?>
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= $this->csrf_token ?>">
	<h1>編輯會議</h1>
	<div class="mb-3">
		<label for="exampleFormControlInput1" class="form-label">會議名稱</label>
        <input name="name" type="text" class="form-control" id="exampleFormControlInput1" value="<?= $this->escape($this->meeting->d('name')) ?>" required>
	</div>
	<div class="mb-3">
		<label for="exampleFormControlTextarea1" class="form-label">會議簡介</label>
        <textarea name="intro" class="form-control" id="exampleFormControlTextarea1" rows="3" required><?= $this->escape($this->meeting->d('intro')) ?></textarea>
	</div>
	<div class="mb-3">
		<label for="exampleFormControlInput1" class="form-label">會議日期時間</label>
        <input name="time" type="text" class="form-control" id="exampleFormControlInput1" value="<?= $this->escape($this->meeting->d('time')) ?>" required>
	</div>
	<div class="mb-3">
		<label for="exampleFormControlInput1" class="form-label">會議議程(請用換行分開)</label>
        <textarea name="agenda" class="form-control" id="exampleFormControlTextarea1" rows="3"><?= $this->escape($this->meeting->d('agenda')) ?></textarea>
	</div>
	<button type="submit" class="btn btn-primary">編輯</button>
</form>
<?= $this->partial('common/footer') ?>
