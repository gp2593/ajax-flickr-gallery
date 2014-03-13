<?php
$photos = $this->input['photo'];
$lines = array();
$width = 930;
$line_height = 200;

$cur = array('width' => 0, 'height' => 0, 'photos' => array());
foreach ($photos as $photo) {
	if (count($cur['photos']) == 0) {
		$cur['photos'][] = $photo;
		$cur['height'] = $photo['height_z'] < $line_height ? $photo['height_z'] : $line_height;
		$cur['width'] = $cur['height'] * $photo['width_z'] / $photo['height_z'];
		continue;
	} else {
		$tmp_w = $cur['width'];
		$tmp_h = $cur['height'];
		$tmp_w += $cur['height'] * $photo['width_z'] / $photo['height_z'];
		if ($tmp_w < $width) {
			$cur['width'] = $tmp_w;
			$cur['photos'][] = $photo;
		} elseif ($tmp_w - $width < $width - $cur['width']) {
			$cur['height'] = $tmp_h * $width / $tmp_w;
			$cur['width'] = $width;
			$cur['photos'][] = $photo;
			$lines[] = $cur;
			$cur = array('width' => 0, 'height' => 0, 'photos' => array());
		} else {
			$cur['height'] = $cur['height'] * $width / $cur['width'];
			$cur['width'] = $width;
			$lines[] = $cur;
			$cur = array('width' => 0, 'height' => 0, 'photos' => array());
			$cur['photos'][] = $photo;
			$cur['height'] = $photo['height_z'] < $line_height ? $photo['height_z'] : $line_height;
			$cur['width'] = $cur['height'] * $photo['width_z'] / $photo['height_z'];
		}
	}
}


foreach ($lines as &$line) {
	$num = count($line['photos']) - 1;
	if ($num < 2)
		continue;
	$tmp_width = $line['width'] - $num * 5;
	$line['height'] = floor($tmp_width * $line['height'] / $line['width']);
	$line['width'] = $tmp_width + $num * 5;
	$total_width = 0.0;
	foreach($line['photos'] as &$photo) {
		$photo['width_z'] = round($photo['width_z'] * $line['height'] / $photo['height_z']);
		$photo['height_z'] = $line['height'];
		$total_width += $photo['width_z'] + 5;
	}
	$diff = floor($width - $total_width + 5);
	$x = floor($diff / ($num));
	$y = $diff % ($num);
	foreach($line['photos'] as &$photo) {
		if ($y > 0) {
			$photo['margin_z'] = 5 + $x + 1;
			$y --;
		} else {
			$photo['margin_z'] = 5 + $x;
		}
	}
	$line['photos'][$num]['margin_z'] = 0;
}
if (count($cur['photos']) > 0) {
	foreach($cur['photos'] as &$photo)
		$photo['margin_z'] = 5;
	$lines[] = $cur;
}
?>
<div id="afg-photo-set">
<?php foreach ($lines as &$line) : ?>
<div class="afg-line" style="width:<?= $width ?>px; height:<?= floor($line['height']) ?>px">
	<?php foreach($line['photos'] as &$photo) :
	?><span class="afg-photo"><img style="height:<?= floor($line['height']) ?>px; margin-right: <?= $photo['margin_z'] ?>px" src="<?= $photo['url_z'] ?>" id="<?= $photo['id'] ?>" alt="<?= $this->_get_photo_big_url($photo) ?>"/></span><?php
	endforeach; ?>
</div>
<?php endforeach; ?>
</div>
<div id="afg-photo-detail">
	<div id="afg-photo-big">
		<div id="afg-photo-container-wrapper">
			<div class="afg-photo-container"><div id="afg-photo-p" class="afg-photo-class" ></div></div>
			<div class="afg-photo-container"><div id="afg-photo-c" class="afg-photo-class" ></div></div>
			<div class="afg-photo-container"><div id="afg-photo-n" class="afg-photo-class" ></div></div>
		</div>
		<div class="afg-photo-loading"><img src="<?= plugin_dir_url(__FILE__) . "loading.gif" ?>" /></div>
		<div id="afg-photo-prev"></div>
		<div id="afg-photo-next"></div>
	</div>
	<div id="afg-photo-info">
		<div id="afg-photo-detail-close"><img src="<?= plugin_dir_url(__FILE__) . "close.png" ?>" /></div>
		<div clsss="dummy"></div>
	</div>
</div>
