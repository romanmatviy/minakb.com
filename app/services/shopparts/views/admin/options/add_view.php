<div class="row">
	<div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:groups_to_all']?></a>
                </div>
                <h4 class="panel-title">Заповність необхідні дані</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_option" method="POST">
						<input type="hidden" name="id" value="0">
	                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
	                        <tbody>
							<?php if($_SESSION['option']->useGroups){
								$this->load->smodel('shop_model');
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
												$selected = '';
												if(isset($_GET['group']) && $_GET['group'] == $g->id) $selected = 'selected';
												echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
												if(!empty($g->child)){
													$l = $level + 1;
													$childs = array();
													foreach ($g->child as $c) {
														$childs[] = $all[$c];
													}
													showList ($all, $childs, $g->id, $l);
												}
											}
											return true;
										}
										showList($list, $list);
									}
									echo('</select>');
								}
								echo "</td></tr>";
							}
							if($_SESSION['language']) foreach ($_SESSION['all_languages'] as $lang) { ?>
								<tr>
									<th>Назва <?=$lang?></th>
									<td><input type="text" name="name_<?=$lang?>" value="" class="form-control" required></td>
								</tr>
								<tr>
									<th>Суфікс (розмірність) <?=$lang?></th>
									<td><input type="text" name="sufix_<?=$lang?>" value="" class="form-control"></td>
								</tr>
							<?php } else { ?>
								<tr>
									<th>Назва</th>
									<td><input type="text" name="name" value="" class="form-control" required></td>
								</tr>
								<tr>
									<th>Суфікс (розмірність)</th>
									<td><input type="text" name="sufix" value="" class="form-control"></td>
								</tr>
							<?php } ?>
							<tr>
								<th>Тип</th>
								<td><select name="type" class="form-control" required>
									<?php $types = $this->db->getAllData('wl_input_types');
										foreach ($types as $type) {
											echo("<option value='{$type->id}'>{$type->name}</option>");
										}
									?>
									</select>
								</td>
							</tr>
							<tr>
								<td></td>
								<td><input type="submit" class="btn btn-sm btn-success" value="Додати"></td>
							</tr>
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>