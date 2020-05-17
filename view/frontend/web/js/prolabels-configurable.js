define([
    'jquery',
    './prolabels'
], function ($, Prolabels) {
    'use strict';

    /**
     * Init prolables for configurable product.
     *
     * @param  {Object} config
     * @param  {jQuery} element
     */
    return function (config, element) {
        /**
         * Unset if inited then init with new options.
         *
         * @param  {Number} product
         */
        function reinitProlabels(product) {
            var prolabels = $(element).data('swissupProlabels');

            if (prolabels) {
                prolabels.destroy();
            }

            product = product ? product : config.superProduct;

            if (config.labels[product]) {
                Prolabels(config.labels[product], element);
            }
        }

        reinitProlabels(config.superProduct);

        // Listen options change when swatches disabled.
        $(config.configurableOptions).on('change', function () {
            var configurable = $('#product_addtocart_form').data('mageConfigurable');

            if (configurable) {
                reinitProlabels(configurable.simpleProduct);
            }

        });

        // Listen options change when swatches enabled.
        $(config.swatchOptions).on('change', function (event) {
            var swatchRenderer = $(event.currentTarget).data('mageSwatchRenderer');

            if (swatchRenderer) {
                reinitProlabels(swatchRenderer.getProduct());
            }

        });
    };
});
