<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title">Новозареєстровані користувачі/клієнти
            <span class="pull-right label label-success">Всіх підтверджених користувачів: <?=$this->db->getCount('wl_users', 1, 'status')?></span>
        </h4>
    </div>
    <ul class="registered-users-list clearfix">
        <?php if($users = $this->db->getAllDataByFieldInArray('wl_users', array('status' => '!3'), 'id DESC LIMIT 8'))
            $count = 0;
            foreach ($users as $user) {
                $link = 'javascript:;';
                if($this->userCan('profile')) $link = SITE_URL.'admin/wl_users/'.$user->email;
        ?>
            <li <?= $count == 4 ? 'style="clear:both"' : ''?>>
                <a href="<?=$link?>"><img src="<?= ($user->photo)? IMG_PATH.'profile/'.$user->photo : SERVER_URL.'style/admin/images/user-'.$user->type.'.jpg'  ?>" alt="<?=$user->name?>"></a>
                <h4 class="username text-ellipsis">
                    <?=$user->name?>
                    <small><?=date('d.m.Y H:i', $user->registered)?></small>
                </h4>
            </li>
        <?php $count++; } ?>
    </ul>
    <?php if($this->userCan('profile')) { ?>
        <div class="panel-footer text-center">
            <a href="<?=SITE_URL?>admin/wl_users" class="text-inverse">До всіх користувачів/клієнтів</a>
        </div>
    <?php } ?>
</div>