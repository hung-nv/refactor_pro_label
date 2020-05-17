define([
    'jquery',
    'Magento_Ui/js/grid/columns/column',
    'Swissup_ProLabels/js/prolabels'
], function ($, Element, Prolabels) {
    'use strict';

    return Element.extend({
        defaults: {
            bodyTmpl: 'Swissup_ProLabels/product/list/columns/prolabel'
        },

        /**
         * Check if label exist.
         *
         * @param {Object} row
         * @return {Boolean}
         */
        labelExists: function (row) {
            return row['extension_attributes'] !== undefined &&
                row['extension_attributes']['swissup_prolabel'] !== undefined;
        },

        /**
         * Render labels
         *
         * @param  {HTMLElement} target
         * @param  {String} optionsJson
         */
        renderLabels: function (target, optionsJson) {
            Prolabels(
                $.parseJSON(optionsJson),
                target
            );
        }
    });
});
