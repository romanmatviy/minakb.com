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
            ajax: SITE_URL + "admin/application/getlist",
            "columns": [
                { "data": "id" },
	            { "data": "link" },
	            { "data": "name" },
	            { "data": "phone" },
	            { "data": "email" },
                { "data": "from" },
                { "data": "status_name" },
	            { "data": "date_add" }
	        ],
	        responsive: true,
            lengthMenu: [20, 40, 60]
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
