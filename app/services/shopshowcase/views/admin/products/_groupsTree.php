<?php
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
			if(isset($emptyChildsList[$g->parent])) $emptyChildsList[$g->parent][] = $g->id;
			else $emptyChildsList[$g->parent] = array($g->id);
		}
	}
}

if(!empty($list))
{
	function showList($product_group, $all, $list, $parent = 0, $parents = array())
	{
		foreach ($list as $g) if($g->parent == $parent) {
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
	showList($product_groups, $list, $list);
	echo('</ul></div>');
}
/*
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
<script type="text/javascript">
	$(function () { 
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
			    $('#selected').val(data.selected);
			})
			.jstree(
				{plugins: ["wholerow", "checkbox", "search"]}
			); });
</script>*/ ?>