define([
    'jquery'
], function ($) {
    'use strict';

    return function (target) {
        $.validator.addMethod(
            'validate-admin-password',
            function (v) {
                var pass;

                if (v == null) {
                    return false;
                }
                // strip leading and trailing spaces
                pass = v.trim();
                // password is not being changed
                if (pass.length === 0) {
                    return true;
                }

                if (!/[a-z]/i.test(v) || !/[0-9]/.test(v)) {
                    return false;
                }

                return pass.length >= 12;
            },
            $.mage.__('Please enter 12 or more characters, using both numeric and alphabetic.')
        );

        return target;
    };
});
