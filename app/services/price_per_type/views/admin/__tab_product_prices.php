<table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
    <thead>
        <tr>
            <th>Тип користувача</th>
            <th>Режим зміни відносно базової ціни</th>
            <th>Зміна ціни на</th>
        </tr>
    </thead>
    <tbody>
        <?php if($product->price_per_type->userTypes) foreach ($product->price_per_type->userTypes as $type) {
            $change_price = '+';
            $price = $currency = 0;
            if(isset($product->price_per_type->shopData[$type->id]))
            {
                $change_price = $product->price_per_type->shopData[$type->id]['change_price'];
                $price = $product->price_per_type->shopData[$type->id]['price'];
                $currency = $product->price_per_type->shopData[$type->id]['currency'];
            }
            if(isset($product->price_per_type->productData[$type->id]))
            {
                $change_price = $product->price_per_type->productData[$type->id]['change_price'];
                $price = $product->price_per_type->productData[$type->id]['price'];
                $currency = $product->price_per_type->productData[$type->id]['currency'];
            } ?>
            <tr>
                <th>
                    <?=$type->title?> <br>
                    <label><input type="checkbox" onchange="updatePPT(this, <?=$type->id?>)" <?=(isset($product->price_per_type->productData[$type->id]))?'':'checked'?>> Стандартна націнка</label>
                </th>
                <td>
                    <select class="form-control" id="change_price-ppt-<?=$type->id?>" onchange="savePPTChangePrice(<?=$type->id?>, '<?=$type->title?>')" <?=(isset($product->price_per_type->productData[$type->id]))?'':'disabled'?>>
                        <option value="+">+ додати фіксовані у.о.</option>
                        <option value="*" <?=($change_price == '*') ? 'selected' : ''?>>* помножити на коефіцієнт</option>
                        <option value="=" <?=($change_price == '=') ? 'selected' : ''?>>= точне значення</option>
                    </select>
                </td>
                <td>
                    <?php if(!empty($_SESSION['currency']) && is_array($_SESSION['currency'])) { ?>
                        <div class="row">
                            <div class="col-md-8">
                                <input type="number" step="0.001" value="<?=$price?>" id="price-ppt-<?=$type->id?>" class="form-control" onchange="savePPTChangePrice(<?=$type->id?>, '<?=$type->title?>')" <?=(isset($product->price_per_type->productData[$type->id]))?'':'disabled'?>>
                            </div>
                            <div class="col-md-4">
                                <select id="currency-ppt-<?=$type->id?>" class="form-control" onchange="savePPTChangePrice(<?=$type->id?>, '<?=$type->title?>')" <?=(isset($product->price_per_type->productData[$type->id]) && $change_price != '*')?'':'disabled'?>>
                                    <option value="0">y.o. (валюта товару)</option>
                                    <?php foreach ($_SESSION['currency'] as $code => $value) {
                                    if($code === $currency)
                                        echo '<option value="'.$code.'" selected>'.$code.'</option>';
                                    else
                                        echo '<option value="'.$code.'">'.$code.'</option>';
                                } ?>
                                </select>
                            </div>
                        </div>
                    <?php } else { ?>
                        <input type="number" step="0.001" value="<?=$price?>" id="price-ppt-<?=$type->id?>" class="form-control" onchange="savePPTChangePrice(<?=$type->id?>, '<?=$type->title?>')" <?=(isset($product->price_per_type->productData[$type->id]))?'':'disabled'?>>
                        <input type="hidden" id="currency-<?=$shop->id?>-<?=$type->id?>" value="<?=$currency?>">
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<script>
    function updatePPT(e, type_id, label) {
        if(e.checked)
        {
            $('#change_price-ppt-'+type_id+', #price-ppt-'+type_id+', #currency-ppt-'+type_id).attr("disabled", "disabled");

            $('#saveing').css("display", "block");
            $.ajax({
              url: "<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/deleteForProduct",
              type: 'POST',
              data: {
                shop_id: <?=$product->wl_alias?>,
                product_id: <?=$product->id?>,
                type_id: type_id,
                json: true
              },
              success: function(res){
                if(res['result'] == false){
                    $.gritter.add({title:"Помилка!", text:label + ' ' + res['error']});
                } else {
                  $.gritter.add({title:label, text:"Дані успішно збережено!"});
                }
                $('#saveing').css("display", "none");
              },
              error: function(){
                $.gritter.add({title:"Помилка!", text:"Помилка! Спробуйте ще раз!"});
                $('#saveing').css("display", "none");
              },
              timeout: function(){
                $.gritter.add({title:"Помилка!", text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
                $('#saveing').css("display", "none");
              }
            });
        }
        else
        {
            $('#change_price-ppt-'+type_id+', #price-ppt-'+type_id).attr("disabled", false);
            if($('#change_price-ppt-'+type_id).val() !== '*')
                $('#currency-ppt-'+type_id).attr("disabled", false);
        }
    }
    function savePPTChangePrice(type_id, label) {
        $('#saveing').css("display", "block");
        var change_price = document.getElementById('change_price-ppt-'+type_id).value;
        $.ajax({
          url: "<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/saveForProduct",
          type: 'POST',
          data: {
            shop_id: <?=$product->wl_alias?>,
            product_id: <?=$product->id?>,
            type_id: type_id,
            change_price: change_price,
            price: document.getElementById('price-ppt-'+type_id).value,
            currency: document.getElementById('currency-ppt-'+type_id).value,
            json: true
          },
          success: function(res){
            if(res['result'] == false){
                $.gritter.add({title:"Помилка!", text:label + ' ' + res['error']});
            } else {
              $.gritter.add({title:label, text:"Дані успішно збережено!"});
              if(change_price == '*')
                document.getElementById('currency-ppt-'+type_id).disabled = 'disabled';
              else
                document.getElementById('currency-ppt-'+type_id).disabled = false;
            }
            $('#saveing').css("display", "none");
          },
          error: function(){
            $.gritter.add({title:"Помилка!", text:"Помилка! Спробуйте ще раз!"});
            $('#saveing').css("display", "none");
          },
          timeout: function(){
            $.gritter.add({title:"Помилка!", text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
            $('#saveing').css("display", "none");
          }
        });
    }
</script>