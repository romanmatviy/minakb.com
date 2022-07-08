$(function () {
	$('#ModalEditCurrency').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget),
			id = button.data('currencyid'),
            code = button.data('currencyсode'),
			title = button.attr('title');

		var modal = $(this);
		modal.find('.modal-title').html(title);
		modal.find('#currencyId').val(id);
        modal.find('#currencyCode').val(code);
		modal.find('#currencyValue').val($('#currency-'+id).text());
	});

    $('#ModalDeleteCurrency').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget),
            id = button.data('currencyid'),
            code = button.data('currencyсode'),
            title = button.attr('title');

        var modal = $(this);
        modal.find('.modal-title').html(title+'?');
        modal.find('[name=id]').val(id);
        modal.find('[name=code]').val(code);
    });
});

function updateCurrency() {
	$('#saveing').css("display", "block");

    $.ajax({
        url: ALIAS_ADMIN_URL + "save",
        type: 'POST',
        data: {
            id: currencyId.value,
            currency: document.forms.FormEditCurrency['currency'].value,
            json: true
        },
        success: function(res){
            if(res['success'])
            {
                $('#currency-'+currencyId.value).text(document.forms.FormEditCurrency['currency'].value);
                $.gritter.add({title:"Курс валют!",text:'Встановлено <strong>1 ' + currencyCode.value + ' = ' + document.forms.FormEditCurrency['currency'].value + ' у.о. </strong>'});
            }
            else
            	$.gritter.add({title:"Помилка!",text:res['error']});
            $('#saveing').css("display", "none");
            $('#ModalEditCurrency').modal('hide');
        },
        error: function(){
            $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
            $('#saveing').css("display", "none");
        },
        timeout: function(){
            $.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
            $('#saveing').css("display", "none");
        }
    });
    return false;
}

function deleteCurrency() {
    $('#saveing').css("display", "block");
    var id = document.forms.FormDeleteCurrency['id'].value;

    $.ajax({
        url: ALIAS_ADMIN_URL + "delete",
        type: 'POST',
        data: {
            id: id,
            json: true
        },
        success: function(res){
            if(res['success'])
            {
                $.gritter.add({title:"Курс валют!",text:'Валюту <strong>'+document.forms.FormDeleteCurrency['code'].value+'</strong> видалено'});
                $('#currency-row-'+id).slideUp();
            }
            else
                $.gritter.add({title:"Помилка!",text:res['error']});
            $('#saveing').css("display", "none");
        },
        error: function(){
            $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
            $('#saveing').css("display", "none");
        },
        timeout: function(){
            $.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
            $('#saveing').css("display", "none");
        }
    });

    $('#ModalDeleteCurrency').modal('hide');
    return false;
}