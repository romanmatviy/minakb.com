$(document).ready(function (){
	if (typeof Sticky === "function")
		var sticky_price_box = new Sticky('#cart .price-box');
	recount()
});

var cart = {
	'add' : function(productKey, quantity, options_id)
	{
		var alertOptions = [];
		if(quantity == 0)
			quantity = $("#productQuantity").val();
		if(options_id != '')
		{
			var options = [];
			for (var i = 0; i < options_id.length; i++) {
				var id = options_id[i].toString();
				var value = false;
				var elem = $('[name=product-option-' + id + ']');
				if(elem)
				{
					if(elem.attr('type') == 'radio')
						value = $('[name=product-option-' + id + ']:checked').val()
					else if(elem.prop("tagName") == 'SELECT')
						value = elem.find(':selected').val()
					else
						value = elem.val();
				}
				if(!value)
				{
					var name = $('#product-option-name-' + id).text();
					alertOptions.push(name);
				}
				else
				{
					if(elem.data('id'))
						id = elem.data('id');
					options[id] = value;
				}
			}
		}
		else
			options = '';
		if(alertOptions.length)
		{
			var text = 'Оберіть властивості товару: ';
			var popupCart = $('#cart-product-delete-bg');
			if(popupCart)
			{
				text += '<strong>';
				for (var i = 0; i < alertOptions.length; i++) {
					text += '<br>' + alertOptions[i];
				}
				text += '</strong>';

				popupCart.find('.product-img, .product-price, .actions a').hide();
				popupCart.find('.product-options').html(text);
				popupCart.css("display", "flex")
				    .hide()
				    .fadeIn();
			}
			else
			{
				for (var i = 0; i < alertOptions.length; i++) {
					text += ' ' + alertOptions[i];
				}
				alert (text);
			}
		}
		else

		$.ajax({
			url: CART_URL+'addProduct',
			type: 'POST',
			data: {
				'productKey' : productKey,
				'quantity' : quantity,
				'options' : options
			},
			success:function(res){
				if(res.result)
				{
					var popupCart = $('#cart-product-delete-bg');
					if(popupCart)
					{
						popupCart.find('.product-img, .product-price, .actions a').show();
						popupCart.find('.product-price').html('<strong>'+res.product.quantity+'</strong> x <span class="price text-primary">'+res.product.price_format+'</span>');
						popupCart.find('.product-options').html(res.product.product_options);
						popupCart.css("display", "flex")
						    .hide()
						    .fadeIn();
					}
					
					var miniCart = $("#shopping-cart-in-menu");
					if(miniCart)
					{
						var product_exist = document.getElementById('product-'+res.product.key);
						if(product_exist)
						{
							$('#product-'+res.product.key + ' span.amount').html('<strong>'+res.product.quantity+'</strong> x <span class="price text-primary">'+res.product.price_format+'</span>');
						}
						else
						{
							var li = $('<li/>', {id: 'product-'+res.product.key});
							var a = $('<a/>', {href: SITE_URL + res.product.link});

							$('<img/>', {
								"class": 'img-responsive product-img',
								src: SITE_URL + 'images/' + res.product.admin_photo
							}).appendTo(a.clone().appendTo(li));

							var div = $('<div/>', {'class': 'product-details'});

							a.text(res.product.name).insertBefore(
								$('<p/>', {
									'class': 'product-title clearfix',
									html: '<span class="amount">'+res.product.price_format+' x '+res.product.quantity+'</span>'
								}).appendTo(div)
							);

							div.appendTo(li.appendTo('#shopping-cart-in-menu .mCustomScrollbar .mCSB_container'));
						}

						$('.cart-empty').parent().remove();
						$('.subtotal-cost').text(res.subTotalFormat);
						$('#shopping-cart-in-menu .badge-open').slideDown();
						$('html, body').animate({
						    scrollTop: miniCart.offset().top
						}, 1000);
					}
				}
			}
		})
	},

	'remove' : function (id)
	{
		$('#cart_notify').addClass('hide');
		$.ajax({
			url: ALIAS_URL+'removeproduct',
			type: 'POST',
			data: {
				'id' : id
			},
			success:function(res){
				if(res['result'] == true)
				{
					$('#product-'+id).fadeOut().remove();
					$('#cart p.price strong').html(res.subTotalFormat);
					$('#cart p.discount strong').html(res.discountTotal);
					if($('.table__cart_products_list2 .tr').length == 0)
					{
						$('a[href$=checkout]').attr('disabled', true).removeClass('active');
						$('#cart .emptyCart').removeClass('hide').fadeIn();
					}
					recount(true);
				}
				else
				{
					$("#cart_notify p").text(res['error'])
					$('#cart_notify').removeClass('hide').fadeIn();
					$("html, body").animate({ scrollTop: 0 }, "slow");
				}
			}
		})
	},

	'update' : function(id, quantity)
	{
		$('#cart_notify').addClass('hide');
		$.ajax({
			url: ALIAS_URL+'updateproduct',
			type: 'POST',
			data: {
				'id' : id,
				'quantity' : quantity
			},
			success:function(res)
			{
				$("#productQuantity-"+id).val(res['quantity']);
				if(res['result'] == true)
				{
					$('#cart #pricePerOne-'+id).html(res.priceFormat);
					$('#cart #priceSum-'+id).html(res.priceSumFormat);
					$('#cart p.price strong').html(res.subTotalFormat);
					$('#cart p.discount strong').html(res.discountTotal);

					recount();
				}
				else
				{
					if(res.max)
						$("#productQuantity-"+id).attr('max', res['max']);
					$("#cart_notify p").text(res['error'])
					$('#cart_notify').removeClass('hide').fadeIn();
					$("html, body").animate({ scrollTop: 0 }, "slow");
				}
			}
		})
	},

	'active' : function(id, active)
	{
		$('#cart_notify').addClass('hide');
		$.ajax({
			url: ALIAS_URL+'updateproduct',
			type: 'POST',
			data: {
				'id' : id,
				'active' : active
			},
			success:function(res)
			{
				if(res['result'] == true)
				{
					$('#cart p.price strong').html(res.subTotalFormat);
					$('#cart p.discount strong').html(res.discountTotal);

					if(active)
					{
						$('#product-'+id + ' .product-active i').removeClass('far fa-circle').addClass('fas fa-check');
						$('#product-'+id + ' .product-active span').text(product_active);
						$('#product-'+id).removeClass('disabled');
					}
					else
					{
						$('#product-'+id + ' .product-active i').addClass('far fa-circle').removeClass('fas fa-check');
						$('#product-'+id + ' .product-active span').text(product_disabled);
						$('#product-'+id).addClass('disabled');
					}
					recount();
				}
				else
				{
					$("#cart_notify p").text(res['error'])
					$('#cart_notify').removeClass('hide').fadeIn();
					$("html, body").animate({ scrollTop: 0 }, "slow");
				}
			}
		})
	}
}

