<div class="flex returns_page">
    <div class="w50-5">
		<h2>Повідомити про повернення товару</h2>
    	<form class="form-horizontal" onsubmit="return checkReturn()">
            <h4>Вкажіть номер замовлення:</h4>
            <div class="flex">
            	<input type="number" id="return-order" required min="1" value="<?=!empty($cart_id) ? $cart_id : ''?>" placeholder="Номер замовлення">
		    	<button type="submit" class="btn btn-success">Перевірити</button>
            </div>
		</form>
	</div>
    <div class="w50-5">
    	<h3>УМОВИ ПОВЕРНЕННЯ:</h3>
    	<ol>
    		<li>Від дня відправки замовлення має бути не більше 14 днів.</li>
    		<li>Помилка клієнта при виборі каталожного номера запчастини не може бути причиною повернення.</li>
    		<li>Товар повинен мати неушкоджену упаковку, у відповідній комплектності і без слідів установки</li>
    		<li>До поверненню не приймаються запчастини, привезені `під замовлення` або куплені з партнерських складів</li>
    	</ol>
	</div>
</div>
<div class="returns_page">
	<div id="order-check-access" class="alert alert-warning order-check-result" <?=(!empty($res) && $res['result'] === 'access') ? '' : 'style="display: none;"'?>>
    	Увага! До повернення доступні лише Ваші <a href="/cart/my">замовлення</a>
    </div>
    <div id="order-check-time" class="alert alert-warning order-check-result" <?=(!empty($res) && $res['result'] === 'time') ? '' : 'style="display: none;"'?>>
    	Увага! Після покупки замовлення пройшло більше 14 днів
    </div>
    <div id="order-check-status" class="alert alert-warning order-check-result" <?=(!empty($res) && $res['result'] === 'status') ? '' : 'style="display: none;"'?>>
    	Увага! До повернення доступні лише підтверджені замовлення (оплачено та відправлено товар)
    </div>

	<form action="<?=SITE_URL.$_SESSION['alias']->alias?>/request" method="POST" class="order-check-result" <?=(!empty($res) && $res['result'] === true) ? '' : 'style="display: none;"'?> onsubmit="return checkReturnProduct()">
		<h4>Серед наявних товарів оберіть бажаний. У колонці "На повернення" вкажіть необхідну кількість одиниць, що повертаються</h4>
		<input type="hidden" name="order" id="form-return-order" required min="1" value="<?=!empty($cart_id) ? $cart_id : 0?>">
        <div class="table-responsive">
    		<table class="table table-striped table-bordered nowrap" width="100%">
    			<thead>
	    			<tr>
	    				<th>Бренд</th>
	    				<th>Артикул</th>
	    				<th>Товар</th>
	    				<th>Ціна USA/од</th>
	    				<th title="Доступно до повернення (од)">Доступно (од)</th>
	    				<th>На повернення:</th>
	    			</tr>
	    		</thead>
	    		<tbody>
	    			<?php $canReturn = false;
	    			 if(!empty($res) && !empty($res['products'])) { foreach ($res['products'] as $product) { ?>
	    				<tr>
	    					<td><?=$product->manufacturer_name?></td>
	    					<td><?=$product->article_show?></td>
	    					<td><?=$product->name?></td>
	    					<td><?=$product->price?></td>
	    					<?php if($product->quantity) { $canReturn = true; ?>
		    					<td><?=$product->quantity?></td>
		    					<td><input type="number" name="product-<?=$product->id?>" value="0" min="0" max="<?=$product->quantity?>" class="form-control"></td>
		    				<?php } else { ?>
		    					<td colspan="2">артнерський склад / Повернуто</td>
		    				<?php } ?>
	    				</tr>
	    			<?php } } ?>
	    		</tbody>
    		</table>
    	</div>
        <textarea class="form-control canReturn" name="reason" rows="5" required placeholder="Причина повернення" <?=$canReturn ? '':'disabled'?>></textarea>
    	<button type="submit" class="btn btn-success canReturn" <?=$canReturn ? '':'disabled'?>>Надіслати на перевірку</button>
	</form>
