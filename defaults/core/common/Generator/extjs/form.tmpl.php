<?php

// unique id for our grid (where loaded more than once)
$id = uniqid();
?>
<div id="form-<?php echo $id; ?>" style="margin:auto;width:600px;"></div>
<script type="text/javascript">

	Ext.require([
	    'Ext.form.*',
	    'Ext.layout.container.Column',
	    'Ext.tab.Panel',
	    '*',
	    'Ext.ux.DataTip'
	]);
	
	
	var required = '<span style="color:red;font-weight:bold" data-qtip="Required">*</span>';
	Ext.onReady(function() {
	    Ext.QuickTips.init();
		
	    var bd = Ext.getBody();
	    
	    /*
	     * ================  Form 3  =======================
	     */
	    Ext.widget({
	        xtype: 'form',
	        id: 'form-el-<?php echo $id; ?>',
	        renderTo: 'form-<?php echo $id; ?>',
	        collapsible: true,
	        frame: true,
	        title: 'Form',
	        bodyPadding: '5 5 0',
	        width: 600,
	        fieldDefaults: {
	            labelAlign: 'top',
	            msgTarget: 'side'
	        },	
	        bbar: {
	            xtype: 'statusbar',
	            reference: 'wordStatus',
	            // These are just the standard toolbar TextItems we created above.  They get
	            // custom classes below in the render handler which is what gives them their
	            // customized inset appearance.
	            items: [{
	                reference: 'wordCount',
					id: 'wordCount-<?php echo $id; ?>',
	                xtype: 'tbtext',
	                text: 'Words: 0'
	            }, ' ', {
	                reference: 'charCount',
					id: 'charCount-<?php echo $id; ?>',
	                xtype: 'tbtext',
	                text: 'Chars: 0'
	            }, ' ', {
	                reference: 'clock',
					id: 'formClock-<?php echo $id; ?>',
	                xtype: 'tbtext',
	                text: Ext.Date.format(new Date(), 'g:i:s A')
	            }, ' ']
	        },
	        listeners: {
	            render: function() {
			        var clock = Ext.getCmp('formClock-<?php echo $id; ?>'),
			            wordStatus = Ext.getCmp('wordStatus-<?php echo $id; ?>'),
			            wordCount = Ext.getCmp('wordCount-<?php echo $id; ?>'),
			            charCount = Ext.getCmp('charCount-<?php echo $id; ?>'),
			            event = Ext.isOpera ? 'keypress' : 'keydown'; // opera behaves a little weird with keydown
						
			        // Kick off the clock timer that updates the clock el every second:
			        Ext.TaskManager.start({
			            run: function() {
							clock = Ext.getCmp('formClock-<?php echo $id; ?>');
			                Ext.fly(clock.getEl()).update(Ext.Date.format(new Date(), 'H:i:s A'));
			            },
			            interval: 1000
			        });
					
					// Set up our event for updating the word/char count
					
			        Ext.each(Ext.ComponentQuery.query('[id^=form-]'), function () {
						this.addListener('keydown', function (comp) {
				            var wordCount = Ext.getCmp('wordCount-<?php echo $id; ?>'),
				            charCount = Ext.getCmp('charCount-<?php echo $id; ?>')
				            var v = comp.getValue(),
				                wc = 0,
				                cc = v.length ? v.length : 0;

				            if (cc > 0) {
				                wc = v.match(/\b/g);
				                wc = wc ? wc.length / 2 : 0;
				            }
				            wordCount.update('Words: ' + wc);
				            charCount.update('Chars: ' + cc);
						});
						
						this.addListener('blur', function (comp) {
				            var wordCount = Ext.getCmp('wordCount-<?php echo $id; ?>'),
				            charCount = Ext.getCmp('charCount-<?php echo $id; ?>')
				          
				            wordCount.update('Words: 0');
				            charCount.update('Chars: 0');
						})
						
						
					})
				},
	            delay: 100
	        },

	        items: [{
	            xtype: 'container',
	            anchor: '100%',
	            layout: 'anchor', 
				items: [
				<?php 
					
					// define row
					$row = NULL;
					$a = 0;
					foreach ($attributes as $name => $attr):
						if (isset($attr['token']))
							$token = strtolower(str_replace(' ' , '', $attr['token']));
						else
							$token = strtolower(str_replace(' ' , '', $name));
						$a++;
						if (isset($attr['row']) && $row != $attr['row']):
							if ($row != NULL): ?>]},<?php endif; 
							$row = $attr['row']; 
							$rowTriggered = true; ?>
				            {
				                xtype: 'container',
				                flex: 1,
				                layout: 'hbox',
				                items: [<?php
						endif;
						switch ($attr['type']):
							case NULL:
							case 'textfield': ?>
						{
							xtype:'textfield',
	                        flex: 2,
	                        fieldLabel: '<?php echo $name; ?>',
	                        name: '<?php echo $token; ?>',
	                        reference: '<?php echo $token; ?>-<?php echo $id; ?>',
							id: 'form-<?php echo $token; ?>-<?php echo $id; ?>',
							margin: '5px',
							enableKeyEvents: true,
	                        allowBlank: <?php echo (isset($attr['required']) && $attr['required']?'false':'true'); ?>
	                    },<?php break;
						case 'htmleditor': ?>
						{
				            xtype: 'htmleditor',
				            name: '<?php echo $token; ?>',
				            fieldLabel: '<?php echo $name; ?>',
	                        reference: '<?php echo $token; ?>-<?php echo $id; ?>',
							id: 'form-<?php echo $token; ?>-<?php echo $id; ?>',
							enableKeyEvents: true,
				            height: 250,
							width: '100%',
				            anchor: '100%'
				        },
						<?php break; 
						case 'timefield': ?>
						{
				            xtype: 'timefield',
				            fieldLabel: '<?php echo $name; ?>',
				            name: '<?php echo $token; ?>',
	                        reference: '<?php echo $token; ?>-<?php echo $id; ?>',
							id: 'form-<?php echo $token; ?>-<?php echo $id; ?>',
							enableKeyEvents: true,
				            minValue: '00:00am',
				            maxValue: '00:00pm',
				            tooltip: 'Enter a time',
							margin:'5px', 
				            plugins: {
				                ptype: 'datatip',
				                tpl: 'Select time {date:date("G:i")}'
				            }
				        },
						<?php break;
						case 'datefield':
						?>{
				            fieldLabel: '<?php echo $name; ?>',
				            name: '<?php echo $token; ?>',
				            xtype: 'datefield',
	                        reference: '<?php echo $token; ?>-<?php echo $id; ?>',
							id: 'form-<?php echo $token; ?>-<?php echo $id; ?>',
							enableKeyEvents: true,
				            tooltip: 'Enter a date',
							margin: '5px'
				        },
						<?php break;
						case 'combo':
						?>{
							xtype: 'combo',
	                        fieldLabel: '<?php echo $name; ?>',
							store: 	Ext.create('Ext.data.Store', {
								fields: ['optionName', 'value'],
								data: [<?php
								foreach($attr['values'] as $key => $col):?>{
									optionName: '<?php echo $col; ?>',
									value: '<?php echo $key; ?>',
									
								},<?php endforeach; ?>],
							}),
							name: '<?php echo $token; ?>',
							emptyText: 'Select...',
							displayField: 'optionName',
							valueField: 'value',
							margin: '5px',
							enableKeyEvents: true,
	                        allowBlank: <?php echo (isset($attr['required']) && $attr['required']?'false':'true'); ?>
						},<?php					
						break;
					endswitch; 
					
					if (count($attributes) > 0 && $a == count($attributes)):
					?>
						]}]},
					<?php
					endif; 
				endforeach; ?>
	            ],
			
	        buttons: [{
	            text: 'Save',
	            handler: function() {
					form = this.up('form').getForm();
					form.url = '{matchedRoute}/hook';
					if (this.up('form').getForm().isValid()) {
						form.submit({
							success: function(form, action) {
							    Ext.MessageBox.show({
									title: 'Success',
									msg: 'Form saved successfully',
									icon: Ext.MessageBox.SUCCESS,
									buttons: Ext.Msg.OK,
									fn: function (val) {
										tabPanel.close();
									}
								});
							},
							failure: function(form, action) {
							    Ext.MessageBox.show({
									title: 'Error',
									msg: 'Could not save the form',
									icon: Ext.MessageBox.ERROR,
									buttons: Ext.Msg.OK
								});
							}
						});
					} else {
					    Ext.MessageBox.show({
							title: 'Error',
							msg: 'The form contains errors',
							icon: Ext.MessageBox.ERROR,
							buttons: Ext.Msg.OK
						});
					}
					
	            }
	        },{
	            text: 'Cancel',
	            handler: function() {
	                this.up('form').getForm().reset();
	            }
	        }]
			
	    });

		
	});
</script>