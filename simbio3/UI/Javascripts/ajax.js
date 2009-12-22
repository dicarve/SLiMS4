/**
 * Arie Nugraha 2009
 * Simbio AJAX related functions
 *
 * Require : jQuery library
 */


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

    // callbacks set
    if ($(document.body).find('#loading').length < 1) {
        // create loading element dinamically
        $(document.body).append('<div id="loading"></div>');
    }
    $("#loading").html(options.loadingMessage);
    $("#loading").ajaxStart(function(){ $(this).show(); });
    $("#loading").ajaxSuccess(function(){ $(this).html('<div>REQUEST COMPLETED!</div>'); });
    $("#loading").ajaxStop(function(){ $(this).fadeOut('slow'); });
    $("#loading").ajaxError(function(request, settings){ $(this).append("<div class=\"error\">Error requesting page : " + settings.url + "</div>");})

    // send AJAX request
    var ajaxResponse = $.ajax({
        type : options.method, url : strURL,
        data : options.addData, async: false }).responseText;

    // fading out current element
    $(this).fadeOut('fast');
    // add to elements
    if (options.insertMode == 'before') {
        $(this).prepend(ajaxResponse);
    } else if (options.insertMode == 'after') {
        $(this).append(ajaxResponse);
    } else { $(this).html(ajaxResponse).fadeIn('slow'); }
}
