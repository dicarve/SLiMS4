/**
 * Arie Nugraha 2009
 * Simbio AJAX related functions
 *
 * Require : jQuery library
 */

jQuery.extend({
    ajaxHistory: new Array(),
    addAjaxHistory: function(strURL, strElement) {
        jQuery.ajaxHistory.unshift({url: strURL, elmt: strElement});
        // delete the last element
        if (jQuery.ajaxHistory.length > 10) {
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
        } else if (moveBack > jQuery.ajaxHistory.length) {
            moveBack = 1;
        } else if (jQuery.ajaxHistory.length == 1) {
            top.location.href = location.pathname + location.search;
            return;
        }
        jQuery(jQuery.ajaxHistory[moveBack].elmt).simbioAJAX(jQuery.ajaxHistory[moveBack].url);
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
    options = {
        method: 'get',
        insertMode: 'replace',
        addData: {},
        returnType: 'html',
        loadingMessage: 'LOADING CONTENT... PLEASE WAIT'
    };
    jQuery.extend(options, params);

    var ajaxContainer = $(this);

    // callbacks set
    if (ajaxContainer.find('#loading').length < 1) {
        // create loading element dinamically
        ajaxContainer.prepend('<div style="display: none; position: absolute: top: 0; left: 0; padding: 5px; background: #fc0; font-weight: bold;" id="loading">LOADING CONTENT, PLEASE WAIT...</div>');
    }
    $("#loading").html(options.loadingMessage);
    $("#loading").ajaxStart(function(){ $(this).fadeIn(500); });
    $("#loading").ajaxSuccess(function(){
        $(this).html('<div>REQUEST COMPLETED!</div>');
        var historyURL = strURL;
        if (strURL.indexOf('?', 0) > -1 && options.method == 'get') {
            historyURL += '&' + options.addData;
        } else {
            historyURL += '?' + options.addData;
        }
        jQuery.addAjaxHistory(historyURL, ajaxContainer[0]);
    });
    $("#loading").ajaxStop(function(){ $(this).fadeOut(2000); });
    $("#loading").ajaxError(function(request, settings){ $(this).append("<div class=\"error\">Error requesting page : " + settings.url + "</div>");})

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