</div>
<div id="order-check-ZERO-products" class="alert alert-danger order-check-result" style="position: absolute; top: 35%; width: 60%; left: 20%;display: none;">
	<strong>Увага!</strong> У колонці "На повернення" вкажіть необхідну кількість одиниць товару, що повертається!
	<span class="close" onclick="$('#order-check-ZERO-products').slideUp();">×</span>
</div>

<?php if(!empty($returns)) { ?>
<div class="returns_page" style="margin-top: 50px">
	<h2>Заявки на повернення</h2>
        <div class="table-responsive">
    		<table class="table table-striped table-bordered nowrap" width="100%">
    			<thead>
	    			<tr>
                        <th>Дата</th>
	    				<th>Статус</th>
                        <th>Замовлення</th>
                        <th>Товар</th>
                        <th>Кількість</th>
                        <th>ТТН</th>
	    			</tr>
	    		</thead>
	    		<tbody>
	    			<?php foreach ($returns as $return) { ?>
	    				<tr class="strong-top <?=($return->status == 1 && $return->manager > 0)?'warning':''?>">
	    					<td><?=date('d.m.Y H:i', $return->date_add)?></td>
	    					<td>
                                <?php switch ($return->status) {
                                    case 1:
                                        if($return->manager > 0)
                                            echo "Повернення дозволено";
                                        else
                                            echo "Очікує обробки";
                                        break;
                                    case 2:
                                        echo "Підтверджено";
                                        break;
                                    case 3:
                                        echo "Скасовано";
                                        break;
                                } ?>
                            </td>
	    					<td><a href="/cart/<?=$return->cart_id?>" class="btn btn-info">№ <?=$return->cart_id?></a></td>
	    					<td><strong><?=$return->product_article?></strong> <?=$return->product_manufacturer.' '.$return->product_name?></td>
	    					<td><?=$return->quantity?></td>
	    					<td><?php if($return->status == 1 && $return->manager > 0) { ?>
	    						<input type="text" value="<?=$return->ttn?>" onchange="saveTTN(this, <?=$return->id?>)" placeholder="Внесіть ТТН повернення">
	    					<?php } else echo $return->ttn; ?>
	    					</td>
	    				</tr>
	    				<?php if($return->reason) { ?>
	    					<tr <?=($return->status == 1 && $return->manager > 0)?'class="warning"':''?>>
	    						<td>Причина повернення: </td>
	    						<td colspan="5"><?=$return->reason?></td>
	    					</tr>
	    			<?php } if($return->info) { ?>
	    					<tr <?=($return->status == 1 && $return->manager > 0)?'class="warning"':''?>>
	    						<td>Повідомлення клієнту: </td>
	    						<td colspan="5"><?=$return->info?></td>
	    					</tr>
	    			<?php } } ?>
	    		</tbody>
    		</table>
    	</div>
	</form>
</div>
<?php } $this->load->js('js/'.$_SESSION['alias']->alias.'/returns.js'); ?>
<style>
	.returns_page * { box-sizing: border-box }
	.returns_page input {
		transition: 0.54s;
		padding: 12px 20px 12px;
		font-size: 15px;
		border: 1px solid #10879b;
	}
	.returns_page form .flex input { width: calc(100% - 150px) }
	.returns_page form .flex button { width: 150px }
	.returns_page h3 { font-size: 18px; padding-left: 10px; }
	.returns_page h4 { font-size: 15px }
	.returns_page ol li { margin-bottom: 5px }
	.returns_page table { width: 100%; max-width: 100%; border-collapse: collapse; }
	.returns_page table th, .returns_page table td {
	    border: 1px solid #eee;
	    padding: 10px;
	}
	.returns_page table tr th, .returns_page table tr.strong-top td { border-top: 2px solid #888 }
	.returns_page table tr.warning td { border-color: #fbd7a3; background-color: #fdebd1 }
	.returns_page table td input { padding: 5px }
	.returns_page textarea {
		margin-top: 10px;
		width: 100%;
		padding: 5px;
		font-size: 14px;
		border: 1px solid #10879b;
	}
</style>