$('#cart .product-active').click(function () {
	var input = $(this).find('input');
	var active = 0;
	if (input.prop("checked"))
	{
		$(this).addClass('postpone');
		$(this).closest('.tr').attr('title', 'Товар відкладено');
		input.prop("checked", false);
	}
	else
	{
		$(this).removeClass('postpone');
		$(this).closest('.tr').attr('title', '');
		input.prop("checked", true);
		active = 1;
	}
	cart.active(input.val(), active);
});
$('#cart .table__cart_products_list2 button.delete').click(function () {
	var key = $(this).val();
	var product = $('#product-'+key);
	var popupCart = $('#cart-product-delete');
	if(popupCart)
	{
		$('#action-product-key').val(key);
		var img = product.find('img');
		if(img)
			popupCart.find('.product-img').empty().append(img.clone());
		popupCart.find('.product-name').html(product.find('.name_action a').text());
		popupCart.find('.product-price').html($('#pricePerOne-'+key).html()+' x '+ $('#productQuantity-'+key).val()+ ' = <strong>'+$('#priceSum-'+key).html()+'</strong>');
		// popupCart.find('.product-options').html(product.find('h3~p:not(.price)').clone());
		// popupCart.find('.product-options').html(product.find('p.article').clone());
		popupCart.fadeIn();
		$('#modal-bg').fadeIn()
	}
});
$('#cart-product-delete .actions .postpone').click(function () {
	var id = $('#action-product-key').val();
	if(id)
	{
		cart.active(id, 0);
		$('#product-'+id+' .product-active').addClass('postpone');
		$('#product-'+id+' .product-active input').attr('checked', false);
	}
	$('#modal-bg, .modal').fadeOut()
});
$('#cart-product-delete .actions .delete').click(function () {
	var id = $('#action-product-key').val();
	if(id)
		cart.remove(id);
	$('#modal-bg, .modal').fadeOut()
});

function recount(counter) {
	var products = $('.table__cart_products_list2 .tr:not(.disabled)');
	if(products.length)
	{
		$('a[href$=checkout]').attr('disabled', false).addClass('active');
	}
	else
		$('a[href$=checkout]').attr('disabled', true).removeClass('active');
}

function isInt(n) {
    return +n === parseInt(n) && !(n % 1);
}
$('.table__cart_products_list2 .amount input').on('change', function () {
	// this.value = this.value.replace(/(?![0-9])./gmi,'');
	changeQuantity(this, this.value.replace(/(?![0-9])./gmi,''));
})
$('.table__cart_products_list2 .amount span.minusInCart').click(function(){
	changeQuantity(this, '-')
})
$('.table__cart_products_list2 .amount span.plusInCart').click(function(){
	changeQuantity(this, '+')
})

function changeQuantity(el, action) {
	if(isInt(action))
	{
		var val = action,
			key = el.dataset.key,
			max = el.dataset.max;
    	el.value = val;
	}
	else
	{
		var amount = $(el).parent().find('input'),
			val = isInt(amount.val()) ? parseInt(amount.val()) : 0,
			key = amount.data('key'),
			max = amount.data('max');
	    switch(action){
	        case '-':
	            if(val > 0)
	                val--;
	            break;
	        case '+':
	        	if(val < max || max < 0)
	            	val++;
	            break;
	    }
    	amount.val(val);
	}
    max = isInt(max) ? parseInt(max) : 0;
    if(max < 0 || val <= max)
    {
	    cart.update(key, val)
	    if (typeof setPercents === "function")
	    	setPercents();
	}
	else
	{
		if(isInt(action))
		{
			el.value = max;
			el.title = 'Максимальна доступна кількість '+max+' од.';
		}
		else
		{
			amount.attr('title', 'Максимальна доступна кількість '+max+' од.');
			amount.val(max);
		}

		$("#cart_notify p").text('Максимальна доступна кількість '+max+' од.')
		$('#cart_notify').removeClass('hide').fadeIn();
		$("html, body").animate({ scrollTop: 0 }, "slow");
	}
}