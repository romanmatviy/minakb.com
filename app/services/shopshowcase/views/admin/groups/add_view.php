<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	
				<?php if($_SESSION['option']->useGroups == 1){ ?>
					<div class="panel-heading-btn">
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:products_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх груп</a>
					</div>
				<?php } ?>
            	
                <h4 class="panel-title">Заповніть дані:</h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_group" method="POST" enctype="multipart/form-data" class="form-horizontal">
					<input type="hidden" name="id" value="0">
					<div class="form-group">
                        <label class="col-md-3 control-label">Фото</label>
                        <div class="col-md-9">
                            <input type="file" name="photo">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">Батьківська група</label>
                        <div class="col-md-9">
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
												$selected = '';
												if(!empty($_GET['parent']) && $g->id == $_GET['parent'])
														$selected = 'selected';
												echo('<option '.(!empty($g->child) ? 'style="font-weight:bold"' : "").' value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
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
						</div>
					</div>
					<?php $name = '';
					if(!empty($_GET['name']))
						$name = $this->data->get('name');if($_SESSION['language']) foreach ($_SESSION['all_languages'] as $lang) { ?>
						<div class="form-group">
	                        <label class="col-md-3 control-label">Назва <?=$lang?></label>
	                        <div class="col-md-9">
	                            <input type="text" class="form-control" name="name_<?=$lang?>" value="<?=$name?>" placeholder="Назва <?=$lang?>" required>
	                        </div>
	                    </div>
					<?php } else { ?>
						<div class="form-group">
	                        <label class="col-md-3 control-label">Назва</label>
	                        <div class="col-md-9">
	                            <input type="text" class="form-control" name="name" value="<?=$name?>" placeholder="Назва" required>
	                        </div>
	                    </div>
					<?php } ?>
						
                    <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-9">
                            <button type="submit" class="btn btn-sm btn-success">Додати</button>
                        </div>
                    </div>
	            </form>
            </div>
        </div>
    </div>
</div>