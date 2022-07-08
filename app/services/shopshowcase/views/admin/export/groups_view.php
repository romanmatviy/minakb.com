<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
            	<div class="panel-heading-btn">
            		<a href="<?=SITE_URL.'admin/'.$_SESSION['alias']->alias?>/export" class="btn btn-xs btn-info">Всі товари</a>
                	<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                </div>
                <h4 class="panel-title">Групи для експорту</h4>
            </div>
			<div class="panel-body">
				<div class="note note-info">
		        	<h4>Керування групами експорту для:</h4>
		        	<p>
		        		<a href="<?=SERVER_URL.'admin/'.$_SESSION['alias']->alias?>/export?groups&type=prom" class="btn btn-success btn-lg">yml для <strong>prom.ua</strong></a>
		        		<a href="<?=SERVER_URL.'admin/'.$_SESSION['alias']->alias?>/export?groups&type=google" class="btn btn-warning btn-lg">xml для <strong>Google Merchant Center</strong></a>
		        		<a href="<?=SERVER_URL.'admin/'.$_SESSION['alias']->alias?>/export?groups&type=facebook" class="btn btn-info btn-lg">xml для <strong>Facebook</strong></a> </p>
		        </div>

		        <div class="m-b-15">
					<input type="search" id="search" class="form-control" placeholder="Пошук групи" />
				</div>

		        <?php $list = $my_groups = array();
					$emptyChildsList = array();
					$export_KEY = 'export_prom';
					switch ($this->data->get('type')) {
						case 'google':
							$export_KEY = 'export_google';
							break;
						case 'facebook':
							$export_KEY = 'export_facebook';
							break;
					}
					foreach ($groups as $g) {
						if($g->$export_KEY)
							$my_groups[] = $g->id;
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
					$_SESSION['alias']->js_init[] = 'init_jstree()';
				?>

				<link rel="stylesheet" href="<?=SITE_URL?>assets/jstree/themes/default/style.min.css" />
				<script type="text/javascript">
					var my_groups = [<?=implode(',', $my_groups)?>];

					function init_jstree() {
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
									selected = [];
									for (var i = 0; i < data.selected.length; i++) {
										id = data.selected[i];
										if($.isNumeric(id))
										{
											id = parseInt(id);
											if(jQuery.inArray( id, my_groups ) === -1)
											{
												selected.push(id);
												my_groups.push(id);
											}
										}
									}
									if(selected.length > 0)
									{
										$('#saveing').css("display", "block");
										$.ajax({
									        type: "POST",
									        url: ALIAS_ADMIN_URL+'save_export_groups',
									        data: {
									        	'export': '<?=$this->data->get('type')?>',
									        	'groups': selected,
									        	'active': 1,
									        	'json': true
									        },
									        success: function(res) {
								            	$.gritter.add({title:"Керування групами експорту", text:'Групи для <strong><?=$this->data->get('type')?></strong> збережено!'});
									            $('#saveing').css("display", "none");
									        }
									    });
									}
								}
								else if(data.action == "deselect_node")
								{
									deselected = [];
									if(my_groups.length && data.selected.length)
										for (var i = my_groups.length; i >= 0; i--) {
											id = my_groups[i];
											find = false;
											for (var j = 0; j < data.selected.length; j++) {
												jd = data.selected[j];
												if($.isNumeric(jd))
												{
													jd = parseInt(jd);
													if(id === jd)
													{
														find = true;
														j = data.selected.length;
													}
												}
											}
											if(!find)
											{
												deselected.push(id);
												my_groups.splice(i, 1);
											}
										}
									
									if(deselected.length > 0)
									{
										$('#saveing').css("display", "block");
										$.ajax({
									        type: "POST",
									        url: ALIAS_ADMIN_URL+'save_export_groups',
									        data: {
									        	'export': '<?=$this->data->get('type')?>',
									        	'groups': deselected,
									        	'active': 0,
									        	'json': true
									        },
									        success: function(res) {
								            	$.gritter.add({title:"Керування групами експорту", text:'Групи для <strong><?=$this->data->get('type')?></strong> збережено!'});
									            $('#saveing').css("display", "none");
									        }
									    });
									}
								}
								
							})
							.jstree(
								{plugins: ["wholerow", "checkbox", "search"]}
							);
					};
				</script>
			</div>
		</div>
	</div>
</div>