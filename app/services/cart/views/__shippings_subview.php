<h4><?=$this->text('Доставка')?></h4>

<?php if(count($shippings) > 1) {
    $selected = $this->data->re_post('shipping-method');
    if(empty($selected) && $userShipping && $userShipping->method_id)
        $selected = $userShipping->method_id;
    ?>
    <div class="form-group">
        <label for="shipping-method"><?=$this->text('Служба доставки')?></label>
        <select name="shipping-method" class="form-control" required>
            <?php foreach ($shippings as $method) { ?>
                <option value="<?=$method->id?>" <?=($selected == $method->id) ? 'selected':''?> ><?=$method->name?></option>
            <?php } ?>
        </select>
    </div>
<?php } else { ?>
    <input type="hidden" name="shipping-method" value="<?=$shippings[0]->id?>">
<?php } ?>

<div class="alert alert-warning" id="shipping-info" <?=(empty($shippingInfo)) ? 'style="display:none"':''?>>
    <?=html_entity_decode($shippingInfo)?>
</div>

<?php foreach(['country' => 'Країна', 'city' => 'Місто', 'department' => 'Відділення', 'address' => 'Адреса'] as $field_key => $field_name) { 
    $value = $this->data->re_post('shipping-'.$field_key);
if(empty($value) && $userShipping && !empty($userShipping->$field_key))
    $value = $userShipping->$field_key;
$placeholder = $field_name;
if($field_key == 'department')
    $placeholder = 'Введіть номер/адресу відділення';
?>
<div class="form-group <?= in_array($field_key, $shippingTypeFields) ? '' : 'hide' ?>" id="shipping-<?=$field_key?>">
    <label><?=$this->text($field_name)?></label>
    <?php if($field_key == 'address') { ?>
        <textarea class="form-control" name="shipping-<?=$field_key?>" placeholder="<?=$this->text('Поштовий індекс, вул. Київська 12, кв. 3')?>" rows="3" <?= in_array($field_key, $shippingTypeFields) ? 'required' : '' ?>><?= $value ?></textarea>
    <?php } else { ?>
        <input type="text" name="shipping-<?=$field_key?>" class="form-control" placeholder="<?=$this->text($placeholder)?>" value="<?= $value ?>" <?= in_array($field_key, $shippingTypeFields) ? 'required' : '' ?> autocomplete="nope">
    <?php } ?>
</div>
<?php } ?>

<div id="shipping_to_cart">
	<?php if($shippingWlAlias != $_SESSION['alias']->id) {
		$this->load->function_in_alias($shippingWlAlias, '__get_Shipping_to_cart', $userShipping);
    } ?>
</div>

<h4 class="<?=$this->userIs() ? '' : 'hide'?>"><?=$this->text('Отримувач')?></h4>
<div class="d-flex <?=$this->userIs() ? '' : 'hide'?>">
    <div class="w50-5">
        <?php $recipientName = $this->data->re_post('recipientName');
        $recipientSurName = $this->data->re_post('recipientSurName');
        if(empty($recipientName))
        {
            $userName = $userShipping && $userShipping->recipientName ? $userShipping->recipientName : '';
            if($userName == '' && $this->userIs())
                $userName = $_SESSION['user']->name;
            if(!empty($userName))
            {
                $userName = explode(' ', $userName);
                $recipientName = array_shift($userName);
                $recipientSurName = implode(' ', $userName);
            }
        } ?>
        <input type="text" name="recipientName" class="form-control" placeholder="<?=$this->text('Ім\'я отримувача')?>" title="<?=$this->text('Ім\'я отримувача')?>" value="<?= $recipientName ?>" required>
    </div>
    <div class="w50-5">
        <input type="text" name="recipientSurName" class="form-control" placeholder="<?=$this->text('Прізвище отримувача')?>" title="<?=$this->text('Прізвище отримувача')?>" value="<?= $recipientSurName ?>" required>
    </div>
</div>
<?php $recipientPhone = $this->data->re_post('recipientPhone');
if(empty($recipientPhone) && $userShipping && !empty($userShipping->recipientPhone))
    $recipientPhone = $userShipping->recipientPhone;
if(empty($recipientPhone) && $this->userIs() && !empty($_SESSION['user']->phone))
    $recipientPhone = $_SESSION['user']->phone;
?>
<div class="form-group <?=$this->userIs() ? '' : 'hide'?>">
    <input type="text" name="recipientPhone" class="form-control" placeholder="<?=$this->text('+380********* (Контактний номер)')?>" value="<?= $recipientPhone ?>" required>
</div>

<link rel="stylesheet" href="<?=SERVER_URL?>assets/select2/select2.min.css" />
<?php $this->load->js('assets/select2/select2.min.js'); ?>