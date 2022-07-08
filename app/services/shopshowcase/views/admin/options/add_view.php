<div class="row">
	<div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/options" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:options_to_all']?></a>
                	<?php if($_SESSION['option']->useGroups) { ?>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх груп</a>
					<?php } ?>
                </div>
                <h4 class="panel-title">Заповність необхідні дані</h4>
            </div>
            <div class="panel-body">
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save_option" method="POST" class="form-horizontal">
					<input type="hidden" name="id" value="0">
					<?php if($_SESSION['option']->useGroups) {
						$this->load->smodel('shop_model');
						$groups = $this->shop_model->getGroups(-1);
						if($groups)
						{
							$list = array();
							$emptyChildsList = array();
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
									else {
										if(isset($emptyChildsList[$g->parent]))
											$emptyChildsList[$g->parent][] = $g->id;
										else
											$emptyChildsList[$g->parent] = array($g->id);
									}
								}
							}
							echo ('<div class="form-group">');
	                        echo ('<label class="col-md-3 control-label">Оберіть групу</label>');
	                        echo ('<div class="col-md-9">');
							echo ('<select name="group" class="form-control">');
							echo ('<option value="0">Немає</option>');
							if(!empty($list))
							{
								function showList($all, $list, $parent = 0, $level = 0)
								{
									$prefix = '';
									for ($i=0; $i < $level; $i++) { 
										$prefix .= '- ';
									}
									foreach ($list as $g)
										if($g->parent == $parent) {
											$selected = '';
											if(isset($_GET['group']) && $_GET['group'] == $g->id)
												$selected = 'selected';
											echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
											if(!empty($g->child))
											{
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
							echo "</div></div>";
						}
					}
					$name = '';
					if(!empty($_GET['name']))
						$name = $this->data->get('name');
					if($_SESSION['language']) foreach ($_SESSION['all_languages'] as $lang) { ?>
						<div class="form-group">
	                        <label class="col-md-3 control-label">Назва <?=$lang?></label>
	                        <div class="col-md-9">
	                            <input type="text" class="form-control" name="name_<?=$lang?>" value="<?=$name?>" placeholder="Назва <?=$lang?>" required>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-md-3 control-label">Суфікс (розмірність) <?=$lang?></label>
	                        <div class="col-md-9">
	                            <input type="text" class="form-control" name="sufix_<?=$lang?>" placeholder="Суфікс (розмірність) <?=$lang?>">
	                        </div>
	                    </div>
					<?php } else { ?>
						<div class="form-group">
	                        <label class="col-md-3 control-label">Назва</label>
	                        <div class="col-md-9">
	                            <input type="text" class="form-control" name="name" value="<?=$name?>" placeholder="Назва" required>
	                        </div>
	                    </div>
	                    <div class="form-group">
	                        <label class="col-md-3 control-label">Суфікс (розмірність)</label>
	                        <div class="col-md-9">
	                            <input type="text" class="form-control" name="sufix" placeholder="Суфікс (розмірність)">
	                        </div>
	                    </div>
					<?php } ?>
					<div class="form-group">
                        <label class="col-md-3 control-label">Тип</label>
                        <div class="col-md-9">
                            <select name="type" class="form-control" required>
							<?php $types = $this->db->getAllData('wl_input_types');
								foreach ($types as $type) {
									echo("<option value='{$type->id}'>{$type->name}</option>");
								}
							?>
							</select>
                        </div>
                    </div>
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