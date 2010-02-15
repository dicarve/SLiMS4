/**
 * Arie Nugraha 2010 : dicarve@yahoo.com
 * This script is licensed under GNU GPL License 3.0
 */

/**
 * Function to initialize Rich Text Editor
 */
var initRTE = function() {
    var contentBody = jQuery('#content_body');
    var parentForm = contentBody.parents('form');
    contentBody.parent().find('.grippie').remove();
    var richTextEditor = contentBody.rte({width: '99%',height: 200, controls_rte: rte_toolbar, controls_html: html_toolbar});
}
