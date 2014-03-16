jQuery(document).ready(function() {
	var cur = null;
	function pick_next(original) {
		parent_next = $(original).parent().next();
		if (parent_next.length == 0) {
			if ($(original).parent().parent().next().length == 0) {
				return false;
			}
			return $(original).parent().parent().next().children().first().children().first();
		} else {
			return parent_next.children().first();
		}
	}
	function pick_prev(original) {
		parent_prev = $(original).parent().prev();
		if (parent_prev.length == 0) {
			if ($(original).parent().parent().prev().length == 0) {
				return false;
			}
			return $(original).parent().parent().prev().children().last().children().first();
		} else {
			return parent_prev.children().first();
		}
	}
	function prev_img(original) {
		source = pick_prev(original);
		if (source == false) {
			$('#afg-photo-prev').css('display', 'block');
			$('#afg-photo-next').css('display', 'block');
		}
		cur = source.get();
		small_src = source.attr('src');
		big_src = source.attr('alt');
		pic_id = source.attr('id');
		get_photo_info(pic_id);

		var img = new Image();
		$(img).attr('src', small_src).appendTo($('#afg-photo-p'));
		$('.afg-photo-container').animate({
			left: '+=25%'}, 300, function() {
				$('#afg-photo-c img').replaceWith(img);
				$('.afg-photo-loading').css('display', 'block');
				$('.afg-photo-container').css('left', 0);
				load_big(source.get(), $('#afg-photo-c img'));
				$('#afg-photo-prev').css('display', 'block');
				$('#afg-photo-next').css('display', 'block');
			});
	}
	function next_img(original) {
		source = pick_next(original);
		if (source == false) {
			$('#afg-photo-prev').css('display', 'block');
			$('#afg-photo-next').css('display', 'block');
		}
		cur = source.get();
		small_src = source.attr('src');
		big_src = source.attr('alt');
		pic_id = source.attr('id');
		get_photo_info(pic_id);

		var img = new Image();
		$(img).attr('src', small_src).appendTo($('#afg-photo-n'));
		$('.afg-photo-container').animate({
			left: '-=25%'}, 300, function() {
				$('#afg-photo-c img').replaceWith(img);
				$('.afg-photo-loading').css('display', 'block');
				$('.afg-photo-container').css('left', 0);
				load_big(source.get(), $('#afg-photo-c img'));
				$('#afg-photo-prev').css('display', 'block');
				$('#afg-photo-next').css('display', 'block');
			});
	}
	function replace_img(original) {
		cur = original;
		get_photo_info($(original).attr('id'));
		var img = new Image();
		$('#afg-photo-c').html('');
		$(img).attr('src', $(original).attr('src')).appendTo($('#afg-photo-c'));
		$('.afg-photo-loading').css('display', 'block');

		load_big(original, $('#afg-photo-c img'));
		$('#afg-photo-prev').css('display', 'block');
		$('#afg-photo-next').css('display', 'block');

	}
	function load_big(original, to_replace) {
		var big_img = new Image();
		$(big_img).load(function(e) {
			var ph = $('#afg-photo-c').height();
			var pw = $('#afg-photo-c').width();
			var mh = this.naturalHeight;
			var mw = this.naturalWidth;
			$('.afg-photo-loading').css('display', 'none');
			$(this).css('max-height', $(window).height());
			$(this).css('height', $('#afg-photo-c img').height());
			to_replace.replaceWith(this);
			var nh, nw;
			if (mh / mw > ph / pw) {
				nh = mh < ph ? mh : ph;
				nw = nh * mw / mh;
			} else {
				nw = mw < pw ? mw : pw;
				nh = nw * mh / mw;
			}
			$(this).animate({
				height: nh,
				width: nw,
			}, {
				duration: 800,
				complete: function () {
					$(this).css('height', 'auto');
					$(this).css('width', 'auto');
				}
			});
		});
		$(big_img).attr('src', $(original).attr('alt'));
	}
	function fatal_error(msg) {
		$('#afg-photo-info-errmsg').html('unexpected error: ' + msg).fadeIn();
	}
	function ajax_check(res) {
		if (typeof res['data'] != 'object')
			return false;

		if (typeof res['data']['meta'] != 'object')
			return false;
		if (typeof res['data']['meta']['photo'] != 'object')
			return false;
		if (typeof res['data']['meta']['photo']['id'] == 'undefined')
			return false;

		if (typeof res['data']['exif'] != 'object')
			return false;

		return true;
	}
	function clear_photo_info_loading() {
		$('#afg-photo-info-errmsg').html('Loading...').fadeIn();
		$('#afg-photo-info-meta').html('');
		$('#afg-photo-info-user').html('');
		$('#afg-photo-info-exif').html('');
	}
	function get_photo_info(id) {
		clear_photo_info_loading();
		var data = {
			action: 'afg_get_photo',
			id: id
		};
		$.post(afg_ajax_url, data, function(res) {
			if (res['errno'] != 0) {
				if (typeof res['data'] == 'string')
					fatal_error(res['data']);
				else
					fatal_error('wild return data');
			} else if (!ajax_check(res)) {
				fatal_error('wild return data');
			} else {
				if (res['data']['meta']['photo']['id'] != $(cur).attr('id'))
					return;
				$('#afg-photo-info-errmsg').html('').hide();
				put_meta_info(res['data']['meta']['photo']);
				put_exif_info(res['data']['exif']);
			}
		}, 'json').fail(function() {
			$('#afg-photo-info-errmsg').html('server error');
		});
	}
	function put_exif_info(exif) {
		ret = "<div>";
		for (i = 0; i < exif['exif'].length; i ++) {
			if (typeof exif['exif'][i]['tag'] == 'string') {
				if (exif['exif'][i]['tag'] == 'Make') {
					ret += "Make: " + exif['exif'][i]['raw'] + "<br />";
				} else if (exif['exif'][i]['tag'] == 'Model') {
					ret += "Model: " + exif['exif'][i]['raw'] + "<br />";
				} else if (exif['exif'][i]['tag'] == 'Software') {
					ret += "Software: " + exif['exif'][i]['raw'] + "<br />";
				} else if (exif['exif'][i]['tag'] == 'ExposureTime') {
					ret += "Exposure: " + exif['exif'][i]['raw'] + "<br />";
				} else if (exif['exif'][i]['tag'] == 'FNumber') {
					ret += "Aperture: " + exif['exif'][i]['raw'] + "<br />";
				} else if (exif['exif'][i]['tag'] == 'ExposureProgram'){
					ret += "Exposure Program: " + exif['exif'][i]['raw'] + "<br />";
				} else if (exif['exif'][i]['tag'] == 'ISO') {
					ret += "ISO: " + exif['exif'][i]['raw'] + "<br />";
				}
			}
		}
		ret += "</div>";
		$('#afg-photo-info-exif').html(ret);
	}
	function put_meta_info(meta) {
		ret = "<header>";
		if (typeof meta['title'] == 'string')
			ret += "<h2>" + meta['title'] + "</h2>";
		else
			ret += "<h2>" + "No Title" + "</h2>";
		if (typeof meta['description'] == 'string' && meta['description'] != '')
			ret += "<p>" + meta['title'] + "</p>";

		ret += "<div>";
		if (typeof meta['views'] == 'string') {
			ret += "<span><img src=\"" + afg_img_base + "eye_icon.png\" />";
			ret += meta['views'] + "</span>";
		}
		if (typeof meta['dates'] == 'object' &&
				typeof meta['dates']['taken'] == 'string') {
			ret += "<span><img src=\"" + afg_img_base + "calendar_1_icon.png\" />";
			d = new Date(meta['dates']['taken']);
			ret += d.getFullYear() + '-' + (1 + d.getMonth()) + '-'
				+ d.getDate() + "</span>";
		}
		ret += "</div>";
		ret += "</header>";
		$('#afg-photo-info-meta').html(ret);
	}
	$('.afg-photo img').click(function(e) {
		ori = this;
		$('#afg-photo-detail').hide().fadeIn(400);
		replace_img(ori);
	});
	$('#afg-photo-detail-close').click(function(e) {
		$('#afg-photo-detail').css('display', 'none');
	});
	$('#afg-photo-next').click(function(e) {
		$('#afg-photo-next').css('display', 'none');
		next_img(cur);
	});
	$('#afg-photo-prev').click(function(e) {
		$('#afg-photo-prev').css('display', 'none');
		prev_img(cur);
	});
});
