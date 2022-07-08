<form>
    <div class="filter">
        <h6>Назва страви</h6>
        <input type="search" name="name" value="<?=$this->data->get('name')?>" placeholder="торт, борщ..">
    </div>
<?php $this->load->js("js/{$_SESSION['alias']->alias}/catalog.js");
$open = true; // []; // $filter->ids ?? true => all filters
$type_2 = []; // $filter->ids
$type_3 = []; // $filter->ids
$positions = [];
foreach ($filters as $filter)
    $positions[] = $filter->position;
array_multisort($positions, $filters);
foreach ($filters as $filter) {
    $for_sort = [];
    foreach ($filter->values as $value) {
        $for_sort[] = $this->shop_model->tofloat($value->name);
    }
    array_multisort($for_sort, SORT_ASC, $filter->values);
    unset($for_sort);

    if(!empty($filter->values)) {
        $class_i = (is_bool($open) && $open || is_array($open) && in_array($filter->id, $open)) ? 'down' : 'up';
        if(count($_GET) > 1)
            $class_i = 'up';
        if(isset($_GET[$filter->alias]))
            $class_i = 'down';
        $class_size = '';
        if(in_array($filter->id, $type_2)) $class_size = 'two';
        if(in_array($filter->id, $type_3)) $class_size = 'three';
        $count = count($filter->values);
        $i = 0;
        ?>
        <div class="filter">
            <i class="fas fa-angle-<?=$class_i?> pull-right angle"></i>
            <h6><?=$filter->name?></h6>
            <div class="options <?=$class_size?> <?=($class_i == 'down') ? '' : 'hide'?>">
                <?php foreach ($filter->values as $value) {
                    $checked = '';
                    if(isset($_GET[$filter->alias])
                         && (is_array($_GET[$filter->alias]) && in_array($value->id, $_GET[$filter->alias]) || is_numeric($_GET[$filter->alias]) && $_GET[$filter->alias] == $value->id)) $checked = 'checked';
                    if($i++ > 19 && empty($_GET[$filter->alias]))
                        echo '<div class="more hide">';
                    ?>
                    <label <?=($checked)?'class="active"':''?>>
                        <input type="checkbox" name="<?=$filter->alias?>[]" value="<?=$value->id?>" <?=$checked?> >
                        <i class="far fa-<?=($checked)?'check-square':'square'?>"></i>
                        <?php if(!empty($value->photo))
                        {
                            echo '<img src="'.$value->photo.'" alt="'.$value->name.'" title="'.$value->name.'" >';
                            if(!in_array($filter->id, $type_2) && !in_array($filter->id, $type_3))
                                echo $value->name;
                        }
                        else
                            echo $value->name;
                        ?>
                        <button><i class="fas fa-search"></i> <?=$this->text('Фільтрувати')?></button>
                    </label>
                <?php if($i > 20 && empty($_GET[$filter->alias])) echo "</div>";
                    } ?>
                <div class="clear"></div>
                <?php if($count > 20 && empty($_GET[$filter->alias])) { ?>
                    <div class="more">
                        <i class="fas fa-angle-down"></i>
                        <span class="open"><?=$this->text('Ще').' '.($count - 20)?></span>
                        <span class="close hide"><?=$this->text('Згорнути').' '.($count - 20)?></span>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } } ?>
    <div class="d-flex wrap actions">
        <button><i class="fas fa-search"></i> <?=$this->text('Фільтрувати')?></button>
        <button type="reset"><i class="fas fa-broom"></i> <?=$this->text('Очистити')?></button>
    </div>
</form>