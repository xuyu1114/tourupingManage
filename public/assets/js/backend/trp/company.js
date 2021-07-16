define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'trp/company/index' + location.search,
                    add_url: 'trp/company/add',
                    edit_url: 'trp/company/edit',
                    del_url: 'trp/company/del',
                    multi_url: 'trp/company/multi',
                    import_url: 'trp/company/import',
                    table: 'company',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'in_company_id', title: __('In_company_id'), operate: 'LIKE'},
                        {field: 'company_name', title: __('Company_name'), operate: 'LIKE'},
                        {field: 'social_code', title: __('Social_code'), operate: 'LIKE'},
                        {field: 'legal_name', title: __('Legal_name'), operate: 'LIKE'},
                        {field: 'card_no', title: __('Card_no'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: 'LIKE'},
                        {field: 'address', title: __('Address'), operate: 'LIKE'},
                        {field: 'business_category', title: __('Business_category'), operate: 'LIKE'},
                        {field: 'business_main', title: __('Business_main'), operate: 'LIKE'},
                        {field: 'business_area', title: __('Business_area'), operate:'BETWEEN'},
                        {field: 'operator_id', title: __('Operator_id'), operate: 'LIKE'},
                        {field: 'longitude_latitude', title: __('Longitude_latitude'), operate: 'LIKE'},
                        {field: 'flag', title: __('Flag'), formatter: Table.api.formatter.flag},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});