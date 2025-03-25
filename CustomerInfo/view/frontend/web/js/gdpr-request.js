define([
    'jquery',
    'ko',
    'mage/url'
], function($, ko, urlBuilder) {
    'use strict';

    return function(config) {
        let gdprEmail = config.gdprEmail || '';

        $('#gdprRequestButton').on('click', function() {
            if (confirm('Are you sure you want to request data deletion?')) {
                $.ajax({
                    url: urlBuilder.build('customerinfo/info/gdprrequest'),
                    type: 'POST',
                    data: { email: gdprEmail },
                    dataType: 'json',
                    success: function(response) {
                        // Check if the response indicates success
                        if (response.success) {
                            console.log(response.message);
                        } else {
                            console.log('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        console.log('An error occurred. Please try again.');
                    }
                });
            }
        });
    };
});
