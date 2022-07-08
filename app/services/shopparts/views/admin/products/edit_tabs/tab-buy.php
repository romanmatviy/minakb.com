<?php

$this->db->select('s_cart_products as cp', '*', $product->id, 'product');
$this->db->join('s_cart as c', '', '#cp.cart');
$this->db->join('s_cart_status as cs', 'name as cart_status', '#c.status');
$this->db->join('wl_users as u', 'name as user_name, email as user_email', '#cp.user');
$this->db->join('s_shopstorage', 'name as storage_name', '#cp.storage_alias');
$this->db->order('id DESC');
$this->db->limit(30);
$history = $this->db->get('array');

if($history)
{
    echo('<div class="table-responsive"><table class="table table-condensed table-bordered">');
    echo("<tr>");
    echo("<td>Дата</td>");
    echo("<td>Склад</td>");
    echo("<td>Замовлення</td>");
    echo("<td>Клієнт</td>");
    echo("<td>Кількість</td>");
    echo("<td>Ціна продано (закуп) за од.</td>");
    echo("<td>Сума продано (закуп)</td>");
    echo("<td>Статус товару</td>");
    echo("</tr>");
    foreach ($history as $invoice) {
        echo("<tr>");
        echo("<td>".date('d.m.Y H:i', $invoice->date)."</td>");
        echo("<td>{$invoice->storage_name}</td>");
        echo("<td><a href=\"".SITE_URL."admin/cart/{$invoice->cart}\" target=\"_blank\">{$invoice->cart} ({$invoice->cart_status})</a></td>");
        echo("<td><a href=\"".SITE_URL."admin/wl_users/{$invoice->user_email}\" target=\"_blank\">#{$invoice->user} {$invoice->user_name}</a></td>");
        echo("<td><strong>{$invoice->quantity}</strong></td>");
        echo("<td><strong>\${$invoice->price}</strong> (\${$invoice->price_in})</td>");
        echo("<td><strong>$".$invoice->price*$invoice->quantity."</strong> ($".$invoice->price_in*$invoice->quantity.")</td>");
        switch ($invoice->status) {
            case 1:
                echo("<td>Замовлено</td>");
                break;
            case 2:
                echo("<td>Скасовано</td>");
                break;
            case 3:
                echo("<td>Повернено</td>");
                break;
        }
        echo("</tr>");
    }
    echo('</table></div>');
}
?>
<p>Виведено останні 30 записів</p>