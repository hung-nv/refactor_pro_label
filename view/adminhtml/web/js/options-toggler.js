define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param  {Event} event
     */
    function clickListener(event) {
        var $section = $(this).parents('.prolabels-preview');

        $section.toggleClass('show-advanced');

        if ($section.hasClass('show-advanced')) {
            $section.find('.CodeMirror').each(function () {
                this.CodeMirror.refresh();
            });
        }

        event.preventDefault();
    }

    /**
     * @param  {Object}     config
     * @param  {DOMElement} element
     */
    return function (config, element) {
        var $a = $('a', element);

        $a.click(clickListener);
    };
});
