/**
 * Arie Nugraha 2009
 * Simbio GUI related functions
 *
 * Require : jQuery library
 */


/**
 * jQuery Plugins function to set row event on datagrid table
 *
 * @param       object      an additional option for table
 * @return      jQuery
 *
 * @example usage
 * $('.datagrid').simbioTable();
 * or
 * $('.datagrid').simbioTable({ mouseoverCol: '#bcd4ec', highlightCol: 'yellow' });
 */
jQuery.fn.simbioTable = function(params) {
    // set some options
    options = {
        mouseoverCol: '#bcd4ec',
        highlightCol: 'yellow'
    };
    jQuery.extend(options, params);
    // add non-standar 'row' attribute to indicate row position
    jQuery(this).find('tr').each(function(i) {
            jQuery(this).attr('row', i);
        });

    // event register
    jQuery(this).find('tr').mouseover(function() {
        // on mouse over change background color
        if (!this.highlighted) {
            this.originColor = jQuery(this).css('background-color');
            jQuery(this).css({background : options.mouseoverCol});
        }
    }).mouseout(function() {
        // on mouse over revert background color to original
        if (!this.highlighted) {
            jQuery(this).css('background-color', this.originColor);
        }
    }).click(function(evt) {
        if (!this.originColor) {
            this.originColor = jQuery(this).css('background-color');
        }
        // on click highlight row with new background color
        if (this.highlighted) {
            this.highlighted = false;
            jQuery(this).removeClass('highlighted').removeClass('last-highlighted').css('background-color', this.originColor);
            // uncheck the checkbox on row if exists
            jQuery(this).find('input:checkbox').each(function() {
                this.checked = false;
            });
        } else {
            // set highlighted flag
            this.highlighted = true;
            // check the checkbox on row if exists
            jQuery(this).find('input:checkbox').each(function() {
                this.checked = true;
            });

            // get parent table of row
            var parentTable = jQuery( jQuery(this).parents('table')[0] );

            // get last highlighted row index
            var lastRow = parseInt(parentTable.find('.last-highlighted').attr('row'));
            // get current row index
            var currentRow = parseInt(jQuery(this).attr('row'));

            if (evt.shiftKey) {
                var start = Math.min(currentRow, lastRow);
                var end = Math.max(currentRow, lastRow);
                for (var r = start+1; r <= end-1; r++) {
                    parentTable.find('tr[row=' + r + ']').trigger('click');
                }
            }

            // remove all last-highlighted row class
            parentTable.find('.last-highlighted').removeClass('last-highlighted');
            // highlight current clicked row
            jQuery(this).addClass('highlighted').addClass('last-highlighted').css('background-color', options.highlightCol);

        }
    });

    return jQuery(this);
};


/**
 * Add some utilities function to jQuery namespace
 */
jQuery.extend({
    unCheckAll: function(strSelector) {
        jQuery(strSelector).find('tr').each(function() {
            if ($(this).hasClass('highlighted')) {
                $(this).trigger('click');
            }
        });
    },
    checkAll: function(strSelector) {
        jQuery(strSelector).find('tr').each(function() {
            if (!$(this).hasClass('highlighted')) {
                $(this).trigger('click');
            }
        });
    }
});


/**
 * jQuery Plugins function to make dynamic addition form field
 *
 *
 * @return      jQuery
 */
jQuery.fn.dynamicField = function() {
    var dynFieldClass = this.attr('class');
    this.find('.add').click(function() {
        // get div parent element
        var currentField = jQuery(this).parent();
        var addField = currentField.clone();
        // append remove button and remove ".add" button for additional field
        addField.append(' <a href="#" class="remove-field">Remove</a>').children().remove('.add');
        // add cloned field after
        currentField.after(addField[0]);
        // register event for remove button
        jQuery(document).ready(function() {
            $('.remove-field', this).click(function() {
                // remove field
                var toRemove = jQuery(this).parent().remove();
            });
        });
    });

    return jQuery(this);
}

/**
 * automatic set for all table with class : datagrid
 */
$(document).ready(function() {
    if (jQuery('.datagrid').length > 0) {
        jQuery('.datagrid').simbioTable();
        // register uncheck click event
        jQuery('.uncheck-all').click(function() {
            jQuery.unCheckAll('.datagrid');
        });
        // register check click event
        jQuery('.check-all').click(function() {
            jQuery.checkAll('.datagrid');
        });
    }
});
