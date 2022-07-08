<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
                	<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Групи для експорту</h4>
            </div>
			<div class="panel-body">
			    <form action="<?= SITE_URL?>admin/excel" class="table-responsive" method="POST" >
			        <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
			            <thead>
			                <tr>
								<th><input type="checkbox" id="test" onclick="check()"></th>
								<th>Група</th>
								<th>Адреса</th>
			                </tr>
			            </thead>
			            <tbody>
							<?php 
								$list = array();
								$emptyParentsList = array();
								$count_level_0 = 0;
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
									if($g->parent == 0) $count_level_0++;
								}
								if(!empty($list)){
									function showList($all, $list, $count_childs, $parent = 0, $level = 0)
									{
										$pl = 15 * $level + 5;
										$ml = 10 * $level;
										foreach ($list as $g) if($g->parent == $parent) { ?>
											<tr>
												<td><input type="checkbox" name="groups[]"  value="<?=$g->id?>" <?= (empty($g->child)) ? 'checked' : ''?>></td>
												<td style="padding-left: <?=$pl?>px"><a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/groups/<?=$g->id?>-<?=$g->alias?>"><?=(!empty($g->child)) ? '<strong>'.$g->name.'</strong>' : $g->name?></a></td>
												<td><a href="<?=SITE_URL.$_SESSION['alias']->alias.'/'.$g->link?>">/<?=$_SESSION['alias']->alias.'/'.$g->link?></a></td>
											</tr>
										<?php
											if(!empty($g->child)) {
												$l = $level + 1;
												$childs = array();
												foreach ($g->child as $c) {
													$childs[] = $all[$c];
												}
												showList ($all, $childs, count($childs), $g->id, $l);
											}
										}
										return true;
									}
									showList($list, $list, $count_level_0);
								}
							?>
			            </tbody>
			        </table>
			        <div class="col-md-12 text-center">
				    	<input type="submit" class="btn btn-success" value="Згенерувати">
				    </div>
			    </form>
			</div>
		</div>
	</div>
</div>

<script>
function check() {
	if(this.checked) {
	    $(':checkbox').each(function() {
	          this.checked = true;
	    });
	    this.checked = false;
	 }
	 else 
	 {
	    $(':checkbox').each(function() {
	          	this.checked = false;
	      	});
	    this.checked = true;
	  }
}
</script>