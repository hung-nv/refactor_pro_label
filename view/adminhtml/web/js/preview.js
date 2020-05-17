define([
    'jquery',
    'knockout',
    'Swissup_ProLabels/js/renderLabels',
    'Magento_Ui/js/lib/view/utils/async',
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($, ko, RenderLabels) {
    'use strict';

    /**
     * Update label image after FileReader loaded
     *
     * @param  {Event} event
     * @return void
     */
    function updateImage(event) {
        var fileReader = event.target,
            viewModel = this;

        viewModel.labelsData[0].items[0].image(fileReader.result);
    }

    /**
     * Listener for change event of image config option
     *
     * @param  {Event} event
     * @return void
     */
    function onChangeImage(event) {
        var fileReader  = new FileReader(),
            fileInput = event.target;

        // You can read more about FileReader at
        // https://developer.mozilla.org/en-US/docs/Web/API/FileReader/readAsDataURL

        fileReader.addEventListener('load', $.proxy(updateImage, this));
        fileReader.readAsDataURL(fileInput.files[0]);
    }

    /**
     * Get demo predefined variables for label
     *
     * @param  {Object} demoData
     * @return {Object}
     */
    function getDemoPredefinedVars(demoData) {
        var data = {
                '#final_price#': demoData.specialPrice || demoData.price || 0,
                '#special_price#': demoData.specialPrice || 0,
                '#price#': demoData.price || 0,
                '#stock_item#': 5
            };

        data['#discount_amount#'] = data['#price#'] - data['#special_price#'];
        data['#discount_percent#'] = data['#price#'] ?
            (1 - data['#special_price#'] / data['#price#']) * 100
            : 0;

        return data;
    }

    /**
     * @param  {DOMElemwnt} inputFile
     * @return {String}
     */
    function getBackgroundImageUrl(inputFile) {
        var url;

        url = $(inputFile).prev('a').attr('href');

        return url;
    }

    $.widget('swissup.prolabelsPreview', {

        renderLabelsWidget: null,

        options: {
            template: 'Swissup_ProLabels/preview/product-labels',
            adminControl: 'td.value select[id], td.value input[id], td.value textarea[id]',
            controlNameFrom: 'id',
            nameMapping: {
                'custom_style': 'custom'
            },
            imageControlUpdateStrategy: 'even',
            demoData: {}
        },

        /**
         * [_create description]
         */
        _create: function () {
            var labelsData = [];

            labelsData.push({
                position: 'top-left',
                items: [
                    {
                        active: '1',
                        position: '',
                        text: '',
                        image: '',
                        custom: '',
                        'custom_url': '',
                        'round_method': 'round',
                        'round_value': '1',
                        'stock_lower': '',
                        demoData: this.options.demoData
                    }
                ]
            });
            this.renderLabelsWidget = RenderLabels(
                {
                    template: this.options.template,
                    labelsData: labelsData,
                    predefinedVars: getDemoPredefinedVars(this.options.demoData),
                    target: '.preview'
                },
                this.element
            );

            $.async(
                {
                    selector: this.options.adminControl,
                    ctx: this.element.get(0)
                },
                $.proxy(this.applyBinding, this)
            );
        },

        /**
         * [getControlName description]
         *
         * @param  {DOMElement|jQuery} control
         * @return {String}
         */
        getControlName: function (control) {
            var sectionId, name;

            if (this.options.controlNameFrom === 'name') {
                sectionId = this.element.attr('data-index');
            } else {
                sectionId = this.element.attr('id');
            }

            name = $(control)
                .attr(this.options.controlNameFrom)
                .replace(sectionId + '_', '');

            return this.options.nameMapping[name] || name;
        },

        /**
         * Apply data binding to config options
         */
        applyBinding: function (adminControl) {
            var name,
                bindingType,
                binding = {},
                viewModel = this.renderLabelsWidget.getViewModel(),
                uiComponent,
                viewModelNode;

            name = this.getControlName(adminControl);

            if (name === 'image') {
                // set value from image to view model
                viewModel.labelsData[0].items[0][name](
                    getBackgroundImageUrl(adminControl)
                );

                // listen image file change
                if (this.options.imageControlUpdateStrategy === 'async') {
                    $.async(
                        '.preview-image',
                        $(adminControl).closest('.file-uploader').get(0),
                        function (image) {
                            viewModel.labelsData[0].items[0].image(image.src);
                            // listen image remove
                            $.async.remove(
                                image,
                                function () {
                                    viewModel.labelsData[0].items[0].image('');
                                }
                            );
                        }
                    );
                } else {
                    $(adminControl).on(
                        'change',
                        $.proxy(onChangeImage, viewModel)
                    );
                }

                return;
            }

            // set value from input to view model
            viewModelNode = name === 'position' ?
                viewModel.labelsData[0][name] :
                viewModel.labelsData[0].items[0][name];

            if (typeof viewModelNode === 'function') {
                viewModelNode(adminControl.value);
            }

            uiComponent = ko.dataFor($(adminControl).get(0));

            if (uiComponent && uiComponent.value) {
                // There is UI Component for control element.
                // Subscribe to value change of this UI Component
                uiComponent.value.subscribe(viewModelNode);
            } else {
                // UI Component not found. Apply regular KO binding.
                bindingType = $(adminControl).is('input[type="text"], textarea') ?
                    'textInput' :
                    'value';
                binding[bindingType] = viewModelNode;
                ko.applyBindingsToNode($(adminControl).get(0), binding, viewModel);
            }
        }
    });

    return $.swissup.prolabelsPreview;
});
