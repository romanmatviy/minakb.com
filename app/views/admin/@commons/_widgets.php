<div class="row">
    <?php 
        $this->load->model('wl_analytic_model');
        if($views = $this->wl_analytic_model->getViewers())
        {
            $unique_progress = $views_progress = 100;
            $unique_today = $views_today = 0;
            if(!empty($views->tableData))
            {
                if(isset($views->tableData[count($views->tableData)-1]->unique))
                $unique_today = $views->tableData[count($views->tableData)-1]->unique;
                if(isset($views->tableData[count($views->tableData)-1]->views))
                    $views_today = $views->tableData[count($views->tableData)-1]->views;
                $i = count($views->tableData)-2;
                if(isset($views->tableData[$i]))
                {
                    $unique_progress = $views->tableData[$i]->unique / $unique_today;
                    $views_progress = $views->tableData[$i]->views / $views_today;
                    if($unique_progress > 1)
                        $unique_progress = $unique_today / $views->tableData[$i]->unique;
                    if($views_progress > 1)
                        $views_progress = $unique_today / $views->tableData[$i]->views;
                    $unique_progress *= 100;
                    $views_progress *= 100;
                }
            }
        ?>
        <!-- begin col-3 -->
        <div class="col-md-3 col-sm-6">
            <div class="widget widget-stats bg-green">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-dashboard fa-fw"></i></div>
                <div class="stats-title">ВІДВІДУВАЧІВ ЗА СЬОГОДНІ</div>
                <div class="stats-number"><?=$unique_today?></div>
                <div class="stats-progress progress" title="<?=$unique_progress?>% в порівнянні з минулим днем">
                    <div class="progress-bar" style="width: <?=$unique_progress?>%;"></div>
                </div>
                <div class="stats-desc">Відвідувачів за весь час <?= $views->totalUsers?></div>
            </div>
        </div>
        <!-- end col-3 -->
        <!-- begin col-3 -->
        <div class="col-md-3 col-sm-6">
            <div class="widget widget-stats bg-blue">
                <div class="stats-icon stats-icon-lg"><i class="fa fa-bar-chart-o fa-fw"></i></div>
                <div class="stats-title">ПЕРЕГЛЯДІВ ЗА СЬОГОДНІ</div>
                <div class="stats-number">
                    <?=$views_today?>
                </div>
                <div class="stats-progress progress" title="<?=$views_progress?>% в порівнянні з минулим днем">
                    <div class="progress-bar" style="width: <?=$views_progress?>%;"></div>
                </div>
                <div class="stats-desc">Переглядів за весь час <?= $views->viewsCount?></div>
            </div>
        </div>
        <!-- end col-3 -->
    <?php } 
    $all_users = $this->db->getCount('wl_users');
    $subscribes = $this->db->getCount('wl_users', 5, 'type');
    $users = $this->db->getCount('wl_users', array('type' => '!5', 'status' => '!3'));
    $subscribes_percent = round($subscribes / $all_users * 100, 2);
    $users_percent = round($users / $all_users * 100, 2);
    ?>
    <!-- begin col-3 -->
    <div class="col-md-3 col-sm-6">
        <div class="widget widget-stats bg-purple">
            <div class="stats-icon stats-icon-lg"><i class="fa fa-book fa-fw"></i></div>
            <div class="stats-title">ПІДПИСНИКІВ</div>
            <div class="stats-number"><?=$subscribes?></div>
            <div class="stats-progress progress">
                <div class="progress-bar" style="width: <?=$subscribes_percent?>%;"></div>
            </div>
            <div class="stats-desc">Користувачі без повноцінного акаунту</div>
        </div>
    </div>
    <!-- end col-3 -->
    <!-- begin col-3 -->
    <div class="col-md-3 col-sm-6">
        <div class="widget widget-stats bg-black">
            <div class="stats-icon stats-icon-lg"><i class="fa fa-users fa-fw"></i></div>
            <div class="stats-title">ЗАРЕЄСТРОВАНИХ КОРИСТУВАЧІВ</div>
            <div class="stats-number"><?=$users?></div>
            <div class="stats-progress progress">
                <div class="progress-bar" style="width: <?=$users_percent?>%;"></div>
            </div>
            <div class="stats-desc">Користувачі, що підтвердили акаунт</div>
        </div>
    </div>
    <!-- end col-3 -->
</div>