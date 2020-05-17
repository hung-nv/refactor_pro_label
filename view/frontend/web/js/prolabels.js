define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'Swissup_ProLabels/js/renderLabels',
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($, _, RenderLabels) {
    'use strict';

    $.widget('swissup.prolabels', {
        options: {
            parent: null,
            imageLabelsTarget: '',
            imageLabelsInsertion: 'appendTo',
            imageLabelsWrap: true,
            imageLabelsRenderAsync: false,
            contentLabelsTarget: '',
            contentLabelsInsertion: 'appendTo',
            labelsData: {},
            predefinedVars: {}
        },

        /**
         * [_create description]
         */
        _create: function () {
            var baseImageElement,
                contentElement;

            this.containers = {};
            this.renderContext = this.options.parent ?
                this.element.closest(this.options.parent) :
                this.element;

            if (this.options.imageLabelsRenderAsync) {
                $.async(
                    {
                        selector: this.options.imageLabelsTarget,
                        ctx: this.renderContext.get(0)
                    },
                    _.once(this.renderImageLabels.bind(this))
                );
            } else {
                baseImageElement = this.options.imageLabelsTarget ?
                    $(this.options.imageLabelsTarget, this.renderContext) :
                    this.renderContext;
                this.renderImageLabels(baseImageElement.get(0));
            }

            contentElement = $(this.options.contentLabelsTarget, this.renderContext);
            this.renderContentLabels(contentElement.get(0));
        },

        /**
         * Render prolabels for product image.
         *
         * @param  {String} baseImage
         */
        renderImageLabels: function (baseImage) {
            var targetElement,
                insertionMethod,
                options;

            if (this.options.imageLabelsWrap &&
                !$(baseImage).hasClass('prolabels-wrapper')
            ) {
                if ($(baseImage).parent().hasClass('prolabels-wrapper')) {
                    // parent element has wrappr class
                    targetElement = $(baseImage).parent();
                } else {
                    // add prolabels-wrapper
                    targetElement = $(baseImage)
                        .wrap('<div class="prolabels-wrapper"></div>')
                        .parent();
                }
            } else {
                // do not add prolabels-wrapper
                targetElement = $(baseImage);
            }

            options = {
                labelsData: this.getImageLabels(),
                predefinedVars: this.options.predefinedVars //,
                // renderMode: 'replaceNode' -- for some unknown reason
                // there are issues with 'replaceNode' in chrome browser
                // when dev console turned off
            };

            if (targetElement.length) {
                insertionMethod = this.options.imageLabelsInsertion;
                this.containers.imageLabels = $('<div></div>');
                this.containers.imageLabels[insertionMethod](targetElement);
                RenderLabels(options, this.containers.imageLabels);
            }
        },

        /**
         * Render prolabels in product info.
         *
         * @param  {String} outputElement
         */
        renderContentLabels: function (outputElement) {
            var insertionMethod,
                options;

            insertionMethod = this.options.contentLabelsInsertion;
            this.containers.contentLabels = $('<div class="prolabels-content-wrapper"></div>');
            this.containers.contentLabels[insertionMethod]($(outputElement));
            options = {
                labelsData: this.getContentLabels(),
                predefinedVars: this.options.predefinedVars
            };
            RenderLabels(options, this.containers.contentLabels);
        },

        /**
         * @return {Object}
         */
        getImageLabels: function () {
            var data = [];

            $.each(this.options.labelsData, function () {
                if (this.position !== 'content') {
                    data.push(this);
                }
            });

            return data;
        },

        /**
         * @return {Object}
         */
        getContentLabels: function () {
            var data = [];

            $.each(this.options.labelsData, function () {
                if (this.position === 'content') {
                    data.push(this);
                }
            });

            return data;
        },

        /**
         * {@inheritdoc}
         */
        _destroy: function () {
            $.each(this.containers, function () {
                this.remove();
            });

            return this;
        }
    });

    return $.swissup.prolabels;
});
