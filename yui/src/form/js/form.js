/**
 * JavaScript for form editing date conditions.
 *
 * @module moodle-availability_credit-form
 */
M.availability_credit = M.availability_credit || {};

/**
 * @class M.availability_credit.form
 * @extends M.core_availability.plugin
 */
M.availability_credit.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} currencies Array of currency_code => localised string
 */
M.availability_credit.form.initInner = function(currencies) {
    this.currencies = currencies;
};

M.availability_credit.form.getNode = function(json) {
    var selected_string = '';

    html += '<div><label>';
    html += M.util.get_string('cost', 'availability_credit');
    html += '<input name="cost" type="text" /></label></div>';

    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values based on the value from the JSON data in Moodle
    // database. This will have values undefined if creating a new one.
    if (json.businessemail) {
        node.one('input[name=businessemail]').set('value', json.businessemail);
    }
    if (json.cost) {
        node.one('input[name=cost]').set('value', json.cost);
    }

    // Add event handlers (first time only).
    if (!M.availability_credit.form.addedEvents) {
        M.availability_credit.form.addedEvents = true;

        var root = Y.one('.availability-field');

        root.delegate('change', function() {
                // The key point is this update call. This call will update
                // the JSON data in the hidden field in the form, so that it
                // includes the new value of the checkbox.
                M.core_availability.form.update();
        }, '.availability_credit input');
    }

    return node;
};

M.availability_credit.form.fillValue = function(value, node) {
    // This function gets passed the node (from above) and a value
    // object. Within that object, it must set up the correct values
    // to use within the JSON data in the form. Should be compatible
    // with the structure used in the __construct and save functions
    // within condition.php.

    value.cost = this.getValue('cost', node);

};

/**
 * Gets the numeric value of an input field. Supports decimal points (using
 * dot or comma).
 *
 * @method getValue
 * @return {Number|String} Value of field as number or string if not valid
 */
M.availability_credit.form.getValue = function(field, node) {
    // Get field value.
    var value = node.one('input[name=' + field + ']').get('value');

    // If it is not a valid positive number, return false.
    if (!(/^[0-9]+([.,][0-9]+)?$/.test(value))) {
        return value;
    }

    // Replace comma with dot and parse as floating-point.
    var result = parseFloat(value.replace(',', '.'));
    return result;
};

M.availability_credit.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    if ((value.cost !== undefined && typeof(value.cost) === 'integer') || value.cost <= 0 ) {
        errors.push('availability_credit:error_cost');
    }
};
