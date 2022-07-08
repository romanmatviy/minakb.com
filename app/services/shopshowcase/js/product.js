function updateProductPrice()
{
	if(productOptionsChangePrice.length > 0)
	{
		var options = {};
		for (var i = 0; i < productOptionsChangePrice.length; i++) {
			var id = productOptionsChangePrice[i];
			if(id == 0)
			{
				var tkanyna = $('input[name=product-option-0]:checked');
				id = tkanyna.data('id');
				value = tkanyna.val();
				if(value && id > 0)
					options['o'+id] = value;
			}
			else
			{
				var value = $('input[name=product-option-'+id+']:checked').val();
				if(value)
					options['o'+id] = value;
			}
			
		}
		if(Object.keys(options).length > 0)
		{
			$.ajax({
				url: ALIAS_URL+'ajaxupdateproductprice',
				type: 'POST',
				data: {
					'product' : productID,
					'options' : options
				},
				success:function(res){
					if(res.price){
						$('#product-price').html(res.price + ' грн');
					}
				}
			})
		}
	}
}

$('#tabs').tabs();

$('.product-gallery').lightSlider({
    gallery:true,
    item:1,
    auto:true,
    loop:true,
    thumbItem:4,
    slideMargin:0,
    enableDrag: false,
    mode: 'fade',
    speed: 1000,
    currentPagerPosition:'left',
    onSliderLoad: function(el) {
        el.lightGallery({
            selector: '.product-gallery figure'
        });
    }   
});
