<!-- begin #sidebar -->
<div id="sidebar" class="sidebar">
<!-- begin sidebar scrollbar -->
<div data-scrollbar="true" data-height="100%">
<!-- begin sidebar user -->
<ul class="nav">
    <li class="nav-profile">
        <div class="image">
            <a href="<?=SITE_URL?>admin/wl_users/my"><img src="<?=SITE_URL?>style/admin/images/user-<?=$_SESSION['user']->type?>.jpg" alt="" /></a>
        </div>
        <div class="info">
            <?=$_SESSION['user']->name?>
            <small>Адміністратор</small>
            <small>Сьогодні: <?=date("d.m.Y H:i")?></small>
        </div>
    </li>
</ul>
<!-- end sidebar user -->
<!-- begin sidebar nav -->
<ul class="nav">
    <li class="nav-header">Панель навігації:</li>
    <li <?=($_SESSION['alias']->alias == 'admin')?'class="active"':''?>>
        <a href="<?=SITE_URL?>admin">
            <i class="fa fa-laptop"></i>
            <span>Домашня сторінка</span>
        </a>
    </li>
    <?php if(!empty($_SESSION['option']->showInAdminWl_comments) && $this->userCan('wl_comments')) {
        $wl_comments_new = $this->db->getCount('wl_comments', array('status' => array(2, 3))); ?>
        <li <?=($_SESSION['alias']->alias == 'wl_comments')?'class="active"':''?>>
            <a href="<?=SITE_URL?>admin/wl_comments">
                <?php if($wl_comments_new) { ?>
                    <span class="badge pull-right"><?=$wl_comments_new?></span>
                <?php } ?>
                <i class="fa fa-group"></i> Відгуки та коментарі</a>
        </li>
    <?php } if( $sidebarForms = $this->db->getQuery("SELECT `name`, `title`, `table` FROM `wl_forms` WHERE `sidebar` = 1", 'array') )
        foreach($sidebarForms as $sidebarForm)
            if($this->userCan('form_'.$sidebarForm->name)) {
                $class = ($_SESSION['alias']->alias == 'wl_forms' && $this->data->uri(2) == 'info' && $this->data->uri(3) == $sidebarForm->name) ? ' class="active"' : '';
                $news = $this->db->getCount($sidebarForm->table, 1, 'new');
         ?>
            <li<?=$class?>> <a href="<?= SITE_URL.'admin/wl_forms/info/'.$sidebarForm->name?>">
                <?php if($news) { ?>
                    <span class="badge pull-right"><?=$news?></span>
                <?php } ?>
                <i class="fa fa-list-ul"></i> <?= $sidebarForm->title?>
            </a></li>
    <?php }
    $use_static_pages = false;
    $this->db->select('wl_aliases as a', 'id, alias, admin_sidebar, admin_ico', array('admin_order' => '>0'));
    $this->db->join('wl_services', 'name as service_name', '#a.service');
    $where = array('alias' => '#a.id', 'content' => '0');
    if($_SESSION['language'])
        $where['language'] = $_SESSION['language'];
    $this->db->join('wl_ntkd', 'name', $where);
    $this->db->order('admin_order DESC');
    if($wl_aliases = $this->db->get('array'))
    {
        $sub_menus = array();
        if($sub_menus_data = $this->db->getAllDataByFieldInArray('wl_options', array('alias' => '<0', 'name' => 'sub-menu')))
            foreach ($sub_menus_data as $sm) {
                $sm->alias *= -1;
                $sub_menus[$sm->alias][] = $sm;
            }
        unset($sub_menus_data);

        foreach ($wl_aliases as $wl_alias)
            if($this->userCan($wl_alias->alias))
            {
                if($wl_alias->service_name == 'static_pages')
                {
                    $use_static_pages = true;
                    continue;
                }
                if($wl_alias->id > 1)
                {
                    if(empty($wl_alias->name))
                        $wl_alias->name = $wl_alias->alias;
                }
                else
                {
                    if($use_static_pages)
                        continue;
                    $wl_alias->name = 'Головна сторінка';
                }

                if(empty($wl_alias->admin_ico))
                    $wl_alias->admin_ico = 'fa-file';

                $sub_menu = false;
                if(isset($sub_menus[$wl_alias->id]) && is_array($sub_menus[$wl_alias->id]) && !empty($sub_menus[$wl_alias->id]))
                  $sub_menu = $sub_menus[$wl_alias->id];

                if($wl_alias->admin_sidebar)
                {
                    $wl_alias->sub_menu = $sub_menu;
                    $wl_alias = $this->load->function_in_alias($wl_alias->id, '__sidebar', $wl_alias, true);
                    if(!empty($wl_alias->continue))
                        continue;
                }
    ?>
        <li class="<?=($_SESSION['alias']->alias == $wl_alias->alias)?'active':''?> <?=($sub_menu)?'has-sub':''?>">
            <?php if($sub_menu) { ?>
                <a href="javascript:;">
                    <b class="caret pull-right"></b>
                    <?php if(!empty($wl_alias->counter)) { ?>
                        <span class="badge pull-right"><?=$wl_alias->counter?></span>
                    <?php } ?>
                    <i class="fa <?=$wl_alias->admin_ico?>"></i>
                    <span><?=$wl_alias->name?></span>
                </a>
                <ul class="sub-menu">
                    <?php
                    echo('<li');
                    if($this->data->uri(2) == '') echo(' class="active"');
                    echo('><a href="'.SITE_URL.'admin/'.$wl_alias->alias.'">Головна сторінка</a>');
                    echo('</li>');
                    foreach ($sub_menu as $sm) {
                        $sb = unserialize($sm->value);
                        if(isset($sb['alias']) && $sb['name'])
                        {
                            echo('<li');
                            if($this->data->uri(2) == $sb['alias'])
                                echo(' class="active"');
                            echo('><a href="'.SITE_URL.'admin/'.$wl_alias->alias.'/'.$sb['alias'].'">'.$sb['name'].'</a>');
                            echo('</li>');
                        }
                    } ?>
                </ul>
          <?php } else { ?>
                <a href="<?=SITE_URL?>admin/<?=$wl_alias->alias?>">
                    <?php if(!empty($wl_alias->counter)) { ?>
                        <span class="badge pull-right"><?=$wl_alias->counter?></span>
                    <?php } ?>
                    <i class="fa <?=$wl_alias->admin_ico?>"></i> <span><?=$wl_alias->name?></span>
                </a>
          <?php } ?>
        </li>
    <?php }
    } 
    if($this->userCan('profile')) { ?>
        <li <?=($_SESSION['alias']->alias == 'wl_users')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_users"><i class="fa fa-group"></i> Клієнти</a></li>
    <?php } if($use_static_pages && $wl_aliases) {
            $active = $_SESSION['alias']->service == 'static_pages' || $_SESSION['alias']->alias == 'main' ? 'active' : ''; ?>
            <li class="<?=$active?> has-sub">
                <a href="javascript:;">
                    <b class="caret pull-right"></b>
                    <i class="fa fa-newspaper-o"></i>
                    <span>Статичні сторінки</span>
                </a>
                <ul class="sub-menu">
                    <?php foreach ($wl_aliases as $wl_alias)
                        if(($wl_alias->service_name == 'static_pages' || $wl_alias->id == 1) && $this->userCan($wl_alias->alias)) {
                            echo('<li');
                            if($wl_alias->alias == $_SESSION['alias']->alias) echo(' class="active"');
                            if(empty($wl_alias->name))
                                $wl_alias->name = $wl_alias->alias;
                            if($wl_alias->id == 1)
                                $wl_alias->name = 'Головна сторінка';
                            echo('><a href="'.SITE_URL.'admin/'.$wl_alias->alias.'">'.$wl_alias->name.'</a>');
                            echo('</li>'); 
                        } ?>
                </ul>
            </li>
    <?php } if($_SESSION['user']->admin == 1) {
		if(!empty($_SESSION['option']->statictic_set_page)) { ?>
            <li <?=($_SESSION['alias']->alias == 'wl_statistic')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_statistic"><i class="fa fa-area-chart"></i> Статистика сайту</a></li>
        <?php } ?>
        <li class="has-sub <?=(in_array($_SESSION['alias']->alias, array('wl_ntkd', 'wl_sitemap', 'wl_aliases', 'wl_services', 'wl_images', 'wl_register', 'wl_language_words', 'wl_forms', 'wl_mail_template', 'wl_pagespeed')) && $this->data->uri(2) != 'info')?'active':''?>">
            <a href="javascript:;">
                <b class="caret pull-right"></b>
                <i class="fa fa-cogs"></i>
                <span>Налаштування</span>
            </a>
            <ul class="sub-menu">
                <li <?=($_SESSION['alias']->alias == 'wl_ntkd')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_ntkd">SEO</a></li>
                <li <?=($_SESSION['alias']->alias == 'wl_sitemap')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_sitemap">SiteMap</a></li>
                <li <?=($_SESSION['alias']->alias == 'wl_images')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_images">Розміри зображень</a></li>
                <li <?=($_SESSION['alias']->alias == 'wl_forms')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_forms">Форми</a></li>
                <li <?=($_SESSION['alias']->alias == 'wl_mail_template')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_mail_template">Розсилка</a></li>
                <li <?=($_SESSION['alias']->alias == 'wl_aliases')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_aliases">Адреси</a></li>
                <li <?=($_SESSION['alias']->alias == 'wl_services')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_services">Сервіси</a></li>
                <li <?=($_SESSION['alias']->alias == 'wl_language_words')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_language_words">Мультимовність</a></li>
                <li <?=($_SESSION['alias']->alias == 'wl_pagespeed')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_pagespeed">Google Page Speed</a></li>
                <li <?=($_SESSION['alias']->alias == 'wl_register')?'class="active"':''?>><a href="<?=SITE_URL?>admin/wl_register">Реєстр</a></li>
            </ul>
        </li>
    <?php } ?>

    <!-- begin sidebar minify button -->
    <li><a href="javascript:;" class="sidebar-minify-btn" data-click="sidebar-minify"><i class="fa fa-angle-double-left"></i></a></li>
    <!-- end sidebar minify button -->
</ul>
<!-- end sidebar nav -->
</div>
<!-- end sidebar scrollbar -->
</div>
<div class="sidebar-bg"></div>
<!-- end #sidebar -->