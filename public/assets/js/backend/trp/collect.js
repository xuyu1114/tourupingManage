define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'trp/collect/index' + location.search,
                    add_url: 'trp/collect/add',
                    edit_url: 'trp/collect/edit',
                    del_url: 'trp/collect/del',
                    multi_url: 'trp/collect/multi',
                    import_url: 'trp/collect/import',
                    table: 'collect',
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
                        {field: 'in_collect_id', title: __('In_collect_id'), operate: 'LIKE'},
                        {field: 'transaction_no', title: __('Transaction_no'), operate: 'LIKE'},
                        {field: 'goods_no', title: __('Goods_no'), operate: 'LIKE'},
                        {field: 'category', title: __('Category'), operate: 'LIKE'},
                        {field: 'brand_name', title: __('Brand_name'), operate: 'LIKE'},
                        {field: 'attr', title: __('Attr'), operate: 'LIKE'},
                        {field: 'out_num', title: __('Out_num'), operate:'BETWEEN'},
                        {field: 'subsidy_price', title: __('Subsidy_price'), operate:'BETWEEN'},
                        {field: 'sales_price', title: __('Sales_price'), operate:'BETWEEN'},
                        {field: 'shop_name', title: __('Shop_name'), operate: 'LIKE'},
                        {field: 'farmer_name', title: __('Farmer_name'), operate: 'LIKE'},
                        {field: 'card_no', title: __('Card_no'), operate: 'LIKE'},
                        {field: 'address', title: __('Address'), operate: 'LIKE'},
                        {field: 'village_name', title: __('Village_name'), operate: 'LIKE'},
                        {field: 'optime', title: __('Optime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'operator_id', title: __('Operator_id'), operate: 'LIKE'},
                        {field: 'unit', title: __('Unit'), operate: 'LIKE'},
                        {field: 'weight', title: __('Weight'), operate:'BETWEEN'},
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