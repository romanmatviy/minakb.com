<?php

// --- Лист-сповіщення менеджеру щодо нового відгуку --- //

$comment = $data;
$subject = 'Отримано новий відгук. '.SITE_NAME;
$message = "<html><h1>{$subject}</h1><body>";
$message .= '<p>Деталі відгуку:</p>';
$message .= '<p>Отримано: <strong>'.date('d.m.Y H:i', $comment->date_add).'</strong></p>';

$message .= '<p>Контактні дані:</p>';
$message .= '<p>email: <strong>'.$comment->user_email.'</strong></p>';
$message .= '<p>ПІБ: <strong>'.$comment->user_name.'</strong></p>';
$message .= '<p>Оцінка від користувача: <strong>'.$comment->rating.'</strong></p>';
$message .= '<h4>Відгук:</h4> <p><strong><i>'.$comment->comment.'</i></strong></p>';
$message .= '<br><p>Керування відгуком <a href="'.SITE_URL.'admin/wl_comments/'.$comment->id.'">'.SITE_URL.'admin/wl_comments/'.$comment->id.'</a></body></html>';

// echo $message;exit;
?>