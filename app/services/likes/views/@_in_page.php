<?php $likes = array();
$likes['content'] = $product->id;
$likes['alias'] = $_SESSION['alias']->id;
$likes['name'] = $_SESSION['alias']->name;
$likes['link'] = $_SESSION['alias']->link;
$likes['image'] = (isset($_SESSION['alias']->images[0])) ? $_SESSION['alias']->images[0] : false;
$likes['additionall'] = "<p>{$product->price} грн</p>";
$this->load->function_in_alias('likes', '__show_Like_Btn', $likes); ?>