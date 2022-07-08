<div class="row">
    <div class="col-md-6">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<?php if($_SESSION['option']->useGroups == 1){ ?>
                	<div class="panel-heading-btn">
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/all" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:articles_to_all']?></a>
						<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups" class="btn btn-info btn-xs">До всіх <?=$_SESSION['admin_options']['word:groups_to_all']?></a>
                	</div>
                <?php } ?>
                <h4 class="panel-title"><?=$_SESSION['admin_options']['word:article_add']?></h4>
            </div>
            <div class="panel-body">
            	<?php if($_SESSION['option']->useGroups) {
            		$this->load->smodel('library_model');
					$groups = $this->library_model->getGroups(-1);
					if(!$groups){
				?>
				<div class="note note-info">
					<h4>Увага! В налаштуваннях адреси не створено жодної групи!</h4>
					<p>
					    <a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/add_group">Додати групу</a>
                    </p>
				</div>
				<?php } } ?>
            	<form action="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/save" method="POST" enctype="multipart/form-data" class="form-horizontal">
					<input type="hidden" name="id" value="0">

	                <div class="form-group">
                        <label class="col-md-3 control-label">Фото</label>
                        <div class="col-md-9">
                            <input type="file" name="photo">
                        </div>
                    </div>

					<?php if($_SESSION['option']->useGroups)
					{
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
									else
									{
										if(isset($emptyChildsList[$g->parent]))
											$emptyChildsList[$g->parent][] = $g->id;
										else
											$emptyChildsList[$g->parent] = array($g->id);
									}
								}
							}
	                
                    		echo('<div class="form-group"> <label class="col-md-3 control-label">Оберіть групу</label><div class="col-md-9">');
							if($_SESSION['option']->articleMultiGroup && !empty($list))
							{
								function showList($all, $list, $parent = 0, $level = 0, $parents = array())
								{
									$ml = 15 * $level;
									foreach ($list as $g) {
										if($g->parent == $parent) 
										{
											$class = '';
											if($g->parent > 0 && !empty($parents))
											{
												$class = 'class="';
												foreach ($parents as $p) {
													$class .= ' parent-'.$p;
												}
												$class .= '"';
											}
											if(empty($g->child))
											{
												$checked = '';
												if(isset($_GET['group']) && $_GET['group'] == $g->id)
													$checked = 'checked';
												echo ('<input type="checkbox" name="group[]" value="'.$g->id.'" id="group-'.$g->id.'" '.$class.' '.$checked.'>');
												echo ('<label for="group-'.$g->id.'">'.$g->name.'</label>');
												echo ('<br>');
											}
											else
											{
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
									}
									return true;
								}
								showList($list, $list);
							}
							else
							{
								echo('<select name="group" class="form-control">');
								echo ('<option value="0">Немає</option>');
								if(!empty($list))
								{
									function showList($all, $list, $parent = 0, $level = 0)
									{
										$prefix = '';
										for ($i=0; $i < $level; $i++) { 
											$prefix .= '- ';
										}
										foreach ($list as $g) {
											if($g->parent == $parent)
											{
												if(empty($g->child))
												{
													$selected = '';
													if(isset($_GET['group']) && $_GET['group'] == $g->id) $selected = 'selected';
													echo('<option value="'.$g->id.'" '.$selected.'>'.$prefix.$g->name.'</option>');
												}
												else
												{
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
										}
										return true;
									}
									showList($list, $list);
								}
								echo('</select>');
							}
							echo ('</div></div>');
						}
					}
					if($_SESSION['language']) foreach ($_SESSION['all_languages'] as $lang) { ?>
						<div class="form-group">
	                        <label class="col-md-3 control-label">Назва <?=$lang?></label>
	                        <div class="col-md-9">
	                            <input type="text" class="form-control" name="name_<?=$lang?>" placeholder="Назва <?=$lang?>" required>
	                        </div>
	                    </div>
					<?php } else { ?>
						<div class="form-group">
	                        <label class="col-md-3 control-label">Назва</label>
	                        <div class="col-md-9">
	                            <input type="text" class="form-control" name="name" placeholder="Назва" required>
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




<script>
	function setChilds (parent) {
		if($('#group-'+parent).prop('checked')){
			$('.parent-'+parent).prop('checked', true);
		} else {
			$('.parent-'+parent).prop('checked', false);
		}
	}
</script>