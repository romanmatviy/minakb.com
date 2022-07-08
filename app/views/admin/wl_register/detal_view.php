<!-- begin row -->
<div class="row">
    <!-- begin col-8 -->
    <div class="col-md-8">
        <!-- begin panel -->
        <div class="panel panel-inverse" data-sortable-id="form-stuff-1">
            <div class="panel-heading">
                <h4 class="panel-title">Запис №<?=$register->id?></h4>
            </div>
            <div class="panel-body">
                <table class="table table-striped table-bordered">
                    <tbody>
                    	<tr>
                    		<td>Дія (адмін запис)</td>
                    		<td><?=$register->title?></td>
                    	</tr>
                    	<tr>
                    		<td>Дія (публічний запис)</td>
                    		<td><?=$register->title_public?></td>
                    	</tr>
                        <tr>
                            <td>Користувач</td>
                            <td><a href="<?=SITE_URL?>admin/wl_users/<?=$register->email?>"><?=$register->user_name?> (<?=$register->user?>)</a></td>
                        </tr>
                        <tr>
                            <td>email користувача</td>
                            <td><?=$register->email?></td>
                        </tr>
                        <tr>
                            <td>Тип запису</td>
                            <td><?=($register->public)?'Публічний':'Службовий'?></td>
                        </tr>
                        <tr>
                            <td>Додатково</td>
                            <td><?=$register->additionally?></td>
                        </tr>
                        <tr>
                    		<td>Допомога</td>
                    		<td><?=$register->help_additionall?></td>
                    	</tr>
                        <tr>
                            <td>Дата реєстрації</td>
                            <td><?=date("d.m.Y H:i", $register->date)?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- end panel -->
    </div>
</div>
<!-- end row -->