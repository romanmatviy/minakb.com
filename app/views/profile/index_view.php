<link href="<?=SERVER_URL?>style/profile.css"rel="stylesheet" />

<main class="container">
	<h1><?=($_SESSION['alias']->alias == 'profile' || empty($_SESSION['alias']->name)) ? $this->text('Кабінет клієнта').' '.$user->name : $_SESSION['alias']->name?></h1>

	<p></p>

	<div class="d-flex m-wrap">
	    <aside class="w20 m-w100">
	        <?php $user_photo = ($user->photo) ? IMG_PATH.'profile/s_'.$user->photo : SERVER_URL.'style/images/avatar.png';
	        if(isset($_SESSION['alias']->link) && $_SESSION['alias']->link == 'profile/edit') { ?>
	            <div id="photo-block" class="mob_user_photo">
	                <img id="photo" src="<?=$user_photo?>">
	                <img id="loading" src="<?=SERVER_URL?>style/images/icon-loading.gif" >
	            </div>
	        <?php }
	            else if($user->type <= 3)
	                echo '<a href="/seller/'.$user->alias.'"><img src="'.$user_photo.'" class="mob_user_photo"></a>';
	            else
	                echo '<img src="'.$user_photo.'" class="mob_user_photo">';

	        if($this->userIs()) { ?>
	            <a href="<?= SITE_URL?>profile" <?=($_SESSION['alias']->alias == 'cart') ? 'class="active"' : ''?>><i class="fas fa-shopping-cart"></i> <?=$this->text('Мої замовлення')?></a>
	            <!-- <a href="<?= SITE_URL?>profile/<?=$user->alias?>"><i class="fa fa-user"></i> <?=$this->text('Профіль')?></a> -->

	            <?php $where_alias = array('alias' => '#ac.alias2', 'content' => '0');
	            if($_SESSION['language'])
	                $where_alias['language'] = $_SESSION['language'];
	            $this->db->select('wl_aliases_cooperation as ac', 'alias2 as id', array('alias1' => '<0', 'type' => '__link_profile'));
	            $this->db->join('wl_aliases', 'alias, admin_ico as ico', '#ac.alias2');
	            $this->db->join('wl_ntkd', 'name', $where_alias);
	            $this->db->order('alias1');

	            if($links = $this->db->get('array'))
	            foreach ($links as $link) { ?>
	                <a href="<?=SITE_URL.$link->alias?>" <?=($_SESSION['alias']->id == $link->id) ? 'class="active"' : ''?>><i class="fa <?=$link->ico?>"></i> <?=$link->name?></a>
	            <?php } ?>

	            <a href="<?=SITE_URL?>profile/edit" <?=(isset($_SESSION['alias']->link) && $_SESSION['alias']->link == 'profile/edit') ? 'class="active"' : ''?>><i class="fas fa-user-cog"></i> <?=$this->text('Редагувати профіль')?></a>

	            <a href="<?=SITE_URL?>logout"><i class="fas fa-sign-out-alt"></i> <?=$this->text('Вийти')?></a>
	        <?php } if($this->userCan()) { ?>
	            <a href="<?=SITE_URL?>admin" class="btn btn-warning"><i class="fas fa-cogs"></i> Панель керування</a>
	        <?php } ?>
	    </aside>
	    <article class="w80-5 m-w100">
	        <?php if(!empty($_SESSION['notify']->errors)) { ?>
	           <div class="alert alert-danger">
	                <span class="close" data-dismiss="alert">×</span>
	                <h4><i class="fas fa-exclamation-triangle"></i> <?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Помилка!')?></h4>
	                <p><?=$_SESSION['notify']->errors?></p>
	            </div>
	        <?php } elseif(!empty($_SESSION['notify']->success)) { ?>
	            <div class="alert alert-success">
	                <span class="close" data-dismiss="alert">×</span>
	                <h4><i class="fas fa-check"></i> <?=(isset($_SESSION['notify']->title)) ? $_SESSION['notify']->title : $this->text('Успіх!')?></h4>
	                <p><?=$_SESSION['notify']->success?></p>
	            </div>
	        <?php } unset($_SESSION['notify']);

	        if(!empty($sub_page))
	            require_once $sub_page;
	        ?>
	    </article>
	</div>
</main>