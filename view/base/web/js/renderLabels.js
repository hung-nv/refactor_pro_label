define([
    'jquery',
    './viewModel/labels',
    'knockout',
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($, LabelsViewModel, ko) {
    'use strict';

    $.widget('swissup.renderLabels', {

        options: {
            template: 'Swissup_ProLabels/labels',
            labelsData: {},
            predefinedVars: {},
            target: '',
            renderMode: 'replaceChildren' // other 'replaceNode'
        },

        viewModel: null,

        /**
         * Add ko template bind and apply ko binding to element
         */
        _create: function () {
            this.viewModel = new LabelsViewModel(
                this.options.labelsData,
                this.options.predefinedVars
            );
            ko.renderTemplate(
                this.options.template,
                this.viewModel,
                {},
                $(this.options.target || this.element, this.element).get(0),
                this.options.renderMode
            );
        },

        /**
         * @return {LabelsViewModel}
         */
        getViewModel: function () {
            return this.viewModel;
        }
    });

    return $.swissup.renderLabels;
});
