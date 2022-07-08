<!-- begin row -->
<div class="row">
    <!-- begin col-12 -->
    <div class="col-md-12">
        <div class="result-container">
            <form action="<?=SITE_URL?>admin/search">
                <div class="input-group m-b-20">
                    <input name="by" type="text" class="form-control input-white" placeholder="Ключове слово пошуку..." value="<?=$this->data->get('by')?>" />
                    <div class="input-group-btn">
                        <button type="submit" class="btn btn-inverse"><i class="fa fa-search"></i> Пошук</button>
                        <button type="button" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu pull-right">
                            <li><a href="?by=<?=$this->data->get('by')?>" title="Скасувати фільтр">Пошук всюди</a></li>
                            <?php if($_SESSION['language']) { ?>
                                <li class="divider"></li>
                                <li <?=(!$this->data->get('language')) ? 'class="active"':''?>><a href="<?=$this->data->get_link('language')?>" title="Включення результату видачі на всіх мовах">Пошук у всіх мовах</a></li>
                                <?php foreach ($_SESSION['all_languages'] as $language) {
                                    echo '<li';
                                    if($this->data->get('language') == $language) echo ' class="active"';
                                    echo '><a href="'.$this->data->get_link('language', $language).'">Пошук тільки <strong>'.$language.'</strong></a></li>';
                                }
                            }
                            if($wl_aliases) { ?>
                                <li class="divider"></li>
                                <li <?=(!$this->data->get('alias')) ? 'class="active"':''?>><a href="<?=$this->data->get_link('alias')?>" title="Скасувати фільтр">Пошук у всіх розділах</a></li>
                                <?php foreach ($wl_aliases as $wl_alias)
                                    if($this->userCan($wl_alias->alias)) {
                                        echo '<li';
                                        if($this->data->get('alias') == $wl_alias->alias) echo ' class="active"';
                                        echo '><a href="'.$this->data->get_link('alias', $wl_alias->alias).'">Пошук тільки <strong>'.$wl_alias->name.'</strong></a></li>';
                                }
                            } ?>
                        </ul>
                    </div>
                </div>
            </form>

            <?php if(!empty($data)) { ?>
                <div class="dropdown pull-left">
                    <a href="javascript:;" class="btn btn-white btn-white-without-border dropdown-toggle" data-toggle="dropdown">
                        Сортувати за <span class="caret m-l-5"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <li <?=(!$this->data->get('sort')) ? 'class="active"':''?>><a href="<?=$this->data->get_link('sort')?>">Авто</a></li>
                        <li class="divider"></li>
                        <li <?=($this->data->get('sort') == 'name_up') ? 'class="active"':''?>><a href="<?=$this->data->get_link('sort', 'name_up')?>">Назві аА..яЯ <i class="fa fa-level-down"></i></a></li>
                        <li <?=($this->data->get('sort') == 'name_down') ? 'class="active"':''?>><a href="<?=$this->data->get_link('sort', 'name_down')?>">Назві яЯ..аА <i class="fa fa-level-up"></i></a></li>
                    </ul>
                </div>
                <?php
                    $this->load->library('paginator');
                    $this->paginator->style('ul', "pagination pagination-without-border pull-right m-t-0");
                    echo $this->paginator->get();
                ?>
                <div class="clearfix"></div>
                <ul class="result-list m-t-10">
                    <?php foreach ($data as $search) { ?>
                    <li>
                        <?php if($search->image) { ?>
                            <div class="result-image">
                                <a href="<?=SITE_URL.$search->edit_link?>"><img src="<?=IMG_PATH.$search->image?>" alt="<?=$search->name?>" title="<?=$search->name?>" /></a>
                            </div>
                        <?php } ?>
                        <div class="result-info">
                            <h4 class="title"><a href="<?=SITE_URL.$search->edit_link?>"><?=$search->name?></a></h4>
                            <p class="location"><a href="<?=SITE_URL.$search->link?>" target="_blank"><?=SITE_URL.$search->link?></a> <?=date('d.m.Y H:i', $search->date)?> By <a href="<?=SITE_URL?>admin/wl_users/<?=$search->author?>"><?=$search->author_name?></a></p>
                            <p class="desc">
                                <?php
                                if($search->list != '')
                                    echo($search->list);
                                else
                                    echo($this->data->getShortText($search->text, 400));
                                ?>
                            </p>
                            <div class="btn-row">
                                <a href="<?=SITE_URL.$search->link?>" data-toggle="tooltip" data-container="body" data-title="Дивитись сторінку на сайті"><i class="fa fa-fw fa-cloud-upload"></i></a>
                                <a href="<?=SITE_URL.'admin/'.$search->edit_link?>" data-toggle="tooltip" data-container="body" data-title="Редагувати"><i class="fa fa-fw fa-cog"></i></a>
                                <?php if($search->folder) { ?>
                                    <a href="<?=SITE_URL.$search->edit_link?>#tab-photo" data-toggle="tooltip" data-container="body" data-title="Редагувати зображення"><i class="fa fa-fw fa-camera"></i></a>
                                    <a href="<?=SITE_URL.$search->edit_link?>#tab-audio" data-toggle="tooltip" data-container="body" data-title="Редагувати аудіо"><i class="fa fa-fw fa-tasks"></i></a>
                                <?php } ?>
                                <a href="<?=SITE_URL.$search->edit_link?>#tab-video" data-toggle="tooltip" data-container="body" data-title="Редагувати відео"><i class="fa fa-fw fa-video-camera"></i></a>
                                <a href="<?=SITE_URL.$search->edit_link?>#tab-statistic" data-toggle="tooltip" data-container="body" data-title="Статистика"><i class="fa fa-fw fa-bar-chart-o"></i></a>
                                <a href="<?=SITE_URL?>admin/wl_users/<?=$search->author?>" data-toggle="tooltip" data-container="body" data-title="Автор"><i class="fa fa-fw fa-user"></i></a>
                            </div>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
                <div class="clearfix">
                    <?php
                        $this->load->library('paginator');
                        $this->paginator->style('ul', "pagination pagination-without-border pull-right");
                        echo $this->paginator->get();
                    ?>
                </div>
            <?php } else { ?>
                <div class="alert alert-warning  m-b-15">
                    <strong>Увага!</strong>
                    За даним запитом нічого не знайдено. Уточніть ключове слово пошуку.
                </div>
            <?php } ?>
        </div>
    </div>
    <!-- end col-12 -->
</div>
<!-- end row -->