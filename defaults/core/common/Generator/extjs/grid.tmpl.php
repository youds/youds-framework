<?php

// unique id for our grid (where loaded more than once)
if (!isset($config['id']))
    $id = uniqid();
else
    $id = $config['id'];

?>

<div id="grid<?php echo $id; ?>" style="width:670px;margin:auto"></div>
<script>

	Ext.onReady(function() {

        Ext.require([
            'Ext.grid.*',
            'Ext.data.*',
            'Ext.util.*',
            'Ext.tip.QuickTipManager',
            'Ext.grid.feature.Grouping'
        ]);
        statusBar<?php echo $id; ?> = new Ext.ux.StatusBar({
            name: 'searchStatusBar',
            style: {
                backgroundColor: '#f5f5f5'
            },
            defaults: {
                style: {
                    borderColor: '#a1bce4',
                    color: '#ffffff',
                    padding: '4px 8px',
                    borderRadius: '3px',
                    cursor: 'pointer'
                }
            },
            items: [
                <?php if (!empty($config['statusBar'])): foreach ($config['statusBar'] as $statusBar): ?>
                {
                text: '<?php echo $statusBar['name']; ?>',
                handler: function () {
                    var selected = grid<?php echo $id; ?>.getSelectionModel().getSelection();
                    if (selected.length) {
                        // Handle delete action
                        Ext.Ajax.request({
                            url: '<?php echo $statusBar['url']; ?>',
                            method: 'POST',
                            params: {
                                <?php foreach($statusBar['params'] as $param): ?>
                                <?php echo $param; ?>: selected[0].get('<?php echo $param; ?>'),
                                <?php endforeach; ?>
                            },
                            success: function (response) {
                                var result = Ext.decode(response.responseText);
                                if (result.success) {
                                    grid<?php echo $id; ?>.store.remove(selected[0]);
                                } else {
                                    Ext.Msg.alert('Error', result.message || 'Operation failed');
                                }
                            },
                            failure: function () {
                                Ext.Msg.alert('Error', 'Server error occurred');
                            }
                        });

                    }
                },
            },<?php endforeach; endif; ?>
            {
                text: 'Search Grid',
                enableToggle: true,
                handler: function () {
                    if (!this.clickCount) {
                        this.clickCount = 1;
                    }
                    this.searchBar = this.up().up().down('toolbar[name=searchBar]')
                    if ((this.clickCount / 2) === parseInt(this.clickCount / 2)) {
                        this.searchBar.setHidden(true);
                    } else {
                        this.searchBar.setHidden(false);
                        var textField = this.up().up().down('textfield[name=searchField]');
                        if (textField) {
                            textField.focus();
                        }
                    }
                    this.clickCount++;
                }
            }],
        });
        
        /**
         * A GridPanel class with live search support.
         */
        Ext.define('Ext.ux.LiveSearchGridPanel', {
            extend: 'Ext.grid.Panel',
            requires: [
                'Ext.toolbar.TextItem',
                'Ext.form.field.Checkbox',
                'Ext.form.field.Text',
                'Ext.ux.statusbar.StatusBar'
            ],
            /**
             * @private
             * search value initialization
             */
            searchValue: null,
            /**
             * @private
             * The matched positions from the most recent search
             */
            matches: [],
            /**
             * @private
             * The current index matched.
             */
            currentIndex: null,
            /**
             * @private
             * The generated regular expression used for searching.
             */
            searchRegExp: null,
            /**
             * @private
             * Case sensitive mode.
             */
            caseSensitive: false,
            /**
             * @private
             * Regular expression mode.
             */
            regExpMode: false,
            /**
             * @cfg {String} matchCls
             * The matched string css classe.
             */
            matchCls: 'x-livesearch-match',
            defaultStatusText: 'No Results Found',
            // Component initialization override: adds the top and bottom toolbars and setup
            // headers renderer.
            initComponent: function () {
                var me = this;
                me.tbar = Ext.create('Ext.toolbar.Toolbar', {
                    name: 'searchBar',
                    listeners: {
                        click: {
                            element: 'el', // bind to the underlying element
                            fn: function () {
                            }
                        },
                        focusenter: {
                            element: 'el', // bind to the underlying element
                            fn: function () {
                            }
                        }
                    },
                    items: [
                        {
                            xtype: 'tbtext',
                            html: 'Search'
                        },
                        {
                            xtype: 'button',
                            text: '&laquo;',
                            tooltip: 'Find Previous Row',
                            handler: me.onPreviousClick,
                            scope: me
                        },
                        {
                            xtype: 'textfield',
                            name: 'searchField',
                            hideLabel: true,
                            width: 140,
                            listeners: {
                                change: {
                                    fn: me.onTextFieldChange,
                                    scope: this,
                                    buffer: 500
                                },

                            }
                        },
                        {
                            xtype: 'button',
                            text: '&raquo;',
                            tooltip: 'Find Next Row',
                            handler: me.onNextClick,
                            scope: me
                        },
                        '-',
                        {
                            xtype: 'checkbox',
                            hideLabel: true,
                            margin: '0 0 0 4px',
                            handler: me.regExpToggle,
                            scope: me
                        },
                        'Regular Expression',
                        {
                            xtype: 'checkbox',
                            hideLabel: true,
                            margin: '0 0 0 4px',
                            handler: me.caseSensitiveToggle,
                            scope: me
                        },
                        'Case Sensitive'
                    ]
                })
                me.callParent(arguments);

            },
            // afterRender override: it adds textfield and statusbar reference and start monitoring
            // keydown events in textfield input
            afterRender: function () {
                var me = this;
                me.callParent(arguments);
                me.textField = me.down('textfield[name=searchField]');
                me.statusBar = statusBar<?php echo $id; ?>;
                me.searchBar = me.down('toolbar[name=searchBar]');
                me.view.on('cellkeydown', me.focusTextField, me);
                //me.statusBar.setHidden(true);
                me.searchBar.setHidden(true);
                this.showingSearchBar = false;
                this.textField = me.down('textfield[name=searchField]');
            },
            focusTextField: function (view, td, cellIndex, record, tr, rowIndex, e, eOpts) {
                if (e.getKey() === e.S) {
                    e.preventDefault();
                    this.textField.focus();
                }
            },
            // detects html tag
            tagsRe: /<[^>]*>/gm,
            // DEL ASCII code
            tagsProtect: '\x0f',
            displaySearchBar: function () {
                var me = this;
                me.statusBar = statusBar<?php echo $id; ?>;
                me.searchBar = me.down('toolbar[name=searchBar]');
                me.statusBar.setHidden(false);
                me.searchBar.setHidden(false);
            },
            hideSearchBar: function () {
                var me = this;
                me.statusBar = statusBar<?php echo $id; ?>;
                me.searchBar = me.down('toolbar[name=searchBar]');
                me.statusBar.setHidden(true);
                me.searchBar.setHidden(true);
            },

            /**
             * In normal mode it returns the value with protected regexp characters.
             * In regular expression mode it returns the raw value except if the regexp is invalid.
             * @return {String} The value to process or null if the textfield value is blank or invalid.
             * @private
             */
            getSearchValue: function () {
                var me = this,
                    value = me.textField.getValue();
                if (value === '') {
                    return null;
                }
                if (!me.regExpMode) {
                    value = Ext.String.escapeRegex(value);
                } else {
                    try {
                        new RegExp(value);
                    } catch (error) {
                        me.statusBar.setStatus({
                            text: error.message,
                            iconCls: 'x-status-error'
                        });
                        return null;
                    }
                    // this is stupid
                    if (value === '^' || value === '$') {
                        return null;
                    }
                }
                return value;
            },

            /**
             * Finds all strings that matches the searched value in each grid cells.
             * @private
             */
            onTextFieldChange: function () {
                var me = this,
                    count = 0,
                    view = me.view,
                    columns = me.visibleColumnManager.getColumns();
                view.refresh();


                // reset the statusbar
                me.statusBar.setStatus({
                    text: '',
                    iconCls: ''
                }).show();
                me.searchValue = me.getSearchValue();
                me.matches = [];
                me.currentIndex = null;
                if (me.searchValue !== null) {
                    me.searchRegExp = new RegExp(me.getSearchValue(), 'g' + (me.caseSensitive ? '' : 'i'));
                    me.store.each(function (record, idx) {
                        var node = view.getNode(record);
                        if (node) {
                            Ext.Array.forEach(columns, function (column) {
                                var cell = Ext.fly(node).down(column.getCellInnerSelector(), true),
                                    matches, cellHTML, seen;
                                if (cell) {
                                    matches = cell.innerHTML.match(me.tagsRe);
                                    cellHTML = cell.innerHTML.replace(me.tagsRe, me.tagsProtect);
                                    // populate indexes array, set currentIndex, and replace wrap
                                    // matched string in a span
                                    cellHTML = cellHTML.replace(me.searchRegExp, function (m) {
                                        ++count;
                                        if (!seen) {
                                            me.matches.push({
                                                record: record,
                                                column: column
                                            });
                                            seen = true;
                                        }
                                        return '<span class="' + me.matchCls + '">' + m + '</span>';
                                    }, me);
                                    // restore protected tags
                                    Ext.each(matches, function (match) {
                                        cellHTML = cellHTML.replace(me.tagsProtect, match);
                                    });
                                    // update cell html
                                    cell.innerHTML = cellHTML;
                                }
                            });
                        }
                    }, me);
                    // results found
                    if (count) {
                        me.currentIndex = 0;
                        me.gotoCurrent();
                        me.statusBar.setStatus({
                            text: Ext.String.format('{0} match{1} found', count, count === 1 ? '' : 'es'),
                            iconCls: 'x-status-valid'
                        });
                        me.textField.focus();
                    }
                }
                // no results found
                if (me.currentIndex === null) {
                    me.getSelectionModel().deselectAll();
                    me.textField.focus();
                } else {
                    me.textField.focus();
                }
            },

            /**
             * Selects the previous row containing a match.
             * @private
             */
            onPreviousClick: function () {
                var me = this,
                    matches = me.matches,
                    len = matches.length,
                    idx = me.currentIndex;
                if (len) {
                    me.currentIndex = idx === 0 ? len - 1 : idx - 1;
                    me.gotoCurrent();
                }
            },

            /**
             * Selects the next row containing a match.
             * @private
             */
            onNextClick: function () {
                var me = this,
                    matches = me.matches,
                    len = matches.length,
                    idx = me.currentIndex;
                if (len) {
                    me.currentIndex = idx === len - 1 ? 0 : idx + 1;
                    me.gotoCurrent();
                }
            },

            /**
             * Switch to case sensitive mode.
             * @private
             */
            caseSensitiveToggle: function (checkbox, checked) {
                this.caseSensitive = checked;
                this.onTextFieldChange();
            },

            /**
             * Switch to regular expression mode
             * @private
             */
            regExpToggle: function (checkbox, checked) {
                this.regExpMode = checked;
                this.onTextFieldChange();
            },
            privates: {
                gotoCurrent: function () {
                    var pos = this.matches[this.currentIndex];
                    this.getNavigationModel().setPosition(pos.record, pos.column);
                    this.getSelectionModel().select(pos.record);
                    this.textField.focus();
                }
            }
        });

        var gridData<?php echo $id; ?>, store<?php echo $id; ?>;
        Ext.QuickTips.init();

        // sample static data for the store
        gridData<?php echo $id; ?> = [
            <?php
            $html = '';
            foreach ($inner as $row):
                $html .= '[';
                foreach ($row as $property => $cell):
                    $html .= sprintf('\'%s\',', addslashes($cell));
                endforeach;
                $html = substr($html, 0, -1) . '],';
            endforeach;
            $html = substr($html, 0, -1);
            echo $html;
            ?>
        ];

        // create the data store
        store<?php echo $id; ?> = Ext.create('Ext.data.ArrayStore', {
            <?php if (isset($config['group']) && is_string($config['group'])): ?>
            groupField: '<?php echo $config['group']; ?>',
            <?php endif; ?>
            fields: [
                <?php
                $html = '';
                if (isset($attributes['columns']))
                    $columns = $attributes['columns'];
                else
                    $columns = $attributes;

                foreach ($columns as $key => $column):

                    if (is_array($column)):
                        $html .= '{';
                        foreach ($column as $key => $value):
                            if (!isset($config['hidden']) || (is_array($config['hidden']) && !in_array($key, $config['hidden']))):
                                if ($key == 'name' || $key == 'text')
                                    $html .= sprintf('name: \'%s\',', strtolower(preg_replace('/[^\w\d]/', '', $value)));
                                else if (is_bool($value))
                                    $html .= sprintf('%s: %s,', $key, ($value ? 'true' : 'false'));
                                else
                                    $html .= sprintf('%s: \'%s\',', $key, $value);
                            endif;
                        endforeach;

                        $html = substr($html, 0, -1);
                        $html .= '},';

                    else:
                        $columnFormatted = strtolower(preg_replace('/[^\w\s]/', '', str_replace(' ', '', $column)));
                        $html .= sprintf('{ name: \'%s\' }, ', !is_numeric($key)?$key:$columnFormatted);
                    endif;
                endforeach;
                $html = substr($html, 0, -1);
                echo $html;
                ?>
            ],
            data: gridData<?php echo $id; ?>
        });

        // create the Grid, see Ext.
       grid<?php echo $id; ?> = Ext.create('Ext.ux.LiveSearchGridPanel', {
           bufferedRenderer: false,
           store: store<?php echo $id; ?>,
           <?php if (isset($config['group']) && strlen($config['group']) > 0): ?>
           features: [{
               ftype: 'grouping',
               groupHeaderTpl: '{name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})',
               hideGroupedHeader: false,
               startCollapsed: false
           }],
           <?php endif; ?>
           columns: [
               <?php
               $html = '';

               foreach ($columns as $columnsKey => $column):
                   if (is_array($column)):
                       $html .= '{';
                       foreach ($column as $key => $value):
                           if (!isset($config['hidden']) || (is_array($config['hidden']) && !in_array($key, $config['hidden']))):
                               if (is_numeric($key) || $key == 'name' || $key == 'text')
                                   $html .= sprintf('text: \'%s\', dataIndex: \'%s\',', $value, strtolower(preg_replace('/[^\w\d]/', '', $value)));
                               else if ($key == 'renderer' || $key == 'handler')
                                   $html .= sprintf('renderer: %s,', $value);
                               else if (is_bool($value))
                                   $html .= sprintf('%s: %s,', $key, ($value ? 'true' : 'false'));
                               else
                                   $html .= sprintf('%s: \'%s\',', $key, $value);
                           endif;
                       endforeach;
                       $html = substr($html, 0, -1);
                       $html .= '},';
                   else:
                       $columnFormatted = strtolower(preg_replace('/[^\w\s]/', '', str_replace(' ', '', $column)));
                       if (!isset($config['hidden']) || (is_array($config['hidden']) && !in_array(!is_numeric($columnsKey)?$columnsKey:$columnFormatted, $config['hidden']))):
                           $html .= sprintf('{
                                text: \'%s\',
                                flex: 1,
                                sortable: true,
                                dataIndex: \'%s\'
                            },', $column, !is_numeric($columnsKey)?$columnsKey:$columnFormatted);
                       endif;
                   endif;
               endforeach;
               $html = substr($html, 0, -1);
               echo $html;
               ?>
           ],
           listeners: {
               <?php if (isset($config['context'])): ?>
               cellcontextmenu: function (table, td, cellIndex, record, tr, rowIndex, e) {
                   Ext.create('Ext.menu.Menu', {
                       items: [
                           <?php  foreach ($config['context'] as $option): ?>
                           {
                           text: 'Remove Ban',
                           handler: function () {
                               var record = table.getSelectionModel().getSelection()[0];
                               Ext.Ajax.request({
                                   url: '<?php //echo $ro->gen($option['content'], $option['params']); ?>',
                                   method: 'POST',
                                   params: {
                                       action: 'remove',
                                       id: record.get('id')
                                   },
                                   success: function (response) {
                                       var result = Ext.decode(response.responseText);
                                       if (result.success) {
                                           store<?php echo $id; ?>.remove(record);
                                       } else {
                                           Ext.Msg.alert('Error', result.message || 'Failed to remove ban');
                                       }
                                   },
                                   failure: function () {
                                       Ext.Msg.alert('Error', 'Server error occurred while removing ban');
                                   }
                               }
                               );
                           },
                           iconCls: 'removeIcon'
                       },
                       <?php endforeach; ?>
                       ]
                   }).showAt(e.pageX, e.pageY);

                   e.preventDefault();
               },
               <?php endif; ?>
               cellkeydown: function (cell, td, cellIndex, record, tr, rowIndex, e, eOpts) {
                   /*console.log(cell)
                   if (e.getKey() == 70) {
                       if (this.showingSearchBar == false) {
                           this.displaySearchBar();
                           this.showingSearchBar = true;
                       } else {
                           this.hideSearchBar();
                           this.showingSearchBar = false;
                       }
                   }*/
               },
               select: function () {
                   //this.displaySearchBar();
               },
               focusleave: function (object, e, eOpts) {
                   //this.hideSearchBar();
               }
           },
           forceFit: true,
           height: 610,
           split: true,
           region: 'north'
        });

        window.updateStore<?php echo $id; ?> = function (data, uniqueField = false) {
            if (!Array.isArray(data)) {
                console.error('Data must be an array');
                return;
            }

            let storeData = store<?php echo $id; ?>.data.items.map(item => item.data);
            let combinedData = [...storeData];

            if (uniqueField) {
                // Create map of existing records by unique field
                const existingMap = new Map(storeData.map(item => [item[uniqueField], item]));

                // Update/add new records
                data.forEach(newItem => {
                    if (newItem[uniqueField] !== undefined) {
                        existingMap.set(newItem[uniqueField], newItem);
                    }
                });
                combinedData = Array.from(existingMap.values());
            } else {
                // Simply append new data if no unique field
                combinedData = combinedData.concat(data);
            }

            for (i=0;i < combinedData.length;i++) {
                if (uniqueField) {
                    for (a=0;a < data.length;a++) {
                        if (combinedData[i][uniqueField] == data[a][uniqueField])
                            combinedData[i] = data[a]
                    }
                }
            }

            store<?php echo $id; ?>.loadData(combinedData);
        }

        // define a template to use for the detail view
        <?php if (isset($config['window'])): ?>
        dataTplMarkup<?php echo $id; ?> = [
           '<?php echo str_replace(array("\r", "\n"), array('', ''), nl2br($config['window'])); ?>'
        ];

        dataTpl = Ext.create('Ext.Template', dataTplMarkup<?php echo $id; ?>);
        <?php endif; ?>

        Ext.create('Ext.Panel', {
            renderTo: 'grid<?php echo $id; ?>',
            frame: true,
            title: '<?php echo $config['title']; ?>',
            width: 580,
            height: 650,
            layout: 'border',
            items: [
                grid<?php echo $id; ?>,
                {
                    id: 'detailPanel<?php echo $id; ?>',
                    region: 'center',
                    bodyPadding: 7,
                    bodyStyle: "background: #ffffff;",
                    html: 'Please select a row to see additional details.',
                    hidden: true
                }
            ],
            <?php if (isset($config['window'])): ?>bbar: statusBar<?php echo $id; ?><?php endif; ?>
        });
        <?php if (isset($config['window'])): ?>
        // update panel body on selection change
        grid<?php echo $id; ?>.getSelectionModel().on('selectionchange', function(sm, selectedRecord) {
            var detailPanel<?php echo $id; ?> = Ext.getCmp('detailPanel<?php echo $id; ?>');
            var editBtn = statusBar<?php echo $id; ?>.down('button[text=Edit]');
            var deleteBtn = statusBar<?php echo $id; ?>.down('button[text=Delete]');

            if (selectedRecord.length) {
                detailPanel<?php echo $id; ?>.setHidden(false);
                detailPanel<?php echo $id; ?>.update(dataTpl.apply(selectedRecord[0].data));
                grid<?php echo $id; ?>.setHeight(410);
                editBtn.enable();
                deleteBtn.enable();
            } else {
                //detailPanel<?php echo $id; ?>.setHidden(true);
                //grid<?php echo $id; ?>.setHeight(100);
                editBtn.disable();
                deleteBtn.disable();
            }
        });
        <?php endif; ?>

    });
    
    
</script>
