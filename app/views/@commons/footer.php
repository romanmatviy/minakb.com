<footer class="footer d-flex">
    <div class="container">
        <div class="logo">
            <img width="150" height="150" src="style/images/logo.svg"
                alt="<?= $this->text('Міністерство акумуляторів', 0) ?>">
        </div>

        <div class="footer-content">
            <div class="footer-content__column">
                <ul class="items">
                    <li class="item">
                        <?= $this->text('Телефон', 0) ?>:
                        <br>
                        <a href="tel:+380 97 122 22 00">+380 97 122 22 00</a>
                    </li>
                </ul>
            </div>
        </div>


        <a target="_blank" href="<?= SITE_URL ?>"
            class="d-flex v-center"><?= $this->text('MA', 0); ?> &copy; <?=date('Y')?> <?= $this->text('All Rights Reserved', 0); ?></a>

        <a target="_blank" href="https://webspirit.com.ua/" class="d-flex v-center"><?= $this->text('Розроблено', 0); ?>
            WebSpirit
            Creative Agency <img
                src="<?=SERVER_URL?>style/admin/images/WebSpirit_logo_mini.png"
                style="height:30px; padding-left: 15px" alt="WebSpirit Creative Agency"></a>
        <!-- <span class="d-flex v-center"><img
                src="<?=SERVER_URL?>style/admin/images/whitelion-black.png"
        style="height:30px" alt="White Lion CMS"> &copy; <?=date('Y')?> White Lion CMS All Right
        Reserved</span> -->
    </div>
</footer>