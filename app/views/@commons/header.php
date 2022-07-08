<header class="container d-flex">
    <a href="<?=SITE_URL?>" class="d-flex v-center">
        <img src="<?=SERVER_URL?>style/admin/images/whitelion-black.png" style="height:30px" title="White Lion CMS <?=WL_VERSION?>">
        <span>White Lion CMS <?=WL_VERSION?></span>
    </a>
    <nav>
        <a href="<?=SITE_URL?>"><?= $this->text('Головна', 0); ?></a>
        <?php if($this->userIs()) { ?>
            <a href="<?=SITE_URL?>profile"><?= $this->text('Кабінет', 0); ?></a></li>
            <?php if($this->userCan()) { ?>
            <a href="<?=SITE_URL?>admin">ADMIN</a></li>
            <?php } ?>
            <a href="<?=SITE_URL?>logout"><?= $this->text('Вийти', 0); ?></a></li>
        <?php } else { ?>
            <a href="<?=SITE_URL?>login"><?= $this->text('Увійти', 0); ?></a></li>
        <?php } 
            //$this->load->function_in_alias('cart', '__show_minicart');
        ?>
    </nav>
</header>