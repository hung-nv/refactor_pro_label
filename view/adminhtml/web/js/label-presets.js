define([
    'jquery',
    'knockout',
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($, ko) {
    'use strict';

    var predefinedVars = {
        '#discount_amount#': 7.01,
        '#discount_percent#': 22,
        '#final_price#': 24.99,
        '#price#': '32.00',
        '#special_price#': 24.99,
        '#stock_item#': 5
    };


    /**
     * Process label text
     *
     * @return {String}
     */
    function processText(text) {
        $.each(predefinedVars, function (predefinedVar, value) {
            if (text.indexOf(predefinedVar) > -1) {
                text = text.replace(new RegExp(predefinedVar, 'g'), value);
            }
        });

        return text;
    }

    $.widget('swissup.prolabelsPresets', {
        options: {
            url: '',
            template: 'Swissup_ProLabels/presets/dropdown'
        },

        dropdownElement: null,

        /**
         * [_create description]
         */
        _create: function () {
            this._on({
                'click > button': this.showPresets,
                'click .prolabel': this.applyPreset,
            });
            this.element.addClass('initialized');
        },

        /**
         * Get presets from mserver and show then in dropdown
         */
        showPresets: function () {
            $('> button', this.element).hide();
            this.dropdownElement = this.prepareDropdown();
            $.get(
                this.options.url,
                $.proxy(this.updateDropdown, this)
            );
        },

        /**
         * [prepareDropdown description]
         */
        prepareDropdown: function () {
            return $('<div class="dropdown"></div>').appendTo(this.element);
        },

        /**
         * [updateDropdown description]
         *
         * @param  {Object} json
         */
        updateDropdown: function (json) {
            json.q = ko.observable('');
            $.each(
                json.labels,
                function () {
                    this.demoText = processText(this.text);
                }
            );
            ko.renderTemplate(
                this.options.template,
                json,
                {},
                this.dropdownElement.get(0)
            );
        },

        applyPreset: function (event) {
            var prolabel = $(event.currentTarget),
                fieldset = this.element.closest(
                    'fieldset.config, fieldset.admin__fieldset'
                );

            $.each(
                prolabel.data(),
                function (key, value) {
                    var fieldName,
                        checkbox,
                        field;

                    if (key.indexOf('label') === 0) {
                        fieldName = key.replace('label', '').toLowerCase();
                        checkbox = $('[name$="[' + fieldName + '][inherit]"]', fieldset);
                        // uncheck inherit checkbox
                        if (checkbox.prop('checked')) {
                            checkbox.click();
                        }

                        // set value from preset
                        field = $('[name$="[' + fieldName + '][value]"]', fieldset);

                        if (!field.length) {
                            // system label field not found
                            // try to find manual label field
                            fieldName += fieldName === 'custom' ? '_style' : '';
                            field = $('[name$="_' + fieldName + '"]', fieldset);
                        }

                        field.val(value).change();
                    }
                }
            );
        }
    });

    return $.swissup.prolabelsPresets;
});
