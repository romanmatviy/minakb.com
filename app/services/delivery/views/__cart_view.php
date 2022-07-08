<?php
    $noCity = false;

    if(!empty($delivery->method))
    {
        if($methods)
            foreach ($methods as $method) {
                if($method->id == $delivery->method)
                {
                    if($method->department == 2)
                        $noCity = true;
                    break;
                }
            }
        if(!$noCity)
        {
            if($delivery->method == 1)
                $city = explode(":", $delivery->address, 2);
            else
                $anotherCity = explode(":", $delivery->address, 2);
        }
    }

    $info = ($methods) ? $methods[0]->info : '';
?>

<input type="hidden" name="delivery_alias" value="<?=$_SESSION['alias']->id?>">

<div class="row">
    <?php if($methods) {
        if(count($methods) > 1) { ?>
        <div class="form-group">
            <label for="shipping-method"><?=$this->text('Служба доставки')?></label>
            <select name="shipping-method" id="shipping-method" class="form-control" required onchange="changeInfo(this)">
                <?php foreach ($methods as $method) { ?>
                    <option value="<?=$method->id?>" <?php if($delivery->method && $delivery->method == $method->id){ echo 'selected'; $info = $method->info; } ?> ><?=$method->name?></option>
                <?php } ?>
            </select>
        </div>
    <?php } else { ?>
        <input type="hidden" name="shipping-method" value="<?=$methods[0]->id?>">
    <?php } } ?>

    <div class="alert alert-warning" id="shipping-info" <?=(empty($info)) ? 'style="display:none"':''?>>
        <?=$info?>
    </div>

    <div class="form-group <?= $noCity ? 'hidden' : '' ?>" id="CityInput">
        <label><?=$this->text('Місто доставки')?></label>
        <input type="text" name="shipping-city" class="form-control" id="shipping-cities" placeholder="<?=$this->text('Місто')?>" value="<?= isset($city) ? rtrim($city[0]) : (isset($anotherCity) ? rtrim($anotherCity[0]) : '' ) ?>" <?= $noCity ? '' : 'required' ?>>
    </div>

    <div class="form-group <?= isset($city) || isset($anotherCity) ? '' : 'hidden' ?>" id="novaPoshtaDepartments" >
        <label><?=$this->text('Відділення')?></label>
        <select class="form-control <?= isset($city) ? '' : 'hidden' ?>" name="shipping-department" id="shipping-department" <?= isset($city) ? 'required' : '' ?>>
            <?php if(isset($city)) { ?>
            <option disabled value=""><?=$this->text('Виберіть відділення')?></option>
            <?php foreach(json_decode($warehouse_by_city, true)[trim(htmlspecialchars_decode($city[0], ENT_QUOTES))] as $department) { $department = '№'.$department['number'] .' : '. $department['address']; ?>
            <option value="<?= $department ?>" <?= $department == trim($city[1]) ? 'selected' : '' ?> ><?= $department ?></option>
            <?php } }?>
        </select>
        <input type="text" name="shipping-department-other" class="form-control <?= isset($anotherCity) ? '' : 'hidden' ?>" value="<?= isset($anotherCity) ? $anotherCity[1] : '' ?>" id="shipping-department-other" placeholder="<?=$this->text('Введіть номер/адрес відділення')?>">
    </div>

    <div class="form-group <?= isset($city) || isset($anotherCity) ? '' : 'hidden' ?>" id="shipping-address">
        <label><?=$this->text('Адреса')?></label>
        <textarea class="form-control" name="shipping-address" placeholder="<?=$this->text('Поштовий індекс, вул. Київська 12, кв. 3')?>" rows="3"></textarea>
    </div>

    <div class="form-group">
        <label><?=$this->text('Отримувач')?></label>
        <input type="text" name="name" class="form-control" id="shipping-receiver" placeholder="<?=$this->text('Ім\'я Прізвище')?>" value="<?= isset($delivery->receiver) ? $delivery->receiver : '' ?>" required>
    </div>

    <?php if($this->userIs()) { ?>
        <div class="form-group">
            <label><?=$this->text('Контактий номер телефону')?></label>
             <input type="text" name="phone" class="form-control" id="shipping-phone" placeholder="<?=$this->text('+380*********')?>" value="<?= isset($delivery->phone) ? $delivery->phone : '' ?>" required>
        </div>
    <?php } else { ?>
        <div class="row">
            <div class="form-group col-sm-6">
                <div class="required">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
            </div>
            <div class="form-group col-sm-6">
                <div class="required">
                    <input type="text" name="phone" class="form-control" placeholder="<?=$this->text('+380********* (Контактний номер)')?>" required>
                </div>
            </div>
        </div>
    <?php } ?>

    <input type="hidden" name="shipping-default" id="shipping-default" value="1" checked> 

    <?php /*<div class="form-group">
        <input type="checkbox" name="shipping-default" id="shipping-default" value="1" checked> 
        <label class="checkbox" for="shipping-default"><?=$this->text('Використовувати ці дані, як інформацію по доставці за замовчуванням')?></label>
    </div>

    <div class="form-group">
        <input type="checkbox" name="shipping-agree" id="shipping-agree" value="1" required>
        <label class="checkbox" for="shipping-agree">
        <span class="delivagree"><?=$this->text('Я ознайомився з термінами доставки')?></span>
        </label>
    </div> */ ?>
</div>

<script>
var active_shipping_method = '<?=(!empty($delivery->method)) ? $delivery->method : $methods[0]->id?>';
var information = {
    <?php if($methods) foreach ($methods as $method)
        echo "\"$method->id\"" . ' : ' . ($method->info != '' ? "\"$method->info\"" : '""')  . ', ';
    ?>
};
var departments = {
    <?php if($methods) foreach ($methods as $method)
        echo "\"$method->id\"" . ' : "' . $method->department. '", ';
    ?>
};
var cities = [<?= $cities ?>];
var warehouse_by_city = <?= $warehouse_by_city ?>;
</script>
<?php $_SESSION['alias-cache'][$_SESSION['alias']->alias_from]->alias->js_load[] = 'js/'.$_SESSION['alias']->alias.'/shipping.js'; ?>