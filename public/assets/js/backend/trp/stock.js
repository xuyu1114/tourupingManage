define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'trp/stock/index' + location.search,
                    add_url: 'trp/stock/add',
                    edit_url: 'trp/stock/edit',
                    del_url: 'trp/stock/del',
                    multi_url: 'trp/stock/multi',
                    import_url: 'trp/stock/import',
                    table: 'stock',
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
                        {field: 'in_stock_id', title: __('In_stock_id'), operate: 'LIKE'},
                        {field: 'shop_name', title: __('Shop_name'), operate: 'LIKE'},
                        {field: 'goods_no', title: __('Goods_no'), operate: 'LIKE'},
                        {field: 'brand_name', title: __('Brand_name'), operate: 'LIKE'},
                        {field: 'in_num', title: __('In_num'), operate:'BETWEEN'},
                        {field: 'out_num', title: __('Out_num'), operate:'BETWEEN'},
                        {field: 'stock', title: __('Stock'), operate:'BETWEEN'},
                        {field: 'category', title: __('Category'), operate: 'LIKE'},
                        {field: 'brand', title: __('Brand'), operate: 'LIKE'},
                        {field: 'operator_id', title: __('Operator_id'), operate: 'LIKE'},
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