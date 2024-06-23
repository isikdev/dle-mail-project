(function(custom, $){

	function getTemplate(name, data) {
		var htmlspecialchars = function(text) {
			var map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return text.toString().replace(/[&<>"']/g, function(m) {
				return map[m];
			});
		}
		var html = $('#' + name).html().trim();
		if (!!data) {
			html = html.replace(/{value\.(.+)}/gi, function (t,v) {
				if (typeof data[v] == 'undefined') return '';
				return htmlspecialchars(data[v]);
			});
			html = html.replace(/{checkbox\.(.+)}/gi, function (t,v) {
				if (typeof data[v] == 'undefined' || !data[v]) return '';
				return 'checked';
			});
			html = html.replace(/{radio\.(.+)=(.*?)}/gi, function (t,v,s) {
				if (typeof data[v] == 'undefined') {
					return s === '' ? 'checked' : '';
				}
				return data[v] == s ? 'checked' : '';
			});
			html = html.replace(/{select\.(.+)=(.+?)}/gi, function (t,v,s) {
				if (typeof data[v] == 'undefined') return '';
				return data[v] == s ? 'selected' : '';
			});
		}
		return html;
	}

	function sendAjax(action, data, callback) {
		data = data || {};
		data.ajax = true;
		data.action = action;
		ShowLoading('');
		$.ajax({
			url: '',
			type: 'POST',
			dataType: 'json',
			data: data,
		})
		.done(function(d) {
			callback&&callback(d);
		})
		.fail(function(e) {
			DLEalert(e.responseText, mod_lang.info);
		})
		.always(function() {
			HideLoading();
		});		
	}

	var subinfo = false;
	custom.showSubInfo = function() {
		subinfo = !subinfo;
		if (subinfo == true) $('.mod-item-subrow').css('display', 'flex');
		else $('.mod-item-subrow').hide();
		return!1;
	}

	//Закрыть настройки
	custom.hideSetting = function() {
		$('#settingsWrapper').slideUp(150, function(){$(this).remove()});
		return!1;
	}

	//Показать настройки
	custom.showSettings = function() {
		if ($('#settingsWrapper').length) {
			return custom.hideSetting();
		}
		sendAjax('showConfig', {}, function(d){
			var html = getTemplate('settingsTemplate', d);
			$(html).hide().prependTo('.mod-content').slideDown(150);
		});
		return !1;
	}

	//Сохранить настройки
	custom.saveConfig = function(form, close) {
		var data = {};
		for (var v of $(form).serializeArray()) {
			data[v.name] = v.value;
		}
		sendAjax('saveConfig', data, function(d){
			ModAlert.addAlert({
				type: 'success',
				text: mod_lang.config_ok
			});

			mod_config = d;

			for (var n in mod_data) {
				custom.printItem(n);
			}
			close&&custom.hideSetting();
		});
		return!1;
	}

	function loadBlockHtml(d, inner) {
		var html = getTemplate('editItemTemplate', d);
		html = html.replace(/{export}/gi, JSON.stringify(d));
		html = html.replace(/{name}/gi, d.name);
		if (inner) html = $(html).html();
		return html;
	}

	//Показать настройки блока/группы
	custom.showBlockConfig = function(name) {
		$('#editItemForm').dialog('close').remove();
		sendAjax('openEdit', {name: name}, function(d){
			var html = loadBlockHtml(d);
			var width = Math.floor(window.innerWidth * 0.6);
			var height = Math.floor(window.innerHeight * 0.7);
			if (width < 1000) width = 1000;
			if (height < 600) height = 600;
			$(html).dialog({
				width: width,
				height: height,
				title: 'Редактирование блока',
				buttons: {
					'Применить': function(){
						custom.saveBlock($(this), function(d){
							name = d.name;
							$('#editItemForm').html(loadBlockHtml(d, true));
						});
					},
					'Сохранить и закрыть': function(){
						custom.saveBlock($(this), function(d){
							$('#editItemForm').dialog('close');
						});
					},
					'Удалить': function(){
						if (custom.deleteBlock(name)) {
							$(this).dialog('close');
						}
					},
					'Закрыть': function(){
						$(this).dialog('close');
					}
				}
			});
		})
		return!1;
	}

	//Сохранить настройки блока/группы
	custom.saveBlock = function(form, callback) {
		var data = {};
		for (var v of form.serializeArray()) {
			data[v.name] = v.value;
		}
		sendAjax('saveBlock', data, function(d){
			ModAlert.addAlert({
				type: 'success',
				text: mod_lang.block_ok
			});
			if (d.new_name) {
				$('#mod-item--' + data.name).attr('id', 'mod-item--' + d.new_name).data('name', d.new_name)
				delete mod_data[data.name];
				data.name = d.new_name;
			}
			mod_data[data.name] = d;
			custom.printItem(data.name);
			callback&&callback(d);
		});
		return!1;
	}

	//Закрыть настройки блока/группы
	custom.closeBlockConfig = function(form) {
		$(form).slideUp(200, function(){$(this).remove()});
		return!1;
	}

	//Закрыть форму добавления блока
	custom.closeAddBlock = function() {
		$('#addBlockWrapper').slideUp(150, function(){$(this).remove()});
		return!1;
	}

	//Открыть форму добавления блока
	custom.addBlock = function() {
		if ($('#addBlockWrapper').length) {
			return custom.closeAddBlock();
		}
		var html = getTemplate('addItemTemplate');
		$(html).hide().prependTo('.mod-content').slideDown(150);
		$('#addBlockWrapper input:first-child').focus();
		return!1;
	}

	//Создать блок
	custom.doAddBlock = function(form) {
		var data = {};
		for (var v of $(form).serializeArray()) {
			data[v.name] = v.value;
		}

		sendAjax('doAddBlock', data, function(d){

			if (mod_data[d.name]) {
				ModAlert.addAlert({
					type: 'error',
					text: mod_lang.blockTitle_error
				});
				return!1;
			}

			ModAlert.addAlert({
				type: 'success',
				text: mod_lang.block_ok
			});

			mod_data[d.name] = d;

			var li = $('<li></li>');
			li.attr({
				id: 'mod-item--' + d.name,
				class: 'dd-item mod-item disabled',
			}).data('name', d.name);
			li.appendTo('#mod-items');
			custom.closeAddBlock();
			custom.printItem(d.name);
			custom.showBlockConfig(d.name);
		});

		return!1;
	}

	//Удалить блок
	custom.deleteBlock = function(name) {
		if (confirm(mod_lang.confirm_del + name)) {
			sendAjax('deleteBlock', {name: name}, function(d){
				ModAlert.addAlert({
					type: 'success',
					text: mod_lang.blockDel_ok
				});
				$('#mod-item--' + name).slideUp(300,function(){$(this).remove()});
				mod_data[name] = null;
			});
			return true;
		}
		return!1;
	}

	//Сохранить очередность блоков
	custom.changeSort = function(data) {
		sendAjax('changeSort', {sort: data});
	}

	//Отобразить элемент
	custom.printItem = function(name) {
		var html = getTemplate('itemTemplate', mod_data[name]);
		html = html.replace(/{name}/gi, name);
		html = html.replace(/{active\.(.+?)}/gi, function(m, v){
			return mod_data[name][v] || mod_config[v] ? 'active' : '';
		});
		html = html.replace(/{local\.(.+?)}/gi, function(m, v){
			if (typeof mod_data[name][v] == 'undefined' || mod_data[name][v] === '') return '';
			return mod_data[name][v] ? 'local-active' : 'local-disabled';
		});
		var item = $('#mod-item--' + name);
		item.html(html);
		if (mod_data[name].active != true) item.addClass('disabled');
		else item.removeClass('disabled');
		subinfo&&item.find('.mod-item-subrow').css('display', 'flex');
	}

	//Очистить кеш шаблонных блоков
	custom.clearCache = function() {
		if (confirm('Clear module cache?')) {
			sendAjax('clearCache',{}, function() {
				$('#cacheSize').html('0 b');
			});
		}
	}

	custom.activate = function() {
		var key = $('#lic_key').val().trim();
		if (!key) {
			ModAlert.addAlert({
				type: 'warning',
				text: mod_lang.empty_key,
			});
			return!1;
		}
		if (key.split('-').length != 5 || key.length != 29) {
			ModAlert.addAlert({
				type: 'warning',
				text: mod_lang.wrong_key,
			});
			return!1;
		}
		sendAjax('activate', {key: key}, function(d){
			window.location.reload();
		});
	}

	$(function(){
		for (var name in mod_data) {
			var html = getTemplate('itemWrapperTemplate', mod_data[name]);
			html = html.replace(/{name}/gi, name);
			$('#mod-items').append(html);
			custom.printItem(name);
		}

		$('.dd').nestable({
			maxDepth: 1
		}).on('change', function(e) {
			if (e.target.className != 'dd') return!1;
			custom.changeSort($(this).nestable('serialize'));
		});
	})

}(window.custom = window.custom || {}, jQuery));

