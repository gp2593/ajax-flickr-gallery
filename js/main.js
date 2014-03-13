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
		var img = new Image();
		$('#afg-photo-c').html('');
		$(img).attr('src', $(original).attr('src')).appendTo($('#afg-photo-c'));
		$('.afg-photo-loading').css('display', 'block');

		load_big(original, $('#afg-photo-c img'));
		$('#afg-photo-prev').css('display', 'block');
		$('#afg-photo-next').css('display', 'block');

		var data = {
			action: 'afg_get_photo',
			id: $(original).attr('id')
		};
		$.post(afg_ajax_url, data, function(res) {
		});
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
