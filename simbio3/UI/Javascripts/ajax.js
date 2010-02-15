/**
 * Arie Nugraha 2010
 * Simbio AJAX related functions
 *
 * Require : jQuery library
 */

jQuery.extend({
    ajaxHistory: new Array(),
    addAjaxHistory: function(strURL, strElement) {
        jQuery.ajaxHistory.unshift({url: strURL, elmt: strElement});
        // delete the last element
        if (jQuery.ajaxHistory.length > 3) {
            jQuery.ajaxHistory.pop();
        }
    },
    ajaxPrevious: function() {
        if (jQuery.ajaxHistory.length < 1) {
            return;
        }
        var moveBack = 1;
        if (arguments[0] != undefined) {
            moveBack = arguments[0];
        }
        if (moveBack > jQuery.ajaxHistory.length) {
            moveBack = 1;
        }
        if (jQuery.ajaxHistory.length == 1) {
            top.location.href = location.pathname + location.search;
            return;
        }
        jQuery(jQuery.ajaxHistory[moveBack].elmt).load(jQuery.ajaxHistory[moveBack].url);
    }
});

/**
 * Function to Set AJAX content
 *
 * @param       string      strSelector : string of CSS and XPATH selector
 * @param       string      strURL : URL of AJAX request
 * @return      void
 */
jQuery.fn.simbioAJAX = function(strURL, params)
{
    var options = {
        method: 'get',
        insertMode: 'replace',
        addData: '',
        returnType: 'html',
        loadingMessage: 'LOADING CONTENT... PLEASE WAIT' };
    jQuery.extend(options, params);

    var ajaxContainer = $(this);

    // callbacks set
    if (ajaxContainer.find('#loading').length < 1) {
        // create loading element dinamically
        ajaxContainer.prepend('<div style="display: none; position: absolute: top: 0; left: 0; padding: 5px; background: #fc0; font-weight: bold;" id="loading"></div>');
    }
    $("#loading").html(options.loadingMessage);
    $("#loading").ajaxStart(function(){ $(this).fadeIn(500); });
    $("#loading").ajaxSuccess(function(){
        $(this).html('<div>REQUEST COMPLETED!</div>');
        // no history on post AJAX request
        if (options.method != 'post') {
            var historyURL = strURL;
            if (options.addData.length > 0) {
                var addParam = options.addData;
                if (Array.prototype.isPrototypeOf(options.addData)) {
                    addParam = jQuery.param(options.addData);
                }
                if (historyURL.indexOf('?', 0) > -1) {
                    historyURL += '&' + addParam;
                } else {
                    historyURL += '?' + addParam;
                }
            }
            jQuery.addAjaxHistory(historyURL, ajaxContainer[0]);
        }
    });
    $("#loading").ajaxStop(function(){ $(this).unbind().fadeOut(500); });
    $("#loading").ajaxError(function(event, request, settings){ $(this).append("<div class=\"error\">Error requesting page : <strong>" + settings.url + "</strong></div>");})

    // send AJAX request
    var ajaxResponse = $.ajax({
        type : options.method, url : strURL,
        data : options.addData, async: false }).responseText;

    // add to elements
    if (options.insertMode == 'before') {
        ajaxContainer.prepend(ajaxResponse);
    } else if (options.insertMode == 'after') {
        ajaxContainer.append(ajaxResponse);
    } else { ajaxContainer.html(ajaxResponse).hide().fadeIn('slow'); }

    return ajaxContainer;
}
