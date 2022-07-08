/*   
Template Name: Color Admin - Responsive Admin Dashboard Template build with Twitter Bootstrap 3.3.4
Version: 1.7.0
Author: Sean Ngu
Website: http://www.seantheme.com/color-admin-v1.7/admin/
*/
var handleDataTableCombinationSetting = function() {
    "use strict";
    if ($("#data-table").length !== 0) {
        var e = $("#data-table").DataTable({
            ajax: SITE_URL + "admin/wl_register/getlist",
            "columns": [
	            { "data": "id" },
	            { "data": "name" },
	            { "data": "user" },
	            { "data": "date" }
	        ],
            "order":[[0, 'desc']],
	        responsive: true,
            lengthMenu: [25, 50, 100]
        });
    }
};
var TableManageCombine = function() {
    "use strict";
    return {
        init: function() {
            handleDataTableCombinationSetting()
        }
    }
}()
