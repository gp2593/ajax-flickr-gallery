<?php
$sets = $this->input;
?>
<div id="afg-set-list">
<?php foreach ($sets as $set) : ?>
	<div class="afg-set">
		<span><img src="<?php echo $this->_get_set_primary_url($set); ?>" /></span>
		<div class="afg-info">
		<h4><?= mb_substr($set['title'], 0, 16) . (mb_strlen($set['title'], 'UTF-8') > 16 ? '...' : '') ?></h4>
		<div class="afg-meta"><?= $set['photos'] ?> Photo<?= $set['photos'] > 1 ? 's': '' ?></div>
		<div class="afg-desc"><?= mb_substr($set['description'], 0, 55) . (mb_strlen($set['description'], 'UTF-8') > 55 ? '...' : '') ?></div>
		</div>
	</div>
<?php endforeach; ?>
<div class="dummy"></div>
</div>
