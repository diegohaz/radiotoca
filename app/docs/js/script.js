$(function() {
var url = location.href.replace(new RegExp('^(' + index + '/?#?/?).*$'), '$1');
var current_process;
var has_link_process = false;
var url_changing_interval = 100;
var schedule_refresh_interval = 20000;

if (!$('body').hasClass('js')) {
	$('body').removeClass('nojs').addClass('js');
}

// Executa
init();
urlChanged();
refreshSchedule();

// Inicia
function init() {

	// Os bugs dos browsers
	if ($.browser.msie) {
		if ($.browser.version < 8) {
			$(':input').focus(function() {
				$(this).addClass('focus');
			}).blur(function() {
				$(this).removeClass('focus');
			});
		}

		$('.btn.disabled, .btn.disabled > *').css('opacity', 0.4);
	}
	else if ($.browser.opera) {
		$('.btn.disabled, .btn.disabled > *').css('opacity', 0.4);
	}

	// Alterna o texto da input
	$('input, textarea').bind('focus blur', function(evt) {
		var sample = $(this).attr('title');
		var value = $(this).val();

		switch (evt.type) {
			case 'focus':
			if (value == sample) {
				$(this).val('');
				$(this).removeClass('sample');
			}
			break;
			case 'blur':
			if (value == '') {
				$(this).val(sample);
			}
			else if (value != sample) {
				$(this).removeClass('sample');
			}

			if ($(this).val() == sample) {
				$(this).addClass('sample');
			}
			break;
		}
	});

	// Realiza a conversão dos links e atribui o evento click
	$('a[href^='+index+']').each(function(i, element) {
		$(element).attr('href', $(element).attr('href').replace(new RegExp('^' + index), index + '/#'));
	})
	.click(function(evt) {
		if (evt.ctrlKey) {
			return;
		}

		var href = $(this).attr('href');
		var element = this;
		url = href;
		current_process = href;
		has_link_process = true;

		$('body').css('cursor', 'wait');
		$(this).css('cursor', 'wait');

		$.getJSON(href.replace(index + '/#', index + '/ajax'), function(page) {
			$('body').css('cursor', 'auto');
			$(element).css('cursor', 'auto');

			if (current_process == href) {
				setPage(page);
			}

			has_link_process = false;
		});
	});

	// É necessário colocar os botões do formulário de contato em ajax
	if ($('#Contact').is('body')) {
		$('#contactForm button').click(function(evt) {
			evt.preventDefault();

			var url = $('#contactForm').attr('action').replace(new RegExp('^' + index), index + '/ajax');
			current_process = url;
			has_link_process = true;

			$('body').css('cursor', 'wait');
			$(evt.target).css('cursor', 'wait');

			$.getJSON(url, $('#contactForm').serialize() + '&' + evt.target.name + '=true',  function(page) {
				$('body').css('cursor', 'auto');
				$(evt.target).css('cursor', 'auto');

				if (current_process == url && page.title !== undefined) {
					setPage(page);
				}

				has_link_process = false;
			});
		});
	}

}

// Atribui os elementos à página
function setPage(page) {
	$('body').attr('id', page.id);
	$('#main').replaceWith(page.content);
	document.title = page.title;
	document.getElementById('ads').appendChild(ads);

	init();
}

// Dá uma olhada na url
function urlChanged() {
	setTimeout(function() {
		if (!has_link_process && location.href != url) {
			url = location.href;
			current_process = url;

			$.getJSON(url.replace(index + '/#', index + '/ajax'), function(page) {
				if (current_process == url && page.title !== undefined) {
					setPage(page);
				}
			});
		}

		urlChanged();
	}, url_changing_interval);
}

// Atualiza o módulo iSchedule
function refreshSchedule() {
	setTimeout(function() {
		$.getJSON(index + '/ajax/schedule', function(schedule) {
			if (schedule.content !== undefined) {
				if (!has_link_process) {
					$('#iSchedule .now').replaceWith(schedule.content);
				}

				$('#player .info div p').html(schedule.info);
			}
		});

		refreshSchedule();
	}, schedule_refresh_interval);
}

});