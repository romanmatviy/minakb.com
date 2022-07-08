function checkReturn() {
	$('#loading').css("display", "block");
	$('.order-check-result').slideUp();
	$('form.order-check-result table > tbody').empty();
    $('.canReturn').attr('disabled', true);
	$.ajax({
		url: SITE_URL+'returns/check',
        type: 'POST',
        data: {
        	order: $('#return-order').val()
        },
        success:function(res){
            if(res['result'] == true) {
            	for (var i = 0; i < res['products'].length; i++) {
            		if(res['products'][i].can_return)
                    {
            			$('form.order-check-result table > tbody:last-child').append('<tr>'+
            							'<td>'+res['products'][i].manufacturer_name+'</td>'+
            							'<td>'+res['products'][i].article_show+'</td>'+
            							'<td>'+res['products'][i].name+'</td>'+
            							'<td>$'+res['products'][i].price+'</td>'+
            							'<td>'+res['products'][i].quantity+'</td>'+
            							'<td><input type="number" name="product-'+res['products'][i].id+'" value="0" min="0" max="'+res['products'][i].quantity+'" class="form-control"></td>'+
            							'</tr>');
                        $('.canReturn').attr('disabled', false);
                    }
            		else
            			$('form.order-check-result table > tbody:last-child').append('<tr>'+
            							'<td>'+res['products'][i].manufacturer_name+'</td>'+
            							'<td>'+res['products'][i].article_show+'</td>'+
            							'<td>'+res['products'][i].name+'</td>'+
            							'<td>$'+res['products'][i].price+'</td>'+
            							'<td colspan="2">Партнерський склад / Повернуто</td>'+
            							'</tr>');
            	}
            	
                $('#form-return-order').val(res['order']);
                $('form.order-check-result').slideDown();
            } else {
                $('#order-check-'+res['result']).slideDown();
            }
            $('#loading').css("display", "none");
        },
        error: function(){
            alert("Помилка! Спробуйте ще раз!");
            $('#loading').css("display", "none");
        },
        timeout: function(){
            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
            $('#loading').css("display", "none");
        }
    });

	return false;
}

function checkReturnProduct() {
	var toReturn = 0;
	var products = document.querySelectorAll('form.order-check-result table > tbody tr td input');
	for (var i = 0; i < products.length; i++) {
		toReturn += parseInt(products[i].value);
	}
	if(toReturn > 0)
		return true;
	else
		$('#order-check-ZERO-products').slideDown();
	return false;
}

function saveTTN(input, id) {
    $.ajax({
        url: SITE_URL+'returns/save_ttn',
        type: 'POST',
        data: {
            return_id: id,
            ttn: input.value
        },
        success:function(res){
            if(res['result'] == true) {
                alert('ТТН збережено');
            }
            $('#loading').css("display", "none");
        },
        error: function(){
            alert("Помилка! Спробуйте ще раз!");
            $('#loading').css("display", "none");
        },
        timeout: function(){
            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
            $('#loading').css("display", "none");
        }
    });
}