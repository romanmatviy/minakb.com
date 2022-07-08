<?php if(isset($_SESSION['notify'])){ 
require APP_PATH.'views/admin/notify_view.php';
} ?>
      
<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<?php if($_SESSION['option']->useGroups == 1){ ?>
                	<div class="panel-heading-btn">
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:products_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:groups_to_all']?></a>
                	</div>
                <?php } ?>
                <h4 class="panel-title"><?=$_SESSION['admin_options']['word:product_add']?></h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="id" value="0">
	                <div class="table-responsive">
	                    <table class="table table-striped table-bordered nowrap" width="100%">
                    		<tr>
								<th>Артикул</th>
								<td><input type="text" name="article" value="<?=(isset($_SESSION['post']['article'])) ? $_SESSION['post']['article'] : ''?>" class="form-control" required></td>
							</tr>

							<tr>
								<th>Виробник</th>
								<td>
									<select name="manufacturer" class="form-control" required>
										<?php if($manufacturers) foreach ($manufacturers as $manufacturer) {
											$selected = (isset($_SESSION['post']['manufacturer']) && $manufacturer->id == $_SESSION['post']['manufacturer']) ? 'selected' : '';
											echo "<option value=\"{$manufacturer->id}\" {$selected}>{$manufacturer->name}</option>";
										} ?>
									</select>
								</td>
							</tr>

							<tr>
								<th>Оригінальність</th>
								<td>
									<input type="radio" name="orign" value="1" <?=(isset($_SESSION['post']['orign']) && $_SESSION['post']['orign'] == 1)?'checked':''?> id="orign-1" required><label for="orign-1">Оригінальна запчастина</label>
									<input type="radio" name="orign" value="0" <?=(isset($_SESSION['post']['orign']) && $_SESSION['post']['orign'] == 0)?'checked':''?> id="orign-0"><label for="orign-0">Замінник оригіналу</label>
								</td>
							</tr>

							<?php if($_SESSION['option']->useGroups)
							{
								$groups = $this->shop_model->getGroups(-1);
								if($groups){

									$list = array();
									$emptyChildsList = array();
									foreach ($groups as $g) {
										$list[$g->id] = $g;
										$list[$g->id]->child = array();
										if(isset($emptyChildsList[$g->id])){
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

									echo "<tr><th>Оберіть групу</th><td>";
									if($_SESSION['option']->ProductMultiGroup && !empty($list)){
										function showList($all, $list, $parent = 0, $level = 0, $parents = array())
										{

											$ml = 15 * $level;
											foreach ($list as $g) if($g->parent == $parent) {
												$class = '';
												if($g->parent > 0 && !empty($parents)){
													$class = 'class="';
													foreach ($parents as $p) {
														$class .= ' parent-'.$p;
													}
													$class .= '"';
												}
												if(empty($g->child)){
													$checked = '';
													if(isset($_GET['group']) && $_GET['group'] == $g->id) $checked = 'checked';
													echo ('<input type="checkbox" name="group[]" value="'.$g->id.'" id="group-'.$g->id.'" '.$class.' '.$checked.'>');
													echo ('<label for="group-'.$g->id.'">'.$g->name.'</label>');
													echo ('<br>');
												} else {
													echo ('<input type="checkbox" id="group-'.$g->id.'" '.$class.' onChange="setChilds('.$g->id.')">');
													echo ('<label for="group-'.$g->id.'">'.$g->name.'</label>');
													$l = $level + 1;
													$childs = array();
													foreach ($g->child as $c) {
														$childs[] = $all[$c];
													}
													$ml = 15 * $l;
													echo ('<div style="margin-left: '.$ml.'px">');
													$parents2 = $parents;
													$parents2[] = $g->id;
													showList ($all, $childs, $g->id, $l, $parents2);
													echo('</div>');
												}
											}

											return true;
										}
										showList($list, $list);
									} else {
										echo('<select name="group" class="form-control">');
										echo ('<option value="0">Немає</option>');
										if(!empty($list)){
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
														if(isset($_SESSION['post']['group']) && $_SESSION['post']['group'] == $g->id) $selected = 'selected';
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
									}
									echo "</td></tr>";
								} else { ?>
									<div class="note note-info">
										<h4>Увага! В налаштуваннях адреси не створено жодної групи!</h4>
										<p>
										    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_group">Додати групу</a>
		                                </p>
									</div>
								<?php }
							} ?>

							<tr>
								<th>Назва</th>
								<td><input type="text" name="name" value="<?=(isset($_SESSION['post']['name'])) ? $_SESSION['post']['name'] : ''?>" class="form-control" required></td>
							</tr>

							<?php if(empty($storages)) { ?>
								<tr>
									<th>Ціна</th>
									<td><input type="number" name="price" value="<?=(isset($_SESSION['post']['price'])) ? $_SESSION['post']['price'] : 0?>" min="0" step="0.01" class="form-control" required></td>
								</tr>
							<?php } ?>
							
							<tr>
								<td></td>
								<td><input type="submit" class="btn btn-sm btn-success" value="Додати"></td>
							</tr>
	                    </table>
	                </div>
	            </form>
            </div>
        </div>
    </div>
</div>
<?php unset($_SESSION['post']) ?>



<script>
	function setChilds (parent) {
		if($('#group-'+parent).prop('checked')){
			$('.parent-'+parent).prop('checked', true);
		} else {
			$('.parent-'+parent).prop('checked', false);
		}
	}
</script>