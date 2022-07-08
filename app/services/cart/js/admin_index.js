$('#clientName').on('input propertychange', function () {
	$('#clientsList').hide().empty();
	if(this.value.length >= 3 || Number.isInteger(this.value.length) )
		$.ajax({
	        type: "POST",
	        url: CART_ADMIN_URL+'searchClient',
	        data: {
	        	by: this.value,
	        	limit: 20,
	        	ajax: true
	        },
	        success: function(res)
	        {
	            if (res.data)
	            {
	            	res.data.forEach(function (client) {
	            		var company = '';
	            		if(client.company)
	            			company = '('+client.company+')';
	            		$('<p/>', {
	            			'data-id': client.id,
	            			html: '#'+client.id + ' <strong>'+client.name+'</strong> '+company,
	            			click: selectUser
	            		}).appendTo('#clientsList');
	            	});
	            	$('#clientsList').show();
	            }
	        }
	    });
});

function selectUser() {
	$('form#cartFilter input[name=user]').val(this.dataset.id);
	$('#clientName').val(this.innerText)
	$('#clientsList').hide();
	$('form#cartFilter').submit();
}