/**
 * Arie Nugraha 2010 : dicarve@yahoo.com
 * This script is licensed under GNU GPL License 3.0
 */

/**
 * JQuery method to bind all Admin module related event
 */
jQuery.fn.registerAdminEvents = function() {
    // cache AJAX container
    var container = jQuery(this);

    // change all anchor behaviour to AJAX in main content
    container.find('a').not('.no-ajax').click(function(evt) {
        evt.preventDefault();
        // get anchor href
        var url = jQuery(this).attr('href');
        // set ajax
        container.simbioAJAX(url, {addData: {ajaxload: 1}});
    });

    // header block move out from AJAX container
    var headerBlock = container.find('.header-block');
    if (headerBlock.length > 0) {
        var headerBlockClone = headerBlock.clone(true);
        container.prevAll('.header-block').remove();
        headerBlockClone.insertBefore(container); headerBlock.remove();
    }

    // set all table with class datagrid
    container.find('table.datagrid').each(function() {
        var datagrid = jQuery(this);
        datagrid.simbioTable();
        // register uncheck click event
        jQuery('.uncheck-all').click(function() {
            jQuery.unCheckAll('.datagrid');
        });
        // register check click event
        jQuery('.check-all').click(function() {
            jQuery.checkAll('.datagrid');
        });
        // set all row to show detail when double clicked
        datagrid.find('tr').each( function() {
            var tRow = jQuery(this);
            var rowLink = tRow.css({'cursor' : 'pointer'}).find('a');
            if (rowLink[0] != undefined) {
                tRow.dblclick(function() {jQuery(rowLink[0]).trigger('click')});
            }
        });
        // unregister event for table-header
        jQuery('.table-header', datagrid).parent().unbind();
    });

    // set all textarea to be resizeable
    container.find('textarea:not(.processed)').TextAreaResizer();

    // set all text with class dateInput to date input
    container.find('.dateInput').date_input();

    // register form AJAX submit button action
    var validated = true;
    container.find('.form-submit-ajax').click(function(evt) {
        evt.preventDefault();
        // get parent form
        var parentForm = jQuery(this).parents('form');
        // validate form
        parentForm.find('.required').each( function() {
            var elm = jQuery(this);
            if (!elm.val() && elm.context.nodeName != 'IFRAME') {
                elm.css({'border' : '2px solid #f00'});
                validated = false;
                return;
            }
        });

        // stop if not validated
        if (!validated) {
            return;
        }

        // get form data and attributes
        var formData = parentForm.serialize(); formData += '&'+jQuery(this).attr('name')+'='+jQuery(this).attr('value');
        var formURI = parentForm.attr('action');
        var formMethod = parentForm.attr('method');
        // create AJAX request
        jQuery(container[0]).simbioAJAX(formURI, {addData: formData, method: formMethod});
    });
    if (!validated) {
        alert('Field marked with (*) must be filled/can\'t be left empty!'+"\n"+'Please fill with appropriate value!');
    }

    // unlock form button action
    container.find('.form-unlock').unbind('click').click( function(evt) {
        evt.preventDefault();
        var unlock = jQuery(this);
        unlock.parents('form').enableForm();
    });

    // form cancel button action
    container.find('.form-cancel').unbind('click').click( function(evt) {
        evt.preventDefault();
        jQuery.ajaxPrevious();
    });

    // datagrid submit button action
    container.find('.datagrid-submit').unbind('click').click(function(evt) {
        evt.preventDefault();
        // get parent form
        var parentForm = jQuery(this).parents('form');
        var actionOption = parentForm.find('.datagrid-option option:selected').text();
        var actionOptionVal = parentForm.find('.datagrid-option').val();
        if (actionOptionVal == '0' || jQuery.trim(actionOptionVal) == '') {
            alert('Please choose action to do from the dropdown menu!');
            return;
        }
        var confirmAction = confirm('Are you sure want to ' + actionOption + '?');
        if (confirmAction) {
            var formData = parentForm.serialize();
            var formMethod = parentForm.attr('method');
            // create AJAX request
            jQuery(container[0]).simbioAJAX('index.php?p=' + actionOptionVal, {addData: formData, method: formMethod});
        }
    });

    // header search form action
    container.parent().find('input#search').unbind('click').click(function(evt) {
        evt.preventDefault();
        // get parent form
        var parentForm = jQuery(this).parents('form');
        // check keyword
        if (!parentForm.find('#keywords').val()) {
            alert('Please supply one or more keyword(s) to search!');
            return;
        }
        var formData = parentForm.serialize(); formData += '&noheader=1&' + '&'+jQuery(this).attr('name')+'='+jQuery(this).attr('value');
        var formMethod = parentForm.attr('method');
        // create AJAX request
        jQuery(container[0]).simbioAJAX('index.php', {addData: formData, method: formMethod});
    });

    // set searchable text field
    container.find('.searchable').keyup( function(evt) {
        // cache search field
        var searchField = jQuery(this);
        var dropDownID = searchField.attr('id') + '-dd';
        // append dynamic drop down list
        var ajaxList = jQuery('#'+dropDownID);
        if (ajaxList.length < 1) {
            ajaxList = jQuery('<ul id="' +dropDownID+ '" class="ajax-dropdown"><li>SEARCH...</li></ul>'); ajaxList.insertAfter(searchField);
        }
        // register event when clicked outside the list or esc button pressed so the list is hidden
        jQuery('body').not('#'+dropDownID).click( function() {ajaxList.fadeOut('fast');} );
        // jQuery('body').keyup( function(evt) { if(evt.keyCode == 27) {ajaxList.fadeOut('fast'); return;} } );
        // get current text field value
        var fieldVal = searchField.val();
        searchVal = fieldVal.replace(/^.+;/, '');
        // get text field width
        var fieldW = searchField.css('width');
        // get search attribute to set URL
        var searchURL = 'index.php?p=' + searchField.attr('search');
        // unbind all loading status notification
        jQuery("#loading").unbind();
        // send AJAX request and get JSON value
        jQuery.getJSON(searchURL, {search: searchVal}, function(data) {
            var listItem = '';
            jQuery.each(data, function(l, item) {
                listItem += '<li><a class="ajax-dd-item" value="' + item.listvalue + '">'+ item.listtext + '</a></li>';
            });
            // if there is an item(s)
            if (listItem) {
                ajaxList.css({'position' : 'absolute', 'width' : fieldW});
                ajaxList.html(listItem).slideDown('fast');
                // register click event on each item
                ajaxList.find('.ajax-dd-item').click( function() {
                    var itemText = jQuery(this).text();
                    searchField.val(fieldVal.replace(/[^;]+$/, '') + itemText); ajaxList.fadeOut('fast');
                });
                // register keydown and keyup event
                searchField.keydown( function(evt) {
                    if (evt.keyCode == 38) {
                        // go up
                    } else if (evt.keyCode == 40) {
                        // go down
                    }
                });
            } else {
                // hide list if there is no item(s)
                ajaxList.hide();
            }
        });
    });

    return container;
}

/**
 * JQuery method to unbind all Admin module related event
 */
jQuery.fn.unRegisterAdminEvents = function() {
    var container = jQuery(this);
    // unbind all event handlers
    container.find('a,table,tr,td,input,textarea,div').unbind();
    return container;
}

// set all navigation links behaviour to AJAX
jQuery('document').ready(function() {
    jQuery('#navigation-block .navigation').click(function(evt) {
        evt.preventDefault();
        // remove all .active anchor
        jQuery('#navigation-block .navigation').removeClass('active');
        // add active class on currently clicked anchor
        jQuery(this).addClass('active');
        // get anchor href
        var url = jQuery(this).attr('href');
        // set ajax
        jQuery('#admin-main-content').simbioAJAX(url, {addData: {ajaxload: 1}});
    });

    jQuery('#admin-main-content').registerAdminEvents();
});
