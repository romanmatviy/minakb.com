<?php
    $this->db->select('wl_aliases as a', 'id, alias, seo_robot, admin_ico, service');
    $where = array('alias' => '#a.id', 'content' => 0);
    if($_SESSION['language']) $where['language'] = $_SESSION['language'];
    $this->db->join('wl_ntkd', 'name', $where);
    unset($where['language']);
    $this->db->join('wl_sitemap', 'id as sitemap_id, time, changefreq, priority', $where);
	$wl_aliases = $this->db->get('array');
    $in_table = [];
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="<?=SITE_URL?>admin/wl_ntkd/global_metatags" class="btn btn-info btn-xs"><i class="fa fa-globe"></i> Загальні Meta-теги</a>
                    <a href="<?=SITE_URL?>admin/wl_ntkd/seo_robot" class="btn btn-success btn-xs"><i class="fa fa-line-chart"></i> Загальний SEO робот</a>
                </div>
                <h4 class="panel-title">Наявні адреси:</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
								<th>Адреса</th>
                                <th>Назва</th>
                                <th>Остання зміна<?=($_SESSION['language']) ? ' ('.$_SESSION['language'].')' : ''?></th>
                                <th>Частота оновлення<?=($_SESSION['language']) ? ' ('.$_SESSION['language'].')' : ''?></th>
								<th>Пріорітетність<?=($_SESSION['language']) ? ' ('.$_SESSION['language'].')' : ''?> [0..1]</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5"><center><a href="<?=SITE_URL?>admin/wl_ntkd/seo_robot" class="btn btn-success btn"><i class="fa fa-line-chart"></i> Загальний SEO робот на всі сторінки</a></center></td>
                            </tr>
                        <?php if($wl_aliases)
                        foreach ($wl_aliases as $alias) {
                            if(in_array($alias->id, $in_table))
                            {
                                $this->db->deleteRow('wl_sitemap', $alias->sitemap_id);
                                continue;
                            }
                            $in_table[] = $alias->id;
                            ?>
							<tr>
								<td>
                                    <a href="<?=SITE_URL.'admin/wl_ntkd/'.$alias->alias?>"><?=($alias->admin_ico) ? '<i class="fa '.$alias->admin_ico.'"></i>' : ''?> <?=$alias->alias?></a>
                                    <?php if($alias->seo_robot > 0) { ?>
                                        <a href="<?=SITE_URL?>admin/wl_ntkd/<?=$alias->alias?>/seo_robot" class="btn btn-success btn-xs"><i class="fa fa-globe"></i> SEO робот</a>
                                    <?php } ?>
                                </td>
                                <td><?=$alias->name?></td>
                                <td><?=($alias->time)?date('d.m.Y H:i', $alias->time):'Не індексовано'?></td>
                                <?php if($alias->priority < 0) echo('<td colspan="2">Сторінка не індексується</td>'); else { ?>
                                    <td><?=$alias->changefreq?></td>
    								<td><?=$alias->priority/10?></td>
                                <?php } ?>
							</tr>
						<?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>