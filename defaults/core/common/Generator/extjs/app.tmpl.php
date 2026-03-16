<div id="header">
	<?php echo (isset($header)?$header:'application'); ?>
</div>

<script type="text/javascript">

    // function to calculate and update viewport height
    var updateViewportHeight = function () {
        var viewHeight = Ext.getBody().getViewSize().height - 39;
        if (tabsPanel) {
            tabsPanel.setHeight(viewHeight);
        }
        return viewHeight;
    };

    // calculate initial viewport height
    var viewHeight = updateViewportHeight();

    // next create our tab panel
    var tabsPanel = Ext.create('Ext.tab.Panel', {
        xtype: 'tabpanel',
        activeTab: 0,
        border: 0,
        height: viewHeight,
        defaults: {
			border:0
	    },
	    items: [
	        {
	            title: 'Home',
				html: '<div class="graphic"><img src="assets/extjs/images/app.gif" alt="Home" class="image" /><h3>{welcomeHeader:Welcome}</h3><p>{welcome:This application is best viewed in a modern browser or tablet. Use the navigation tree to open tabs and manage them by using the controls at the top of this page.}</p></div>'
	        }
	    ]
	});
	<?php
	
	function navigationRoutesToString($node) {
		$retVal = '';
		foreach ($node as $item):
			if (isset($item['path'])):
				$token = strtolower(preg_replace("/[^\w\d\-\_\.]/", '', $item['name'])); 
				$retVal .= 'navigation["' . $token . '"] =  "' . $item['path'] . '";' . PHP_EOL;
			elseif (isset($item['children'])):
				$retVal .= navigationRoutesToString($item['children']);
			endif;
		endforeach;
		
		return $retVal;
	}
	echo 'navigation = [];' . PHP_EOL . navigationRoutesToString($navigation);
	
	function navigationTreeToString($node) {
		$retVal = '';
		foreach ($node as $item):
			if (isset($item['path'])):
				$retVal .= sprintf('{text: "%s",leaf: true},', $item['name']);
			elseif (isset($item['children'])):
				$retVal .= sprintf('{text: "%s",children: [', $item['name']);
				$retVal .= navigationTreeToString($item['children']);
				$retVal .= ']},';
			endif;
		endforeach;
		
		return $retVal;
	}
		
	?>
	// navigation
    var treePanel = Ext.create('Ext.tree.Panel', {
		border:false,
		maxWidth:320,
		width:320,
		listeners: {
	        itemclick: function(treeModel, record, item, index, e, eOpts){
				
				// get the id
				tabPanelId = item.innerText.replace(/[^\w\d\-\_\.]/g, '').toLowerCase();
				if (!tabsPanel.queryById(tabPanelId)) {
					tabPanel = tabsPanel.add({
						id:  tabPanelId,
						title: item.innerText,
						closable: true,
						scripts: true,
						html: '<div class="loading">Loading...</div>'
					}).show();	
				} else {
					tabsPanel.queryById(tabPanelId).show()
				}
				
				Ext.Ajax.request({
					url: navigation[tabPanelId],
				     success: function(response, opts) {
						tabPanel.update(response.responseText, true);
				     },
				     failure: function(response, opts) {
				         console.log('Server-side failure with status code ' + response.status);
				     }
				});
				
				
			}
		},
		root: {
			text: 'Navigation',
			expanded: true,
			children: [<?php echo navigationTreeToString($navigation); ?>]
		}
    });
	
	// append to array
    items = {
        xtype: 'panel',
        layout: {
            type: 'border'
		},
        collapsible: false,

        defaults: {
            collapsible: true,
            split: true
        },

        items: [
            {
                title: 'Youds Framework ExtJS Classic Integration',
				titleAlign: 'center',
                region: 'south',
				html: '<span class="footer">For further information please visit <a href="https://framework.youds.com/documentation/generator/extjs" target="_blank">https://framework.youds.com/documentation/generator/extjs</a></span>',
				collapsed:true,
                height: 70
            },
            {
                title: 'Navigation',
                region: 'west',
                items: treePanel,
                width: 210,
				maxWidth:320,
				collapsible: false
            },
            {
                title: 'Help and Further Information',
                region: 'east',
                html: 'East',
				collapsedMode:'mini',
				collapsed: true,
                width: 100
            },
            {
                region: 'center',
				collapsible: false,
				items: tabsPanel
            }
        ]
    };
	
	Ext.require([
	    'Ext.window.Window',
	    'Ext.panel.Panel',
	    'Ext.toolbar.*',
	    'Ext.tree.Panel',
	    'Ext.container.Viewport',
	    'Ext.container.ButtonGroup',
	    'Ext.form.*',
	    'Ext.tab.*',
	    'Ext.slider.*',
	    'Ext.layout.*',
	    'Ext.button.*',
	    'Ext.grid.*',
	    'Ext.data.*',
	    'Ext.util.*',
	    'Ext.perf.Monitor'
	]);

	Ext.onReady(function() {
        // add resize listener
        Ext.on('resize', updateViewportHeight);

        container = Ext.create('Ext.container.Container', {
		    id: 'main-container',
			layout: 'fit',
			width: '100%',
			height: '100%',
			region: 'south',
			items: items
		});

		view = Ext.create('Ext.container.Viewport', {
			padding: '39 0 0 0',
			layout: {
		        type: 'hbox',
		        align : 'stretch'
		    },
		    items: [
				container
			]
		});
	});

</script>