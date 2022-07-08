<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST" class="form-horizontal">
    <input type="hidden" name="product_alias" value="<?=$product->wl_alias?>">
    <input type="hidden" name="product_id" value="<?=$product->id?>">

    <?php $i = 1; if(!empty($product->marketing)) {
        echo "<h4>Поточні зміни ціни товару відносно кількості</h4>";
        foreach ($product->marketing as $from => $price) {
        ?>
        <div class="row">
            <label class="col-md-1 control-label">Ціна від (од.)</label>
            <div class="col-md-3">
                <input type="number" name="from-<?=$i?>" value="<?=$from?>" min="0" step="1" required class="form-control">
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="number" name="price-<?=$i++?>" value="<?=$price?>" min="1" step="0.01" required class="form-control">
                    <span class="input-group-addon">y.o.</span>
                </div>
            </div>
        </div>
    <?php } } ?>
    <input type="hidden" name="max_i" value="<?=--$i?>">

    <h4>Додати нову зміну</h4>
    <?php for ($i=0; $i < 3; $i++) { ?>
        <div class="row">
            <label class="col-md-1 control-label">Ціна від (од.)</label>
            <div class="col-md-3">
                <input type="number" name="from-new-<?=$i?>" value="0" min="0" step="1" class="form-control">
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="number" name="price-new-<?=$i?>" value="0" min="0" step="0.01" class="form-control">
                    <span class="input-group-addon">y.o.</span>
                </div>
            </div>
        </div>
    <?php } ?>

    <p>Зміна ціни при нульовій кількості автоматично видаляється</p>

    <div class="form-group">
        <div class="col-md-3"></div>
        <button type="submit" class="btn btn-sm btn-success col-md-2">Зберегти</button>
    </div>
</form>