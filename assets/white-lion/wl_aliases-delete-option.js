$('#modal-delete-option').on('show.bs.modal', function (event) {
	var button = $(event.relatedTarget) // Button that triggered the modal
	var id = button.data('id') // Extract info from data-* attributes
	var title = button.data('title')

	var modal = $(this)
	modal.find('.modal-title').text('Видалити параметр "' + title + '"')
	modal.find('.modal-body input').val(id)
});

function deleteOption () {
	var id = $('#option-id-to-delete').val();
	if(id > 0){
		$.ajax({
			url: SITE_URL + "admin/wl_aliases/deleteOption",
			type: 'POST',
			data: {
				id: id,
				json : true
			},
			success: function(res){
				if(res['result'] == false){
					$.gritter.add({title:"Помилка!",text:res['error']});
				} else {
					$.gritter.add({title:"Параметр успішно видалено!"});
					$('#modal-delete-option').modal('hide');
					$('#option-'+id).fadeTo(400, 0, function () { 
				        $(this).remove();
				    });
				}
			},
			error: function(){
				$.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
			},
			timeout: function(){
				$.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
			}
		});
	}
}