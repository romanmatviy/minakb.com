<div id="tabs">
    <ul class="d-flex">
        <li><a href="#main"><?=$this->text('Загальні дані')?></a></li>
        <li><a href="#security"><?=$this->text('Безпека')?></a></li>
    </ul>

    <div id="main">
        <form action="<?= SITE_URL?>profile/saveUserInfo" method="POST">
            <table>
                <tbody>
                    <tr>
                        <td><?=$this->text("Моє ім'я")?></td>
                        <td><i class="fas fa-pencil-alt right" data-name="name" data-required="true"></i> <?=$user->name?></td>
                    </tr>
                    <tr>
                        <td><?=$this->text('Мій')?> email</td>
                        <td><?=$user->email?></td>
                    </tr>
                    <?php $showSave = false;
                     $info = array('phone' => 'Номер телефону', 'company_info' => 'Компанія');
                        foreach($info as $key => $title) { ?>
                            <tr>
                                <td><?=$this->text($title)?></td>
                                <td>
                                    <?php if(isset($user->info[$key])) {
                                        if($key == 'phone') $user->info[$key] = $this->data->formatPhone($user->info[$key]);
                                        ?>
                                        <i class="fas fa-pencil-alt right" data-name="<?= $key ?>"></i> <?=$user->info[$key]?>
                                    <?php } else { $showSave = true; ?>
                                        <input type='text' name='<?=$key?>'>
                                    <?php } ?>
                                </td>
                            </tr>
                    <?php } ?>
                    <tr>
                        <td><?=$this->text('Додати/оновити фото')?></td>
                        <td id="fileupload"><input onchange="show_image(this)" type="file" name="photos"></td>
                    </tr>
                    <?php if($_SESSION['language']) { ?>
                    <tr>
                        <td><?=$this->text('Мова користувача')?></td>
                        <td>
                            <select name="language" onchange="$('main #tabs #main button.hide').removeClass('hide')">
                                <?php foreach ($_SESSION['all_languages'] as $lang) {
                                    $selected = $lang == $user->language ? 'selected' : '';
                                    echo "<option value=\"{$lang}\" {$selected}>{$lang}</option>";
                                } ?>
                            </select>
                        </td>
                    </tr>
                    <?php }
                     $this->load->library('facebook'); 
                    if($_SESSION['option']->facebook_initialise){
                        if(empty($user->info['facebook'])) { ?>
                            <tr>
                                <td>Facebook <i class="fab fa-facebook"></i></td>
                                <td><span class="media" onclick="return facebookSignUp()"><?=$this->text('Підключити авторизацію через')?> facebook</span></td>
                            </tr>

                            <script>
                                window.fbAsyncInit = function() {
                                    
                                    FB.init({
                                      appId      : '<?=$this->facebook->getAppId()?>',
                                      cookie     : true,
                                      xfbml      : true,
                                      version    : 'v3.1'
                                    });
                                };

                                (function(d, s, id){
                                    var js, fjs = d.getElementsByTagName(s)[0];
                                    if (d.getElementById(id)) {return;}
                                    js = d.createElement(s); js.id = id;
                                    js.src = "//connect.facebook.net/en_US/sdk.js";
                                    fjs.parentNode.insertBefore(js, fjs);
                                }(document, 'script', 'facebook-jssdk'));
                            </script>
                    <?php } else { ?>
                        <tr>
                            <td>Facebook <i class="fab fa-facebook"></i></td>
                            <td><a href="<?=SITE_URL?>profile/facebook_disable" class="media right"> Відключити </a> <?=$this->text('Авторизацію підключено')?></td>
                        </tr>
                    <?php } } 
                    if(isset($user->s_newsletter)) { ?>
                    <tr>
                        <td><?=$this->text('Розсилка')?></td>
                        <td>
                            <select name="s_newsletter" onchange="$('main #tabs #main button.hide').removeClass('hide')">
                                <option value="1" <?=$user->s_newsletter == 1 ? 'selected' : ''?>><?=$this->text('Отримувати листи')?></option>
                                <option value="0" <?=$user->s_newsletter == 0 ? 'selected' : ''?>><?=$this->text('Не хочу отримувати листи')?></option>
                            </select>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><?=$this->text('Тип')?></td>
                        <td><?=$user->type_title?></td>
                    </tr>
                    <tr>
                        <td><?=$this->text('Останній вхід')?></td>
                        <td><?= date("d.m.Y H:i", $user->last_login) ?></td>
                    </tr>
                    <tr>
                        <td><?=$this->text('Дата реєстрації')?></td>
                        <td><?=date("d.m.Y H:i", $user->registered)?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <button class="<?=($showSave)?'':'hide'?>" type="submit"><?=$this->text('Зберегти зміни')?></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
    
    <div id="security">
        <h4><?=$this->text('Зміна паролю')?></h4>
        <form action="<?= SITE_URL?>profile/save_security" method="POST">
            <table>
                <tbody>
                    <?php if(!empty($user->password)) { ?>
                        <tr>
                            <td><?=$this->text("Введіть старий/поточний пароль")?></td>
                            <td><input type="password" name="old_password" placeholder="Поточний пароль" required></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td><?=$this->text("Введіть новий пароль")?></td>
                        <td><input type="password" name="new_password" placeholder="Новий пароль" required></td>
                    </tr>
                    <tr>
                        <td><?=$this->text("Повторіть новий пароль")?></td>
                        <td><input type="password" name="new_password_re" placeholder="Новий пароль" required></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="submit"><?=$this->text('Оновити пароль')?></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>

        <h4><?=$this->text('Реєстр дій')?></h4>
        <table>
            <tbody>
                <?php if($registerDo) foreach ($registerDo as $register) { ?>
                    <tr>
                        <td><?= date("d.m.Y H:i", $register->date)?></td>
                        <td><?= $register->title_public?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php
    $_SESSION['alias']->js_load[] = "assets/jquery-ui/ui/minified/jquery-ui.min.js";
    $_SESSION['alias']->js_load[] = "assets/blueimp/js/vendor/jquery.ui.widget.js";
    $_SESSION['alias']->js_load[] = "assets/blueimp/js/load-image.all.min.js";
    $_SESSION['alias']->js_load[] = "assets/blueimp/js/jquery.fileupload.js";
    $_SESSION['alias']->js_load[] = "js/user.js";
 ?>