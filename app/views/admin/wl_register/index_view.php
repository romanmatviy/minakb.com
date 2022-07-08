<!-- begin row -->
<div class="row">
    <!-- begin col-12 -->
    <div class="col-md-12">
        <div class="panel panel-inverse">
            <div class="panel-heading">
                <div class="panel-heading-btn">
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a>
                </div>
                <h4 class="panel-title">Список всіх записів реєстру</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="data-table" class="table table-striped table-bordered nowrap" width="100%">
                        <thead>
                            <tr>
                                <th width="100px" nowrap>ID</th>
                                <th >Запис</th>
                                <th >Користувач</th>
                                <th >Дата реєстрації</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- end col-12 -->
</div>
<!-- end row -->

<?php 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/jquery.dataTables.js';  
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colReorder.js'; 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.colVis.js'; 
  $_SESSION['alias']->js_load[] = 'assets/DataTables/js/dataTables.responsive.js'; 
  $_SESSION['alias']->js_load[] = 'assets/white-lion/table-register.js'; 
  $_SESSION['alias']->js_init[] = 'TableManageCombine.init();'; 
?>
<link href="<?=SITE_URL?>assets/DataTables/css/data-table.css" rel="stylesheet" />