$('#modal-copy-dialog').on('show.bs.modal', function (event) {
	var button = $(event.relatedTarget) // Button that triggered the modal
	var alias = button.data('alias') // Extract info from data-* attributes
	var language = button.data('language')
	var title = button.data('title')

	var modal = $(this)
	modal.find('.copy-title').text(title)
	modal.find('.copy-language').text(language)
	modal.find('.modal-body input#copy-alias').val(alias)
	modal.find('.modal-body input#copy-language').val(language)
});

function confirmCopy () {
	$('#saveing').css("display", "block");
	var alias = $('#copy-alias').val();
	var lang = $('#copy-language').val();
	if(lang != ''){
		$.ajax({
			url: SITE_URL + "admin/wl_language_words/copy",
			type: 'POST',
			data: {
				alias: alias,
				language: lang,
				json : true
			},
			success: function(res){
				if(res['result'] == false){
					$.gritter.add({title:"Помилка!", text:res['error']});
				} else {
					$.gritter.add({title:"Ключові слова шаблону успішно встановлено!"});
					$('#modal-copy-dialog').modal('hide');
					$('td.alias-' + alias).each(function( index ) {
						var word = $( this ).attr('id');
						var value = $('#' + word + ' b').text();
						if($('#' + word + '-' + lang).val() == ''){
							$('#' + word + '-' + lang).val( value ); 
						}
					});
				}
				$('#saveing').css("display", "none");
			},
			error: function(){
				$.gritter.add({title:"Помилка!", text:"Помилка! Спробуйте ще раз!"});
				$('#saveing').css("display", "none");
			},
			timeout: function(){
				$.gritter.add({title:"Помилка!", text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
				$('#saveing').css("display", "none");
			}
		});
	}
}