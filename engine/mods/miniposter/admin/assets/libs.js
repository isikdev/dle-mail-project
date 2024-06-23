function modSetData(group, area, call){
	ShowLoading();
	$.post('', {ajax:1,group:group,area:area}, function(d){
		HideLoading();
		if (d.error) {
			DLEalert(d.error, 'Ошибка');
		} else if (d.info) {
			DLEalert(d.info, 'Информация');
		} else {
			call();
		}
	},"json");
}

$(document)
.on('submit', 'form', function(){
	ShowLoading();
})

.on('click', '[data-mod_status]', function(e){
	e.preventDefault();
	var $this = $(this);
	modSetData($this.data('mod_status'), 'status', function(){
		$this.toggleClass('ptable-status-on');
	})
})
.on('click', '[data-mod_clear]', function(e){
	e.preventDefault();
	modSetData($(this).data('mod_clear'), 'clear')
})

.on('click', '.modAlert-close', function(e){
	e.preventDefault();
	$(this).closest('.modAlert-item').slideUp(200,function(){
		$(this).remove();
		if (!$('.modAlert-item').length) {
			$('.modAlert').hide();
		}
	})
})
