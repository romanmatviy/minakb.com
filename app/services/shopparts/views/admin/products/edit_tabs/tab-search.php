<?php

$history = $this->db->getQuery("SELECT psh.*, wl_users.name as user_name, wl_users.email as user_email, wl_user_info.city as user_city FROM `{$_SESSION['service']->table}_search_history` AS psh LEFT JOIN `wl_users` ON wl_users.id = psh.user LEFT JOIN `wl_user_info` ON wl_user_info.user = psh.user WHERE psh.product_id = '{$product->id}' OR psh.product_article = '{$product->article}' ORDER BY psh.last_view DESC LIMIT 30", 'array');

if($history)
{
    echo('<div class="table-responsive"><table class="table table-condensed table-bordered">');
    echo("<tr>");
    echo("<td>Дата</td>");
    echo("<td>Останній перегляд</td>");
    echo("<td>Переглядів</td>");
    echo("<td>Клієнт</td>");
    echo("</tr>");
    foreach ($history as $search) {
        echo("<tr>");
        echo("<td>".date('d.m.Y', $search->date)."</td>");
        echo("<td>".date('d.m.Y H:i', $search->last_view)."</td>");
        echo("<td>{$search->count_per_day}</td>");
        echo("<td><a href=\"".SITE_URL."admin/wl_users/{$search->user_email}\">#{$search->user} {$search->user_name}</td>");
        echo("</tr>");
    }
    echo('</table></div>');
    echo "<p>Виведено останні 30 записів</p>";
}
else
    echo "Дані відсутні";
?>