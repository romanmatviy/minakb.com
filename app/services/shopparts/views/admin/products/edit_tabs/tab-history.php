<?php

$this->db->select('products_update_history as h', '*', $product->id, 'product');
$this->db->join('s_shopstorage_updates as u', 'date, file', '#h.update');
$this->db->join('s_shopstorage', 'name as storage_name', '#u.storage');
$this->db->order('date DESC', 'u');
$this->db->limit(30);
$history = $this->db->get('array');


if($history)
{
    echo('<div class="table-responsive"><table class="table table-condensed table-bordered">');
    echo("<tr>");
    echo("<td>Дата</td>");
    echo("<td>Склад</td>");
    echo("<td>Файл</td>");
    echo("<td>Ціна до</td>");
    echo("<td>Ціна після</td>");
    echo("<td>Кількість до</td>");
    echo("<td>Кількість після</td>");
    echo("</tr>");
    foreach ($history as $invoice) {
        echo("<tr>");
        echo("<td>".date('d.m.Y H:i', $invoice->date)."</td>");
        echo("<td>{$invoice->storage_name}</td>");
        echo("<td>{$invoice->file}</td>");
        echo("<td>$ {$invoice->price_old}</td>");
        echo("<td><strong>$ {$invoice->price_new}</strong></td>");
        echo("<td>{$invoice->amount_old}</td>");
        echo("<td><strong>{$invoice->amount_new}</strong></td>");
        echo("</tr>");
    }
    echo('</table></div>');
}
?>
<p>Виведено останні 30 записів</p>