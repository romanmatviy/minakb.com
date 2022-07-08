<div class="row">
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <h4 class="panel-title"><?=(isset($user) && is_object($user)) ? 'Рух коштів балансу / Звірка клієнта '.$user->name : 'Список всіх транзакцій'?></h4>
            </div>
            <div class="panel-body">
                <?php if(isset($user) && is_object($user)) { ?>
                    <input type="hidden" name="user" value="<?=$user->id?>">
                <?php } ?>
                <?php /* ?>
                <form class="form-inline m-b-15">
                    <input type="date" name="from" value="<?=$this->data->get('from')?>" class="form-control m-r-5" title="Період від">
                    <?php if($from = $this->data->get('from')) { ?>
                    <input type="date" name="to" value="<?=$this->data->get('to')?>" class="form-control m-r-5" title="Період до">
                    <?php } ?>
                    <select name="status" class="form-control m-r-5">
                        <option value="0">Всі статуси</option>
                        <option value="1" <?= (isset($_GET['status']) && $_GET['status'] == 1) ? 'selected' : ''?>>Заявка</option>
                        <option value="2" <?= (isset($_GET['status']) && $_GET['status'] == 2) ? 'selected' : ''?>>Підтверджено</option>
                        <option value="3" <?= (isset($_GET['status']) && $_GET['status'] == 3) ? 'selected' : ''?>>Скасовано</option>
                    </select>
                    <select class="form-control m-r-5">
                        <option value="0">Всі дії</option>
                        <option value="1">Тільки дебет</option>
                        <option value="-1">Тільки кредит</option>
                    </select>
                    <?php if(isset($user) && is_object($user)) { ?>
                        <input type="hidden" name="user" value="<?=$user->id?>">
                    <?php } else { ?>
                        <input type="text" name="user" class="form-control m-r-5">
                    <?php } ?>
                    <input type="submit" class="btn btn-warning" value="Фільтрувати" />
                </form> */?>
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Id Статус</th>
                                <th>Клієнт</th>
                                <td>Дебет</td>
                                <td>Кредит</td>
                                <td>Баланс</td>
                                <th>Інформація</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($payments)) foreach($payments as $p) { ?>
                        <tr class="<?=($p->status == 1 && $p->debit > 0 && $p->security_check) ? 'warning' : ''?> <?=$p->security_check ? '':'danger'?>" <?=$p->security_check ? '':'title="Помилка цифрового підпису платежу"'?>>
                            <td><a href="<?=SITE_URL?>admin/<?=$_SESSION['alias']->alias?>/<?=$p->id?>" class="btn btn-xs btn-<?=($p->status == 1 && $p->debit > 0) ? 'warning' : 'success'?>">#<?=$p->id?>:
                            <?php
                                switch ($p->status) {
                                    case 1:
                                        echo "Очікує обробки";
                                        break;
                                    case 2:
                                        echo "Підтверджено";
                                        break;
                                    case 3:
                                        echo "Скасовано";
                                        break;
                                    case 4:
                                        echo "Підтверджено => Скасовано";
                                        break;
                                }
                                if($p->manager > 0)
                                    echo(date(' d.m H:i', $p->date_edit));
                                ?>
                            </a></td>
                            <td><a href="/admin/wl_users/<?=$p->client_email?>#tabs-<?=$_SESSION['alias']->alias?>">#<?=$p->user?> <strong><?=$p->client_name?></strong></a></td>
                            <?php if($p->debit)
                                echo "<td>\${$p->debit}</td>";
                            else
                                echo "<td></td>";
                            if($p->credit)
                                echo "<td>\${$p->credit}</td>";
                            else
                                echo "<td></td>";
                            echo "<td>\${$p->balance}</td>";
                            echo "<td>";
                            if($p->status == 1 && $p->debit > 0)
                                echo "Заявка. ".$p->action;
                            else
                                echo $p->action;
                            echo "</td>"; ?>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php
                $this->load->library('paginator');
                echo $this->paginator->get();
                ?>
            </div>
        </div>
    </div>
</div>

<style>hr { margin: 5px 0 }</style>