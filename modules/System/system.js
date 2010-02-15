/**
 * Arie Nugraha 2010 : dicarve@yahoo.com
 * This script is licensed under GNU GPL License 3.0
 */

/**
 * JQuery method to bind all Admin module related event
 */
jQuery.fn.registerSystemEvents = function() {
    var container = jQuery(this);
    // register check click event
    jQuery('#install-all').click(function() { jQuery.checkAll('#datagrid'); });
    return container;
}

// set all navigation links behaviour to AJAX
jQuery('document').ready(function() {
    jQuery('#admin-main-content').registerSystemEvents();
});
