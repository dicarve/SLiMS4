/**
 * JQuery method to bind all Admin module related event
 */
jQuery.fn.registerAdminEvents = function() {
    // cache container
    var container = jQuery(this);

    var headerBlock = jQuery('.header-block').clone(true); jQuery('.header-block').remove();
    if (headerBlock.length > 1) {
        container.before(headerBlock[1]);
    } else { container.before(headerBlock[0]); }


    // change all anchor behaviour to AJAX in main content
    container.parent().find('a').not('.no-ajax').click(function(evt) {
        evt.preventDefault();
        // get anchor href
        var url = jQuery(this).attr('href');
        // set ajax
        container.simbioAJAX(url, {addData: {ajaxload: 1}});
    });

    // set all table with class datagrid
    container.find('table.datagrid').each( function() {
        jQuery(this).simbioTable();
        // register uncheck click event
        jQuery('.uncheck-all').click(function() {
            jQuery.unCheckAll('.datagrid');
        });
        // register check click event
        jQuery('.check-all').click(function() {
            jQuery.checkAll('.datagrid');
        });
    });

    // set all textarea to be resizeable
    container.find('textarea:not(.processed)').TextAreaResizer();

    // set all text with class dateInput to date input
    container.find('.dateInput').date_input();

    // register AJAX submit button
    var validated = true;
    container.find('.form-submit-ajax').click( function(evt) {
        evt.preventDefault();
        // get parent form
        var parentForm = jQuery(this).parents('form');
        // validate form
        parentForm.find('.required').each( function() {
            var elm = jQuery(this);
            if (!elm.val()) {
                alert('Field marked with (*) must be filled/can\'t be left empty!'+"\n"+'Please fill with appropriate value!');
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

    // unlock form button
    container.find('.form-unlock').unbind('click').click( function(evt) {
        evt.preventDefault();
        var unlock = jQuery(this);
        unlock.parents('form').enableForm();
    });

    // form cancel button
    container.find('.form-cancel').unbind('click').click( function(evt) {
        evt.preventDefault();
        jQuery.ajaxPrevious();
    });

    // datagrid submit button
    container.find('.datagrid-submit').unbind('click').click( function(evt) {
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

    // search form action
    container.parent().find('input#search').unbind('click').click( function(evt) {
        evt.preventDefault();
        // get parent form
        var parentForm = jQuery(this).parents('form');
        // check keyword
        if (!parentForm.find('#keywords').val()) {
            alert('Please supply one or more keyword(s) to search!');
            return;
        }
        var formData = parentForm.serialize(); formData += '&'+jQuery(this).attr('name')+'='+jQuery(this).attr('value');
        var formMethod = parentForm.attr('method');
        // create AJAX request
        jQuery(container[0]).simbioAJAX('index.php', {addData: formData, method: formMethod});
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
