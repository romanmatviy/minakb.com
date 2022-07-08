<div class="row">
	<div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/promo" class="btn btn-info btn-xs"><i class="fa fa-tasks" aria-hidden="true"></i> До всіх акцій</a>
                </div>
                <h4 class="panel-title">Дані акції</h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_promo" method="POST" class="form-horizontal">
					<input type="hidden" name="id" value="<?=$promo->id?>">
					<div class="form-group">
                        <label class="col-md-3 control-label">Поточний статус</label>
                        <div class="col-md-9">
                        	<select name="status" class="form-control">
                        		<?php $options = [0 => 'Відключено', 1 => 'Активна'];
                        		foreach ($options as $i => $name) {
                        			$attr = $promo->status == $i ? 'selected' : '';
                        		 	echo '<option value="'.$i.'"'.$attr.'>'.$name.'</option>';
                        		 } ?>
                        	</select>
                        </div>
                    </div>
					<div class="form-group">
                        <label class="col-md-3 control-label">Початок дії акції</label>
                        <div class="col-md-9">
                        	<div class="col-xs-6">
                        		<input type="text" class="form-control datepicker" name="from_date" value="<?=$this->data->re_post('from_date', date('d.m.Y', $promo->from))?>" required autocomplete="off">
                        	</div>
                            <div class="col-xs-6">
                            	<input type="time" class="form-control" name="from_time" value="<?=$this->data->re_post('from_time', date('H:i', $promo->from))?>" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Кінець акції</label>
                        <div class="col-md-9">
                        	<div class="col-xs-6">
                        		<input type="text" class="form-control datepicker" name="to_date" value="<?=$this->data->re_post('to_date', date('d.m.Y', $promo->to))?>" required autocomplete="off">
                        	</div>
                            <div class="col-xs-6">
                            	<input type="time" class="form-control" name="to_time" value="<?=$this->data->re_post('to_time', date('H:i', $promo->to))?>" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Відсоток знижки</label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="percent" min="1" step="0.5" value="<?=$this->data->re_post('percent', $promo->percent)?>" required autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Інформація</label>
                        <div class="col-md-9">
                        	<textarea name="info" rows="3" class="form-control"><?=$this->data->re_post('info', $promo->info)?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-warning"><i class="fa fa-floppy-o" aria-hidden="true"></i> Зберегти</button>
                        </div>
                    </div>
				</form>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel p-15">
			<p>Створено: <strong><?=date('d.m.Y H:i', $promo->date_add)?></strong></p>
			<p>Редаговано: <strong><?=date('d.m.Y H:i', $promo->date_edit)?>
				<?php if($promo->manager_edit)
						if($manager = $this->db->getAllDataById('wl_users', $promo->manager_edit))
							echo '<a href="'.SITE_URL.'admin/wl_users/'.$manager->email.'">#'.$manager->id.' '.$manager->name.'</a>'; ?>
			</strong></p>
			<hr>
			<h4>Акційних товарів: <strong id="promo_productsCount"><?=$promo->productsCount?></strong></h4>
			<button id="promo_productsGet" class="btn btn-success" <?=$promo->productsCount ? '' : 'disabled'?>><i class="fa fa-check-square-o" aria-hidden="true"></i> Показати товари, які беруть участь в акції</button>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h4 class="panel-title">Обрати товари, які братимуть участь в акції</h4>
            </div>
            <div class="panel-body">
            	<div class="m-b-15">
					<input type="search" id="search" class="form-control" placeholder="Пошук групи" />
				</div>

		        <?php $list = $my_groups = $emptyChildsList = array();
				if (!empty($groups))
					foreach ($groups as $g) {
						$list[$g->id] = $g;
						$list[$g->id]->child = array();
						if(isset($emptyChildsList[$g->id]))
							foreach ($emptyChildsList[$g->id] as $c) {
								$list[$g->id]->child[] = $c;
							}
						if($g->parent > 0)
						{
							if(isset($list[$g->parent]->child))
								$list[$g->parent]->child[] = $g->id;
							else
							{
								if(isset($emptyChildsList[$g->parent])) $emptyChildsList[$g->parent][] = $g->id;
								else $emptyChildsList[$g->parent] = array($g->id);
							}
						}
					}

					if(!empty($list))
					{
						function showList($product_group, $all, $list, $parent = 0, $parents = array())
						{
							foreach ($list as $g)
								if($g->active && $g->parent == $parent) {
									if(empty($g->child))
									{
										$selected = '';
										if(in_array($g->id, $product_group))
											$selected = ', "selected":true';

										echo ('<li id="'.$g->id.'" data-jstree=\'{"icon":"none"'.$selected.'}\'>'.$g->name.'</li>');
										// echo ('<li id="'.$g->id.'" data-jstree=\'{"icon":"jstree-file"'.$selected.'}\'>'.$g->name.' '.$g->id.'</li>');
									}
									else
									{
										echo ('<li>'.$g->name);
										$childs = array();
										foreach ($g->child as $c) {
											$childs[] = $all[$c];
										}
										echo ('<ul>');
										$parents2 = $parents;
										$parents2[] = $g->id;
										showList ($product_group, $all, $childs, $g->id, $parents2);
										echo('</ul>');
										echo ('</li>');
									}
								}

							return true;
						}
						echo (' <div id="jstree"><ul>');
						showList($my_groups, $list, $list);
						echo('</ul></div>');
					}
					$_SESSION['alias']->js_load[] = 'assets/jstree/jstree.min.js';
				?>
				<link rel="stylesheet" href="<?=SITE_URL?>assets/jstree/themes/default/style.min.css" />
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel p-15" id="promo_products_list">
			<?php if (!empty($groups)) { ?>
				<i class="fa fa-arrow-circle-left" aria-hidden="true"></i> Оберіть групу
			<?php } else {
				$res = '<h4>Всі товари</h4>';
				$_SESSION['option']->paginator_per_page = 0;
				if ($products = $this->shop_model->getProducts())
					foreach ($products as $product)
					{
						$attr = $title = '';
						if ($product->promo > 0)
						{
							if ($product->promo == $promo->id)
								$attr = 'checked';
							else
							{
								$title = 'title="Інша акція" class="text-danger"';
								$attr = 'disabled';
								$product->name .= ' (<a href="' . SITE_URL . 'admin/' . $_SESSION['alias']->alias . '/promo/' . $product->promo . '" class="btn btn-xs btn-info">Акція #' . $product->promo . '</a>';
							}
						}
						else if ($product->old_price > $product->price)
						{
							$title = 'title="Власна акційна ціна" class="text-success"';
							$attr = 'disabled';
							$product->name .= ' (' . $product->old_price . ' ' . $product->currency . ' => ' . $product->price . ')';
						}
						$res .= '<label ' . $title . '><input type="checkbox" value="' . $product->id . '" ' . $attr . '> <strong>' . $product->article_show . '</strong> ' . $product->name . ' <a href="' . SITE_URL . 'admin/' . $_SESSION['alias']->alias . '/search?id=' . $product->id . '"><small>Детальніше</small></a></label>';
					}
				echo $res;
			} ?>
		</div>
	</div>
