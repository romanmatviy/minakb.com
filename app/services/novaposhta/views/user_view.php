<div class="container">
    <div class="row">
    	<form action="<?=SITE_URL.$_SESSION['alias']->alias?>/save" method="POST">
    		<input type="hidden" name="id" value="<?=$delivery->id?>">
	        <div class="col-lg-6">
	            <div class="form-group">
	                <label for="name">Служба доставки за замовчуванням</label>
	                <select name="method" class="form-control" required>
	                	<option value="0" disabled <?=($delivery->method == 0) ? 'selected' : ''?>>Не вказано</option>
	                	<?php if($methods) foreach ($methods as $method) { ?>
	                		<option value="<?=$method->id?>" <?=($delivery->method == $method->id) ? 'selected' : ''?>><?=$method->name?></option>
	                	<?php } ?>
	                </select>
	            </div>
	            <div class="form-group">
	                <label>Адреса доставки/№ відділення</label>
	                <textarea name="address" placeholder="м. Львів, Нова пошта, відділення №54, отримувач Сагайдак П.І." class="form-control" required><?=$delivery->address?></textarea>
	            </div>
	            <div class="form-group">
				    <div class="text-center">
				      	<button type="submit" class="btn btn-success">Зберегти</button>
				    </div>
				</div>
	        </div>
	    </form>
    </div>
</div>