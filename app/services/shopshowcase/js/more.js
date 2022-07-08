function showMore(page, group, sort){
	var params = parseQueryString(document.location.search);

	setAttr('per_page', page*20);

	$('#loading').removeClass('hidden');
	$.ajax({
        url: SITE_URL+'shop/ajaxGetProducts',
        type: 'POST',
        data: {
            page : page,
            group : group,
            sort : sort,
            params : params
        },
        complete: function () {
        	$('#loading').addClass('hidden');
        },
        success:function (res) {
        	if(res.products)
        	{
        		$("#showMore").attr('onclick', 'showMore('+res.page+','+res.group+')');

	            $.each(res.products, function (i, val) 
	            {
					var name = val.name.replace(val.article, '').trim();
					$("#productRow").append('<div class="col-sm-3 col-xs-6 margin-bottom-5"><div class="product-full-brd"><div class="product-img product-img-brd">'+( val.list_photo ? '<a href="'+SITE_URL+val.link+'"><img class="full-width img-responsive" src="'+IMG_PATH + val.list_photo+'" alt="'+name+'"></a>' : '' )+ (val.old_price > 0 && val.old_price > val.price ? '<div class="shop-rgba-red rgba-banner line-through">'+val.old_price+' грн</div>' : '' )+'</div><div class="product-description product-description-brd margin-bottom-5"><div class="overflow-h margin-bottom-5"><div><h4 class="title-price product-name-overflow"><a href="'+SITE_URL+val.link+'" title="'+name+'">'+name+'</a></h4></div><div class="product-price pull-left"><span class="title-price">'+val.price+' грн</span></div><div class="product-add-to-cart pull-right hidden-xs"><a href="'+SITE_URL+val.link+'" class="btn btn-sm" >Детальніше</a></div></div></div></div></div>');
	            });

	        	if(res.products.length < 20){
	            	$("#showMore").slideToggle('slow');
	            } 
        	}
        	else 
        		$("#showMore").slideToggle('slow');
        }
    })
}

var parseQueryString = function (querystring) { 
	var qsObj = new Object(); 
	if (querystring) { 
		var parts = querystring.replace(/\?/, "").split("&"); 

		var up = function (k, v) { 
			var a = qsObj[k] || qsObj[k.replace('[]', '')]; 
			if (typeof a == "undefined") { 
				if(k.indexOf('[]') > 0){
					k = k.replace('[]', '');
					qsObj[k] = [v];
				}
				else
					qsObj[k] = v; 
			} 
			else if (a instanceof Array) { 
				a.push(v); 
			} 
		}; 

		for (var i in parts) { 
			var part = parts[i]; 
			var kv = part.split('='); 
			if (kv.length == 1) { 
				var v = decodeURIComponent(kv[0] || ""); 
				up(null, v); 
			} 
			else if (kv.length > 1) { 
				var k = decodeURIComponent(kv[0] || ""); 
				var v = decodeURIComponent(kv[1] || ""); 
				up(k, v); 
			} 
		} 
	} 
	return qsObj; 
};

function setAttr(prmName,val){
    var res = '';
	var d = location.href.split("#")[0].split("?");
	var base = d[0];
	var query = d[1];
	if(query) {
		var params = query.split("&");
		for(var i = 0; i < params.length; i++) {
			var keyval = params[i].split("=");
			if(keyval[0] != prmName) {
				res += params[i] + '&';
			}
		}
	}
	res += prmName + '=' + val;

	window.history.pushState(null, null, base + '?' + res);

	return false;
}