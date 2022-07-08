<div class="row" id="newProduct" hidden>
	<input type="hidden" id="userType" value="<?= isset($cart->user_type) ? $cart->user_type : $_SESSION['option']->new_user_type ?>">
	<div class="col-md-7">
		<table class="table table-striped table-bordered nowrap" width="100%" id="cartAddProduct">
			<tbody>
				<tr>
					<th>Артикул товару</th>
					<td>
						<div class="input-group">
							<input type="text" name="article" id="productArticle" class="form-control" required="">
							<span class="input-group-btn">
	    						<button type="submit" class="btn btn-info" onclick="getProduct()"><i class="fa fa-search"></i> Знайти</button>
	    					</span>
						</div>
					</td>
					<td>або <a href="#modal-add-virtual-product" data-toggle="modal" class="btn btn-sm btn-warning pull-right"><i class="fa fa-plus"></i> Додати віртуальний товар</a></td>
				</tr>
			</tbody>
		</table>
		<h4 id="invoicesError" style="color:red; text-align:center"></h4>
	</div>
	<div class="col-md-5"><strong>Віртуальний товар</strong> замовлення - довільний товар/послуга, який можна створити і додати до замовлення без прив'язки до існуючої товарної бази сайту. Крім замовлення даний товар ніде не відображається/не відслідковується. Рекомендується для разових послуг, тимчасових товарів, пропозицій тощо.</div>
	<div class="col-md-12" id="productInvoices" hidden>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function(){
		var navTabs = $('.nav-tabs a');
	    var hash = window.location.hash;
	    if (navTabs && location.hash != '')
	    	$('a[href="'+location.hash+'"]').tab('show');

	    $("#productArticle").keypress(function (e) {
	    	if(e.keyCode == 13){
	    		getProduct();
	    	}
	    })
	});

	function getProduct () {
		var article = $("#productArticle").val(),
			cartId = <?= isset($cart) ? $cart->id : 0?>,
			userId = <?= isset($cart->user) ? $cart->user : '$("#userId").val()' ?> ;
			userType = $("#userType").val();

		if(!article) return false;

	    $('#saveing').css("display", "block");
	    $("#invoicesError").text('');

	    $.ajax({
	        url: "<?= SITE_URL."admin/".$_SESSION['alias']->alias?>/getProductByArticle",
	        type: 'POST',
	        data: {
	            product: article,
	            cartId : cartId,
	            userType: userType,
	            userId : userId,
	            json: true
	        },
	        success: function(res) {
	            if(res) {
	            	$("#productInvoices").slideDown('slow').html(res);
	            } else {
	            	$("#productInvoices").html('');
	            	$("#invoicesError").text('Жодного товару не знайдено');
	            }
	            $('#saveing').css("display", "none");
	        },
	        error: function(){
	            alert("Помилка! Спробуйте ще раз!");
	            $('#saveing').css("display", "none");
	        },
	        timeout: function(){
	            alert("Помилка: Вийшов час очікування! Спробуйте ще раз!");
	            $('#saveing').css("display", "none");
	        }
	    });
	}
</script>

<div class="modal fade" id="modal-add-virtual-product">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Додати віртуальний товар/послугу</h4>
			</div>
			<form action="<?=SERVER_URL.'admin/'.$_SESSION['alias']->alias.'/add_virtualProduct'?>" method="post" enctype="multipart/form-data" class="m-15 form-horizontal">
				<input type="hidden" name="cart_id" value="<?=$cart->id ?? 0?>">
				<input type="hidden" name="user_id" value="0">

				<div class="form-group">
                    <label class="col-md-3 control-label">Зображення</label>
                    <div class="col-md-9">
                        <input type="file" name="product-image" accept="image/*" class="form-control">
                    </div>
                </div>
				<div class="form-group">
                    <label class="col-md-3 control-label">Артикул</label>
                    <div class="col-md-9">
                        <input type="text" name="product-article" value="" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Назва</label>
                    <div class="col-md-9">
                        <input type="text" name="product-name" value="" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Ціна</label>
                    <div class="col-md-9">
                        <input type="number" name="product-price" value="0" min="0" step="0.01" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Кількість од.</label>
                    <div class="col-md-9">
                        <input type="number" name="product-quantity" value="1" min="1" class="form-control" required>
                    </div>
                </div>

				<div class="modal-footer">
					<a href="javascript:;" class="btn btn-sm btn-white" data-dismiss="modal">Скасувати</a>
					<button type="submit" class="btn btn-sm btn-warning"><i class="fa fa-plus"></i> Додати</button>
				</div>
			</form>
		</div>
	</div>
</div>