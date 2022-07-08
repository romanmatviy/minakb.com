<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:products_to_all']?></a>
					<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:groups_to_all']?></a>
            	</div>
                <h4 class="panel-title">Заповніть дані:</h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_group" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="id" value="0">
	                <div class="table-responsive">
	                    <table class="table table-striped table-bordered nowrap" width="100%">
						    <tr>
								<th>Фото</th>
								<td><input type="file" name="photo" class="form-control"></td>
							</tr>
							<tr>
								<th>Батьківська група</th>
								<td>
									<select name="parent" class="form-control" required>
										<option value="0">Немає</option>
										<?php if(!empty($groups)){
											$list = array();
											$emptyParentsList = array();
											foreach ($groups as $g) {
												$list[$g->id] = $g;
												$list[$g->id]->child = array();
												if(isset($emptyParentsList[$g->id])){
													foreach ($emptyParentsList[$g->id] as $c) {
														$list[$g->id]->child[] = $c;
													}
												}
												if($g->parent > 0) {
													if(isset($list[$g->parent]->child)) $list[$g->parent]->child[] = $g->id;
													else {
														if(isset($emptyParentsList[$g->parent])) $emptyParentsList[$g->parent][] = $g->id;
														else $emptyParentsList[$g->parent] = array($g->id);
													}
												}
											}
											if(!empty($list)){
												function showList($all, $list, $parent = 0, $level = 0)
												{
													$prefix = '';
													for ($i=0; $i < $level; $i++) { 
														$prefix .= '- ';
													}
													foreach ($list as $g) if($g->parent == $parent) {
														echo('<option value="'.$g->id.'">'.$prefix.$g->name.'</option>');
														if(!empty($g->child)) {
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
										} ?>
									</select>
								</td>
							</tr>
							<?php if($_SESSION['language']) foreach ($_SESSION['all_languages'] as $lang) { ?>
								<tr>
									<th>Назва <?=$lang?></th>
									<td><input type="text" name="name_<?=$lang?>" value="" class="form-control" required></td>
								</tr>
							<?php } else { ?>
								<tr>
									<th>Назва</th>
									<th><input type="text" name="name" value="" class="form-control" required></th>
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