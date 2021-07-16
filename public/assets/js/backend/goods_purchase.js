define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods_purchase/index' + location.search,
                    add_url: 'goods_purchase/add',
                    edit_url: 'goods_purchase/edit',
                    del_url: 'goods_purchase/del',
                    multi_url: 'goods_purchase/multi',
                    import_url: 'goods_purchase/import',
                    table: 'goods_purchase',
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
                        {field: 'in_purchase_id', title: __('In_purchase_id'), operate: 'LIKE'},
                        {field: 'transaction_no', title: __('Transaction_no'), operate: 'LIKE'},
                        {field: 'shop_name', title: __('Shop_name'), operate: 'LIKE'},
                        {field: 'good_name', title: __('Good_name'), operate: 'LIKE'},
                        {field: 'category', title: __('Category'), operate: 'LIKE'},
                        {field: 'attr', title: __('Attr'), operate: 'LIKE'},
                        {field: 'unit', title: __('Unit'), operate: 'LIKE'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'num', title: __('Num'), operate:'BETWEEN'},
                        {field: 'supplier_name', title: __('Supplier_name'), operate: 'LIKE'},
                        {field: 'goods_no', title: __('Goods_no'), operate: 'LIKE'},
                        {field: 'optime', title: __('Optime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'operator_id', title: __('Operator_id'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'flag', title: __('Flag'), formatter: Table.api.formatter.flag},
                        {field: 'weight', title: __('Weight'), operate:'BETWEEN'},
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