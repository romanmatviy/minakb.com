<h1><?=$_SESSION['alias']->name?></h1>
<?=$_SESSION['alias']->text?>

<?php if($groups = $this->load->function_in_alias('shop', '__get_Groups'))
{
	echo "<ul>";
	foreach ($groups as $group) {
        $group->link = SITE_URL.$group->link;
		echo "<li><a href=\"{$group->link}\">{$group->name}</a></li>";
	}
	echo "</ul>";
} ?>