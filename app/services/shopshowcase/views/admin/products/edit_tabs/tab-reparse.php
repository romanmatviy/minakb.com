<div class="row">
    <div class="col-md-12">
		<form class="col-md-6 " method="POST" action="<?=SITE_URL?>admin/decathlon/reparse" >
            <input type="hidden" name="product" value="<?= $product->id ?>">
            <div class="form-group m-r-10">
               	<input type="checkbox" name="price" id="price" value="1" >
            	<label for="price">Ціна</label>
            </div>
            <div class="form-group m-r-10">
               	<input type="checkbox" name="availability" id="availability" value="1" >
            	<label for="availability">Наявність</label>
            </div>
            <div class="form-group m-r-10">
               	<input type="checkbox" name="image" id="image" value="1" >
               	<label for="image">Фото</label>
            </div>
            <div class="form-group m-r-10">
               	<input type="checkbox" name="similar" id="similar" value="1" >
            	<label for="similar">Подібні</label>
            </div>
            <div class="form-group m-r-10">
                <input type="submit" class="btn btn-success" value="Репарсити">
            </div>
        </form>
    </div>
</div>