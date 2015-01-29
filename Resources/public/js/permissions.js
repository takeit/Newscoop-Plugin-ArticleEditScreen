var dynatable = $('#users-table')
    .dynatable({
        inputs: {
            paginationClass: 'pagination',
            paginationActiveClass: 'current',
            paginationDisabledClass: 'unavailable',
            paginationPrev: '&laquo;',
            paginationNext: '&raquo;',
            pageText: Translator.trans('aes.table.table_messages.page'),
            perPageText: Translator.trans('aes.table.table_messages.perPageText'),
            pageText: Translator.trans('aes.table.table_messages.pageText'),
            recordCountPageBoundTemplate: '{pageLowerBound} ' + Translator.trans('aes.table.table_messages.to') + ' {pageUpperBound} ' + Translator.trans('aes.table.table_messages.of'),
            recordCountPageUnboundedTemplate: '{recordsShown} ' + Translator.trans('aes.table.table_messages.of'),
            recordCountTotalTemplate: '{recordsQueryCount} {collectionName}',
            recordCountFilteredTemplate: Translator.trans('aes.table.table_messages.recordCountFilteredTemplate', {'%totalRecords%': '{recordsTotal}'}),
            recordCountText: Translator.trans('aes.table.table_messages.recordCountText'),
            recordCountTextTemplate: Translator.trans('aes.table.table_messages.recordCountTextTemplate'),
            recordCountTemplate: "<span id='dynatable-record-count-{elementId}' class='dynatable-record-count'>{textTemplate}</span>",
            processingText: Translator.trans('aes.table.table_messages.processingText'),
        },
        dataset: {
            ajax: true,
            ajaxUrl: Routing.generate('newscoop_editor_settings_loadusers'),
            ajaxOnLoad: false,
            records: [],
            perPageDefault: 10,
            perPageOptions: [10,20,50,100],
        },
        features: {
            paginate: true,
        },
        writers: {
            _cellWriter: function (column, record) {
                if (column.id == 'toggle') {
                    column.attributeWriter = function(record) {
                        var checked = '';
                        if (record.assigned) {
                            checked = 'checked';
                        }

                        return '<input type="checkbox" '+checked+' name="index[]" value="'+record.id+'" class="table-checkbox">';
                    }
                }

                if (column.id == 'updated') {
                    column.attributeWriter = function(record) {
                        return record.updated;
                    }
                }

                var html = column.attributeWriter(record),
                    td = '<td';

                if (column.hidden || column.textAlign) {
                  td += ' style="';
                  // keep cells for hidden column headers hidden
                  if (column.hidden) {
                    td += 'display: none;';
                  }
                  // keep cells aligned as their column headers are aligned
                  if (column.textAlign) {
                    td += 'text-align: ' + column.textAlign + ';';
                  }
                  td += '"';
                }
                if (column.cssClass) {
                  td += ' class="' + column.cssClass + '"';
                }

                return td + '>' + html + '</td>';
            }
        }
    }).bind('dynatable:ajax:success', function (e, response) {
        if (response.assignedAll) {
            $('.toggle-checkbox').prop('checked', true);
        } else {
            $('.toggle-checkbox').prop('checked', false);
        }
    }).data('dynatable');

    $('.toggle-checkbox').live('change', function() {
        $(this).parents('table').find('.table-checkbox').prop('checked', this.checked);
        if ($(this).prop("checked")) {
            $.post(Routing.generate('newscoop_editor_settings_assignall'),
                {'format': 'json'},
                function(data, textStatus, jqXHR) {
                    if (!data.status) {
                        flashMessage(Translator.trans('aes.alerts.assigallnerror'), 'error');
                        return false;
                    }
                    dynatable.process();
                    flashMessage(Translator.trans('aes.alerts.assignedall'));
            });
        } else {
            $.post(Routing.generate('newscoop_editor_settings_unassignall'),
                {'format': 'json'},
                function(data, textStatus, jqXHR) {
                    if (!data.status) {
                        flashMessage(Translator.trans('aes.alerts.assignallerror'), 'error');
                        return false;
                    }
                    dynatable.process();
                    flashMessage(Translator.trans('aes.alerts.unassignedall'));
            });
        }
    });

    $('.table-checkbox').live('change', function(event) {
        if (!$(this).prop("checked")) {
            $.post(Routing.generate('newscoop_editor_settings_unassignuser', {userId: $(this).val()}), {'format': 'json'}, function(data, textStatus, jqXHR) {
                if (!data.status) {
                    flashMessage(Translator.trans('aes.alerts.unassignerror'), 'error');
                    return false;
                }
                dynatable.process();
                flashMessage(Translator.trans('aes.alerts.unassigned'));
            });
        } else {
            $.post(Routing.generate('newscoop_editor_settings_assignuser', {userId: $(this).val()}), {'format': 'json'}, function(data, textStatus, jqXHR) {
                if (!data.status) {
                    flashMessage(Translator.trans('aes.alerts.assignerror'), 'error');
                    return false;
                }
                dynatable.process();
                flashMessage(Translator.trans('aes.alerts.assigned'));
            });
        }
    });