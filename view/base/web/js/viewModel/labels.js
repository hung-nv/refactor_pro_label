define([
    'jquery',
    'knockout'
], function ($, ko) {
    'use strict';

    /**
     * [countDecimals description]
     * https://stackoverflow.com/a/17369245
     *
     * @param  {Float} value
     * @return {Number}
     */
    function countDecimals(value) {
        var parts = value.toString().split('.');

        return parts.length < 2 ? 0 : parts[1].length;
    }

    /**
     * Round value using roundValue and roundMethod
     *
     * @param  {float} value
     * @param  {int} roundValue
     * @param  {String} roundMethod
     * @return {float}
     */
    function roundNumber(value, roundValue, roundMethod) {
        var newValue;

        roundValue = roundValue || 1;
        newValue = Math[roundMethod](value / roundValue) * roundValue;

        return newValue.toFixed(countDecimals(roundValue));
    }

    /**
     * Map all properties of object as observable for viewModel
     *
     * @param  {Object} object
     * @param  {Object} viewModel
     * @return void
     */
    function koMapping(object, viewModel) {
        $.each(object, function (key, value) {
            viewModel[key] = ko.observable(value);
        });
    }

    /**
     * Image on load function to add image to label
     *
     * @param  {Event} event
     * @return void
     */
    function imageOnload(event) {
        var koLabel = this,
            img = event.target;

        koLabel.imageCss(
            'background-image: url(' + img.src + '); ' +
            'width: ' + img.width + 'px; ' +
            'height: ' + img.height + 'px; '
        );
    }

    /**
     * Update image css for labels when label image is changed
     *
     * @param  {String} newValue
     * @return void
     */
    function updateImageCss(newValue) {
        var koLabel = this,
            img;

        if (newValue) {
            img = new Image();
            img.onload = $.proxy(imageOnload, koLabel);
            img.src = newValue;
        } else {
            koLabel.imageCss('');
        }
    }

    /**
     * Process label text
     *
     * @return {String}
     */
    function processText() {
        var koLabel = this,
            varValue,
            newValue;

        newValue = koLabel.text ? koLabel.text() : '';
        $.each(koLabel.root.predefinedVars, function (predefinedVar, value) {
            if (newValue.indexOf(predefinedVar) > -1) {
                varValue = isNaN(value) || value === '' ?
                    value :
                    roundNumber(
                        value,
                        koLabel['round_value'] ? koLabel['round_value']() : 1,
                        koLabel['round_method'] ? koLabel['round_method']() : 'round'
                    );
                newValue = newValue.replace(new RegExp(predefinedVar, 'g'), varValue);
            }
        });

        return newValue;
    }

    /**
     * Collect all classes into one string.
     *
     * @return {String}
     */
    function prepareCssClasses() {
        var koLabel = this;

        return 'prolabel' +
            (koLabel['css_class'] ? ' ' + koLabel['css_class']() : '');
    }

    /**
     * ko ViewModel for labels.
     *
     * Structure of labelsData parameter
     *
     * [
     *     {
     *         position: 'position1',
     *         items: [label1 (Object), label2 (Object) .. labelN (Object)]
     *     },
     *     {
     *         position: 'position2',
     *         items: [label1 (Object), label2 (Object) .. labelN (Object)]
     *     }
     *     ...
     * ]
     *
     * @param  {Array} labelsData
     * @param  {Object} predefinedVars
     */
    return function (labelsData, predefinedVars) {
        var self = this;

        self.predefinedVars = predefinedVars || {};
        self.labelsData = [];
        $.each(labelsData, function () {
            var data = {};

            data.position = ko.observable(this.position);
            data.items = [];
            $.each(this.items, function () {
                var model = {};

                this.imageCss = '';
                this.image = this.image || '';
                this.custom = this.custom || '';
                koMapping(this, model);

                if (!$.isEmptyObject(model)) {
                    model.root = self;
                    model.textProcessed = ko.pureComputed(processText, model);
                    model.cssClasses = ko.pureComputed(prepareCssClasses, model);
                    model.image.subscribe(updateImageCss, model);
                    $.proxy(updateImageCss, model)(model.image());
                    data.items.push(model);
                }
            });

            self.labelsData.push(data);
        });
    };
});
