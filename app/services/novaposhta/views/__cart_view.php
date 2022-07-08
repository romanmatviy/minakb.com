<div class="d-flex">
    <div class="w50-5">
        <input type="radio" name="nova-poshta-method" value="warehouse" id="np-warehouse" <?=empty($userShipping->address_street) ? 'checked' : ''?>>
        <label for="np-warehouse" class="radio"> <?=$this->text('На відділення')?> </label>
    </div>
    <div class="w50-5">
        <input type="radio" name="nova-poshta-method" value="courier" id="np-courier" <?=!empty($userShipping->address_street) ? 'checked' : ''?>>
        <label for="np-courier" class="radio"> <?=$this->text("Адреса")?> </label>
    </div>
</div>

<input type="hidden" name="nova-poshta-city-ref" value="<?=$userShipping->city_ref ?? ''?>">
<input type="hidden" name="nova-poshta-warehouse-ref" value="<?=$userShipping->warehouse_ref ?? ''?>">

<div class="form-group">
    <label><?=$this->text('Місто')?></label>
    <input type="text" name="novaposhta-city" class="form-control" placeholder="<?=$this->text('Місто')?>" value="<?=!empty($userShipping->city_ref) ? $userShipping->city : ''?>" autocomplete="off" required>
    <div id="novaposhta-city-list" style="position: relative"></div>
</div>

<div class="form-group mb-15 <?=!empty($userShipping->address_street) ? 'hide1' : ''?>" id="nova-poshta-warehouse" >
    <label class="d-block"><?=$this->text('Відділення')?></label>
    <select name="nova-poshta-warehouse" class="form-control" required>
        <?php $info = '';
        if (!empty($userShipping->city_ref) && !empty($userShipping->warehouse_ref)) {
            if($warehouses = $this->getWarehouses($userShipping->city_ref))
                foreach ($warehouses as $warehouse) {
                    $selected = '';
                    if($warehouse->id == $userShipping->warehouse_ref)
                    {
                        $selected = 'selected';
                        $info = $warehouse->info;
                    }
                    echo '<option data-id="'.$warehouse->id.'" data-info="'.htmlspecialchars($warehouse->info).'" title="'.$warehouse->title.'" '.$selected.'>'.$warehouse->name.'</option>';
                }
        } else { ?>
            <option selected disabled value=""><?=$this->text('Для вибору відділення спершу введіть та оберіть місто')?></option>
        <?php } ?>
    </select>
    <div id="nova-poshta-warehouse-list"></div>
    <div class="info <?=empty($info) ? 'hide1' : ''?>"><?=$info?></div>
</div>
<style>
    #novaposhta-city-list .ui-autocomplete,
    #novaposhta-address-list .ui-autocomplete {
        left: 0 !important;
        top: 0 !important;
    }
</style>

<div id="nova-poshta-courier" class="d-flex <?=empty($userShipping->address_street) ? 'hide1' : ''?>">
    <div class="form-group w50-5">
        <input type="text" name="novaposhta-address-street" class="form-control" placeholder="<?=$this->text('Вулиця')?>" value="<?=$userShipping->address_street ?? ''?>">
        <div id="novaposhta-address-list" style="position: relative"></div>
    </div>
    <div class="form-group w50-5">
        <input type="text" name="novaposhta-address-house" class="form-control" placeholder="<?=$this->text('Номер будинку/та квартри')?>" value="<?=$userShipping->address_house ?? ''?>">
    </div>
</div>
                                


<?php $novaposhta_selected = $this->data->re_post('shipping-novaposhta');
if(empty($novaposhta_selected) && $userShipping && $userShipping->department)
    $novaposhta_selected = $userShipping->department; ?>
<script>
    function setCity(city) {
        $("#nova-poshta-warehouse select").empty().append('<option selected disabled="" value="">Виберіть відділення</option>');
        $("#nova-poshta-warehouse .info").addClass('hide1');
        $('input[name="nova-poshta-city-ref"]').val(city);
        if($('input[name="nova-poshta-method"]:checked').val() == 'warehouse')
        {
            $("div#divLoading").addClass('show');
            $.ajax({
                url: '<?=SITE_URL.$_SESSION['alias']->alias?>/getWarehouses',
                type: 'POST',
                data: { 'city' : city },
                success:function(warehouses) {
                    if(warehouses)
                        warehouses.forEach(function(warehous) {
                            $('<option/>', { text: warehous.name, title: warehous.title, 'data-id': warehous.id, 'data-info': warehous.info}).appendTo($("#nova-poshta-warehouse select"))
                        });
                },
                complete: function() {
                    $("div#divLoading").removeClass('show');
                }
            })
        }
        else
            $('input[name="novaposhta-address-street"]').autocomplete({
                appendTo: "#novaposhta-address-list",
                source: '<?=SITE_URL.$_SESSION['alias']->alias?>/getAddresses/'+city,
                minLength: 2
            });
    }

    function initShipping() {
        $('#nova-poshta-warehouse select').select2({dropdownParent: $('#nova-poshta-warehouse-list')});
        $('input[name="nova-poshta-method"]').attr('required', true);

        $('input[name="novaposhta-city"]').autocomplete({
            appendTo: "#novaposhta-city-list",
            source: '<?=SITE_URL.$_SESSION['alias']->alias?>/getcities/warehouse',
            minLength: 3,
            select: function (event, ui) {
                $('input[name="novaposhta-city"]').val(ui.item.value);
                setCity(ui.item.id);
            }
        }).attr('autocomplete', 'none');
        $('input[name="nova-poshta-method"]').change(function(){
            var tab = $(this).val();
            $('input[name="nova-poshta-city-ref"], input[name="nova-poshta-warehouse-ref"], input[name="novaposhta-city"], input[name="novaposhta-address-street"]').val('');
            if(tab == 'warehouse')
            {
                $('input[name="novaposhta-city"]').autocomplete( "option", "source", '<?=SITE_URL.$_SESSION['alias']->alias?>/getcities/warehouse');
                $('#nova-poshta-warehouse').removeClass('hide1');
                $('#nova-poshta-courier').addClass('hide1');
                $('select[name="nova-poshta-warehouse"]').attr('required', true);
                $('input[name="novaposhta-address-street"], input[name="novaposhta-address-house"]').attr('required', false);
            }
            else
            {
                $('input[name="novaposhta-city"]').autocomplete( "option", "source", '<?=SITE_URL.$_SESSION['alias']->alias?>/getcities/courier');
                $('#nova-poshta-warehouse').addClass('hide1');
                $('#nova-poshta-courier').removeClass('hide1');
                $('select[name="nova-poshta-warehouse"]').attr('required', false);
                $('input[name="novaposhta-address-street"], input[name="novaposhta-address-house"]').attr('required', true);
            }
            if (typeof setPercents === "function")
                setPercents()
        });
        $("#nova-poshta-warehouse select").change(function() {
            var option = $(this).find(':selected');
            $('input[name="nova-poshta-warehouse-ref"]').val(option.data('id'));
            $("#nova-poshta-warehouse .info").html(option.data('info')).removeClass('hide1')
            if (typeof setPercents === "function")
                setPercents();
        });

        if (typeof setPercents === "function")
            setPercents();
    }
</script>

<style>
    #nova-poshta-warehouse .info {
        border: #da291c 1px solid;
        padding: 5px 10px;
        border-radius: 2px;
        margin-top: 15px;
    }
    #cart input.ui-autocomplete-loading { background: #eee url("<?=SERVER_URL?>style/<?=$_SESSION['alias']->alias?>/ui-anim_basic_16x16.gif") right center no-repeat !important }
</style>