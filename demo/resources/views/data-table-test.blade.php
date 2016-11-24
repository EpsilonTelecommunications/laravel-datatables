<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.js" crossorigin="anonymous"></script>
    </head>
    <body>
        {!! $dataTable->getHtmlWithJson('dataTable1') !!}
        <script>

            var app = {
                pageVariables : {}
            };

            var buildDataTable = function(id, extraParams, extraConfig) {

                if (!app.pageVariables.dataTablesAjaxProcessing) {
                    app.pageVariables.dataTablesAjaxProcessing = {};
                }

                app.pageVariables.dataTablesAjaxProcessing[id] = false;

                var dataTable = $('#' + id);
                var configuration = dataTable.data('datatableconfig');

                var config = {
                    ajax: function (data, callback, settings) {
                        if (app.pageVariables.dataTablesAjaxProcessing[id] !== false) {
                            clearTimeout(app.pageVariables.dataTablesAjaxProcessing[id]);
                        }
                        app.pageVariables.dataTablesAjaxProcessing[id] = setTimeout(function() {
                            $.ajax({
                                url: configuration.endpoint,
                                data: $.extend({}, ($.isFunction(extraParams)) ? extraParams.call() : extraParams, data),
                                method: 'GET',
                                success: function(data) {
                                    callback(data);
                                    app.pageVariables.dataTablesAjaxProcessing[id] = false;
                                }
                            });
                        }, 350);
                    },
                    processing: true,
                    serverSide: true,
                    sDom: '<"dt-panelmenu clearfix"lfr>t<"dt-panelfooter clearfix"ip>',
                    columns: configuration.columns,
                    order: configuration.order,
                    oLanguage: {
                        sProcessing: '<div class="processing"><i class="fa fa-spinner fa-pulse text-primary fs80 text-primary mb10"></i><br><p class="text-muted fs16">Loading results<br />Please wait</p></div>'
                    }
                };

                if (extraConfig) {
                    config = $.extend(true, {}, config, extraConfig);
                }

                return dataTable.DataTable(config);
            };

            buildDataTable('dataTable1');


        </script>
    </body>
</html>
