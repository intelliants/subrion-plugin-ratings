intelli.ratings = function()
{	
	return {
		oGrid: null,
		title: _t('manage_ratings'),
		url: intelli.config.admin_url + '/ratings.json',
		removeBtn: true,
		progressBar: false,
		texts:{
			confirm_one: _t('are_you_sure_to_delete_this_rating'),			
			confirm_many: _t('are_you_sure_to_delete_selected_ratings')
		},
		statusesStore: ['active','inactive'],
		record:['rate', 'rate_num', 'item_type', 'obj_id', 'item_title', 'item_url', 'remove'],
		columns:[
			'checkcolumn',{
				header: _t('title'), 
				dataIndex: 'item_title',
				sortable: true,
				width: 140,
                renderer: function(val, obj, grid){
                    return '<a href="'+intelli.config.ia_url+grid.json.item_url+'">'+val+'</a>';
                }
			},{
				header: _t('id'), 
				dataIndex: 'obj_id',
				sortable: true,
				width: 140
			},{
				header: _t('rate'), 
				dataIndex: 'rate',
				sortable: true,
				width: 140
			},{
				header: _t('rate_num'), 
				dataIndex: 'rate_num',
				sortable: true,
				width: 140
			},{
				header: _t('item'), 
				dataIndex: 'item_type', 
				sortable: true,
				width: 140
			},'remove'
		]
	};	
}();

Ext.onReady(function(){
	intelli.ratings = new intelli.exGrid(intelli.ratings);
	intelli.ratings.cfg.tbar = new Ext.Toolbar(
	{
		items:[
		_t('id') + ':',
		{
			xtype: 'numberfield',
			allowDecimals: false,
			allowNegative: false,
			name: 'searchId',
			id: 'searchId',
			emptyText: 'Enter Object ID',
			style: 'text-align: left'
		},
		_t('title') + ':',
		{
			xtype: 'textfield',
			name: 'searchTitle',
			id: 'searchTitle',
			emptyText: 'Enter title'
		},
		_t('item') + ':',
		{
			xtype: 'combo',
			typeAhead: true,
			triggerAction: 'all',
			editable: false,
			lazyRender: true,
			store: new Ext.data.SimpleStore({
				fields: ['value', 'display'],
				data : intelli.config.items
			}),
			value: intelli.config.items[0][1],
			displayField: 'display',
			valueField: 'value',
			mode: 'local',
			id: 'searchItem',
			name: 'searchItem'
		},{
			text: _t('search'),
			iconCls: 'search-grid-ico',
			id: 'fltBtn',
			handler: function()
			{
				var id = Ext.getCmp('searchId').getValue();
				var title = Ext.getCmp('searchTitle').getValue();
				var item = Ext.getCmp('searchItem').getValue();

				if('' != id || '' != title || '' != item)
				{
					intelli.ratings.dataStore.baseParams =
					{
						action: 'get',
						title: title,
						item: item,
						id: id
					};
					intelli.ratings.dataStore.reload();
				}
			}
		},
		'-',
		{
			text: _t('reset'),
			id: 'resetBtn',
			handler: function()
			{
				Ext.getCmp('searchId').reset();
				Ext.getCmp('searchTitle').reset();
				Ext.getCmp('searchItem').reset();

				intelli.ratings.dataStore.baseParams =
				{
					action: 'get',
					title: '',
					item: '',
					id: ''
				};
				intelli.ratings.dataStore.reload();
			}
		}]
	});
	intelli.ratings.init();
});