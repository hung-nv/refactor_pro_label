define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    var self,
        url;

    return {
        /**
         * @param  {String} ajaxCallUrl
         * @param  {String} IndexBtn
         * @return void
         */
        init: function (ajaxCallUrl, IndexBtn) {
            self = this;
            url = ajaxCallUrl;
            $(IndexBtn).attr('onclick', 'return false');

            $(IndexBtn).on('click', function () {
                self.indexLabels();

                return false;
            });
        },

        /**
         * Start labels indexing process
         */
        indexLabels: function () {
            $.ajax({
                method: 'POST',
                url: url,
                showLoader: true,
                dataType: 'json',
                data: {
                    'form_key': window.FORM_KEY
                }
            })
            .done(function (data) {
                if (data.error) {
                    alert({
                        title: $.mage.__('Error'),
                        content: data.error
                    });

                    return;
                }

                if (!data.finished) {
                    $('.loading-mask .popup-inner').text(data.loaderText);
                    self.indexLabels();
                } else {
                    location.reload();
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                alert({
                    title: $.mage.__('Error'),
                    content: $.mage.__('An error occured:') + errorThrown
                });
            });
        }
    };
});