</div>

<style>
	#promo_products_list label { display: block; margin-bottom: 5px }
</style>
<link  href="<?=SERVER_URL?>assets/datepicker/dist/datepicker.min.css" rel="stylesheet">
<?php
    $_SESSION['alias']->js_load[] = "assets/datepicker/dist/datepicker.min.js";
   	$_SESSION['alias']->js_load[] = "assets/datepicker/i18n/datepicker.uk.js";
    $_SESSION['alias']->js_init[] = "promo_add_init();";
	if (empty($groups))
		$_SESSION['alias']->js_init[] = "init_saveProduct();";
 ?>
<script>
	function promo_add_init() {
		var datepicker_config = { language: 'uk', 'startDate': '<?=date('d.m.Y')?>', 'autoHide':true };
		$('input.datepicker').datepicker(datepicker_config).on('pick.datepicker', function (e) {
	        if(e.target.name == 'from_date')
	        {
	        	$('input.datepicker[name=to_date]').datepicker('setStartDate', e.date);
	        	$('input.datepicker[name=to_date]').datepicker('show');
	        }
	    });

	    var to = false;

		$('#search').keyup(function () {
			if(to) { clearTimeout(to); }
			to = setTimeout(function () {
				var v = $('#search').val();
				$('#jstree').jstree(true).search(v);
			}, 250);
		});

		$('#jstree')
			.on("changed.jstree", function (e, data) {
				if(data.action == "select_node")
				{
					var id = data.node.id;
					if($.isNumeric(id))
					{
						$('#saveing').css("display", "block");
						$.ajax({
					        type: "POST",
					        url: ALIAS_ADMIN_URL+'promo_getGroupProducts',
					        data: {
					        	'group': id,
					        	'promo': <?=$promo->id?>,
					        	'json': true
					        },
					        success: function(res) {
				            	$('#promo_products_list').html(res);
				            	init_saveProduct();
					            $('#saveing').css("display", "none");
					        },
					        error: function(){
					            $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
					            $('#saveing').css("display", "none");
					        },
					        timeout: function(){
					            $.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
					            $('#saveing').css("display", "none");
					        }
					    });
					}
				}
			})
			.jstree(
				{plugins: ["wholerow", "search"]}
			);

		$('#promo_productsGet').click(function () {
			$('#saveing').css("display", "block");
				$.ajax({
			        type: "POST",
			        url: ALIAS_ADMIN_URL+'promo_getProducts',
			        data: {
			        	'promo': <?=$promo->id?>,
			        	'json': true
			        },
			        success: function(res) {
		            	$('#promo_products_list').html(res);
		            	init_saveProduct();
			            $('#saveing').css("display", "none");
			        },
			        error: function(){
			            $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
			            $('#saveing').css("display", "none");
			        },
			        timeout: function(){
			            $.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
			            $('#saveing').css("display", "none");
			        }
			    });
		})
	}
	function init_saveProduct() {
		$('#promo_products_list input').change(function () {
			active = 0;
			if(this.checked)
				active = 1;
			productId = this.value;
			if(productId > 0)
			{
				$('#saveing').css("display", "block");
				$.ajax({
			        type: "POST",
			        url: ALIAS_ADMIN_URL+'promo_saveProduct',
			        data: {
			        	'product': productId,
			        	'promo': <?=$promo->id?>,
			        	'active': active,
			        	'json': true
			        },
			        success: function(res) {
			        	$('#promo_productsCount').text(res.count);
			        	if(res.count > 0)
			        		$('#promo_productsGet').attr('disabled', false);
			        	else
			        		$('#promo_productsGet').attr('disabled', true);
			        	if(res.save)
			        		$.gritter.add({title:"Акції",text:"Дані оновлено"});
			        	else
			        		$.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
			            $('#saveing').css("display", "none");
			        },
			        error: function(){
			            $.gritter.add({title:"Помилка!",text:"Помилка! Спробуйте ще раз!"});
			            $('#saveing').css("display", "none");
			        },
			        timeout: function(){
			            $.gritter.add({title:"Помилка!",text:"Помилка: Вийшов час очікування! Спробуйте ще раз!"});
			            $('#saveing').css("display", "none");
			        }
			    });
			}
		})
	}
</script>