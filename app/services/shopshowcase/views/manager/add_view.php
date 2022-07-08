<h2>Крок 1. Основна інформація про товар</h2>
<br>
<p>Товар додається у декілька етапів:</p>
<p> <strong>Етап 1 - основна інформація</strong>, яку Ви заповнюєте на даній сторінці</p>
<p> <strong>Етап 2 - опис та додаткові характеристики товару</strong> (в залежності від обраної групи - вид тіста, декор тощо)</p>
<p> <strong>Етап 3 - корекція вигляду</strong>. Переглядуючи товар як кінцевий споживач (клієнт) Ви вносите правки у текст, зображення для того, щоб Ваша пропозиція була якнайкраще оформленою та викликала бажання її якнайшвидше придбати/замовити</p>
<br>

<form action="<?=SITE_URL.$_SESSION['alias']->alias?>/save" onsubmit="return validateForm(this)" method="POST" enctype="multipart/form-data">
	<div class="d-flex v-center">
		<label>Назва товару</label>
		<input type="text" name="name" value="<?=$this->data->re_post('name')?>" required placeholder="Назва товару">
	</div>
	<?php if($groups) { ?>
	<div class="d-flex v-center">
		<label>Група</label>
		<?php if($_SESSION['option']->ProductMultiGroup && false)
		{
			$_SESSION['alias']->js_load[] = 'assets/jstree/jstree.min.js';
			$_SESSION['alias']->js_load[] = 'js/'.$_SESSION['alias']->alias.'/init-jstree.js';
			echo '<link rel="stylesheet" href="'.SITE_URL.'assets/jstree/themes/default/style.min.css" />';
			echo '<input type="hidden" name="product_groups" id="selected" value="" />';
			$product_groups = array();
			require_once '_groupsTree.php';
		}
		else
		{
			$list = array();
			$emptyChildsList = array();
			foreach ($groups as $g) {
				$list[$g->id] = $g;
				$list[$g->id]->child = array();
				if(isset($emptyChildsList[$g->id])) {
					foreach ($emptyChildsList[$g->id] as $c) {
						$list[$g->id]->child[] = $c;
					}
				}
				if($g->parent > 0) {
					if(isset($list[$g->parent]->child)) $list[$g->parent]->child[] = $g->id;
					else {
						if(isset($emptyChildsList[$g->parent])) $emptyChildsList[$g->parent][] = $g->id;
						else $emptyChildsList[$g->parent] = array($g->id);
					}
				}
			}

			echo('<select name="group" required>');
			echo ('<option value="0" disabled selected>Оберіть кінцеву групу</option>');
			if(!empty($list))
			{
				function showList($all, $list, $parent = 0, $level = 0)
				{
					$prefix = '';
					for ($i=0; $i < $level; $i++) { 
						$prefix .= '- ';
					}
					foreach ($list as $g) if($g->parent == $parent) {
						if(empty($g->child)){
							$selected = '';
							if(isset($_GET['group']) && $_GET['group'] == $g->id) $selected = 'selected';
							if(isset($_SESSION['_POST']['group']) && $_SESSION['_POST']['group'] == $g->id) $selected = 'selected';
							echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
						} else {
							echo('<optgroup label="'.$prefix.$g->name.'">');
							$l = $level + 1;
							$childs = array();
							foreach ($g->child as $c) {
								$childs[] = $all[$c];
							}
							showList ($all, $childs, $g->id, $l);
							echo('</optgroup>');
						}
					}
					return true;
				}
				showList($list, $list);
			}
			echo('</select>');
		} ?>
	</div>
	<?php } ?>
	<div class="d-flex v-center">
		<label>Додайте реальні фото товару</label>
		<input type="file" name="photo[]" accept="image/jpg,image/jpeg,image/png" multiple id="add-images" required onchange="imagesPreview(this, '.gallery')">
	</div>
	<div class="gallery"></div>
	<?php if ($options) {
		foreach ($options as $option) {
			if($option->type_name == 'select' && $option->id != 8) { ?>
				<div class="d-flex v-center">
					<label><?=$option->name?></label>
					<select name="option-<?=$option->id?>" required>
						<?php if(!$this->data->re_post('option-'.$option->id))
						echo "<option value=\"0\" disabled selected>Оберіть потрібне</option>";
						foreach ($option->values as $value) {
							$selected = ($this->data->re_post('option-'.$option->id) == $value->id) ? 'selected' : '';
							echo "<option value={$value->id} {$selected}>{$value->name}</option>";
						} ?>
					</select>
				</div>
			<?php }
		}
	} ?>
	<div class="d-flex v-center">
		<label>Ціна у грн</label>
		<input type="number" min="1" name="price" value="<?=$this->data->re_post('price')?>" required placeholder="Ціна">
	</div>
	<div class="d-flex v-center">
        <label>Наявність</label>
        <?php if($_SESSION['option']->useAvailability)
            echo '<input type="number" min="0" name="availability" value="'.$product->availability.'" required placeholder="Наявність (одиниць)">';
        else
        {
            $where_availability_name = ['availability' => '#a.id'];
            if($_SESSION['language']) $where_availability_name['language'] = $_SESSION['language'];
            $availability = $this->db->select($_SESSION['service']->table.'_availability as a', '*', 1, 'active')
                                    ->join($_SESSION['service']->table.'_availability_name', 'name', $where_availability_name)
                                    ->order('position DESC')
                                    ->get('array');
            if($availability) { ?>
                <select name="availability">
                    <?php foreach ($availability as $a) {
                        $selected = $a->id == $this->data->re_post('availability') ? 'selected' : '';
                        echo "<option value='{$a->id}' {$selected}>{$a->name}</option>";
                    } ?>
                </select>
            <?php }
        } ?>
    </div>
	<div class="d-flex">
		<label></label>
		<div class="w100">
			<button class="btn">Зберегти і продовжити <i class="fas fa-angle-double-right"></i></button>
		</div>
	</div>
</form>

<style>
	form .d-flex, .gallery { padding: 5px }
	.gallery img { height: 120px; width: auto; padding: 5px }
	form .d-flex label { width: 20% }
	@media screen and (max-width: 576px) {
		form .d-flex label { width: 30% }
		.gallery img { height: 80px }
	}
</style>
<script type="text/javascript">
    var imagesPreview = function(input, placeToInsertImagePreview) {
        $(placeToInsertImagePreview).empty();
        if (input.files) {
            var filesAmount = input.files.length;
            for (i = 0; i < filesAmount; i++) {
                var reader = new FileReader();
                reader.onload = function(event) {
                    $($.parseHTML('<img>')).attr('src', event.target.result).appendTo(placeToInsertImagePreview);
                }
                reader.readAsDataURL(input.files[i]);
            }
        }
    };
    function validateForm(form) {
    	let valid = true;
    	$(form).find(':required').each(function(index, el) {
    		let val = $(el).val();
    		if(val == 0 || val == null)
    		{
    			$(el).addClass('required').change(function(event) {
    				$(this).removeClass('required');
    			});;
    			valid = false;
    		}
    	});
    	return valid;
    }
</script>