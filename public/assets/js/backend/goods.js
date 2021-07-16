define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/index' + location.search,
                    add_url: 'goods/add',
                    edit_url: 'goods/edit',
                    del_url: 'goods/del',
                    multi_url: 'goods/multi',
                    import_url: 'goods/import',
                    table: 'goods',
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
                        {field: 'in_goods_id', title: __('In_goods_id'), operate: 'LIKE'},
                        {field: 'operator', title: __('Operator'), operate: 'LIKE'},
                        {field: 'goods_no', title: __('Goods_no'), operate: 'LIKE'},
                        {field: 'goods_name', title: __('Goods_name'), operate: 'LIKE'},
                        {field: 'category', title: __('Category'), operate: 'LIKE'},
                        {field: 'attr', title: __('Attr'), operate: 'LIKE'},
                        {field: 'unit', title: __('Unit'), operate: 'LIKE'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'produce_company', title: __('Produce_company'), operate: 'LIKE'},
                        {field: 'regist_no', title: __('Regist_no'), operate: 'LIKE'},
                        {field: 'toxicity', title: __('Toxicity'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        {field: 'desc', title: __('Desc'), operate: 'LIKE'},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
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