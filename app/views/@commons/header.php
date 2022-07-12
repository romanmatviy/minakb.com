<header class="container d-flex">
    <a href="<?=SITE_URL?>" class="d-flex v-center">
        <img src="<?=SERVER_URL?>style/images/logo.jpg"
            style="height:30px" title="<?= SITE_NAME ?>">
        <span><?= SITE_NAME ?></span>
    </a>
    <nav class="menu">
        <a href="<?=SITE_URL?>"><?= $this->text('Головна', 0); ?></a>
        <a href="<?=SITE_URL?>about_us"><?= $this->text('Про нас', 0); ?></a>
        <a href="<?=SITE_URL?>contact"><?= $this->text('Контакти', 0); ?></a>

        <?php if ($this->userIs()) { ?>
        <a href="<?=SITE_URL?>profile"><?= $this->text('Кабінет', 0); ?></a>
        <?php if ($this->userCan()) { ?>
        <a href="<?=SITE_URL?>admin"><?= $this->text('Адмін', 0); ?></a>
        <?php } ?>
        <a href="<?=SITE_URL?>logout"><?= $this->text('Вийти', 0); ?></a>
        <?php } else { ?>
        <a href="<?=SITE_URL?>login"><?= $this->text('Увійти', 0); ?></a>
        <?php }
            //$this->load->function_in_alias('cart', '__show_minicart');
        ?>

        <div class="header-langs">
            <a href="<?=SITE_URL_UA?>"><?= $this->text('УКР', 0); ?></a> | <a
                class="ml-0" href="<?=SITE_URL_EN?>">ENG</a>
        </div>
    </nav>
</header>