var ModAlert={config:{type:"",title:"",text:"",timeout:3e3,autohide:!0,showicon:!0,showtitle:!0},type:{success:"Успешно",info:"Информация",warning:"Внимание!",error:"Ошибка"},addAlert:function(t){var e=this;$(".modAlert").length<1&&$("body").append('<div class="modAlert"></div>');var i=$.extend({},this.config,t);i.title||(this.type[i.type]?i.title=this.type[i.type]:i.type&&(i.title=i.type));var o=["modAlert-item"];i.type&&o.push("modAlert-"+i.type),i.showicon||o.push("modAlert-noicon"),i.title&&i.text&&i.showtitle||(o.push("modAlert-onerow"),i.showtitle||o.push("modAlert-notitle"));var l='<div class="'+o.join(" ")+'">';i.showicon&&(l+='<div class="modAlert-icon"></div>'),i.title&&i.showtitle&&(l+='<div class="modAlert-title">'+i.title+"</div>"),i.text&&(l+='<div class="modAlert-text">'+i.text+"</div>"),l+="</div>";var d=$(l);return d.appendTo(".modAlert").slideDown(200),d.click(function(){e.hideItem($(this))}),i.autohide&&i.timeout>0&&setTimeout(function(){e.hideItem(d)},i.timeout),!1},hideItem:function(t){t.animate({left:"-"+$(".modAlert").outerWidth()},220,function(){$(this).remove(),$(".modAlert-item").length<1&&$(".modAlert").remove()})}};
mod_alert&&ModAlert.addAlert(mod_alert);

function copyToClipboard(element) {
	var $temp = $('<input>');
	$('body').append($temp);
	$temp.val($(element).text()).select();
	document.execCommand("copy");
	$temp.remove();
	ModAlert.addAlert({
		type: 'success',
		text: mod_lang.copied,
		timeout: 1200,
	});
	return false;
}

$(document).on('click', '.mod-faq-q', function(e){
	e.preventDefault();
	var item = $(this).closest('.mod-faq-item');
	item.toggleClass('expand');
	item.find('.mod-faq-a').slideToggle(300);
})