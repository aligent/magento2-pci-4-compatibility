define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    return function (config, element) {
        let sessionTimeoutHandle = null,
            sessionLifetime = config.sessionLifetime || 900,
            warningOffset = 60, // Show warning 60 seconds before expiry
            extendSessionUrl = config.extendSessionUrl,
            formKey = config.formKey,
            $modal = $(element),
            $message = $modal.find('#session-warning-message'),
            originalMessage = $message.text();

        /**
         * Initialize and configure the session expiry warning modal
         */
        function initSessionWarningModal() {
            const options = {
                type: 'popup',
                innerScroll: true,
                title: $t('Session Expiring Soon'),
                modalClass: 'modal-admintimeout',
                buttons: [
                    {
                        text: $t('Extend Session'),
                        class: 'action-primary',
                        click: function () {
                            extendSession(this);
                        }
                    },
                    {
                        text: $t('Close'),
                        class: 'action-secondary',
                        click: function () {
                            this.closeModal();
                        }
                    }
                ]
            };

            modal(options, $modal);
        }

        /**
         * Schedule the session warning modal to appear
         */
        function scheduleSessionWarning() {
            // Clear any existing timeout
            if (sessionTimeoutHandle) {
                clearTimeout(sessionTimeoutHandle);
            }

            // Calculate delay: (sessionLifetime - warningOffset) * 1000 milliseconds
            const delay = (sessionLifetime - warningOffset) * 1000;

            sessionTimeoutHandle = setTimeout(function () {
                $modal.modal('openModal');
            }, delay);
        }

        /**
         * Extend the admin session via AJAX
         *
         * @param {Object} modalContext - The modal instance context
         */
        function extendSession(modalContext) {
            // Show loading state
            $message.text($t('Extending your session...'));

            $.ajax({
                url: extendSessionUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    form_key: formKey
                },
                success: function (response) {
                    if (response.success) {
                        // Show a success message
                        $message.text($t('Session extended successfully!'));

                        // Close modal after a short delay
                        setTimeout(function () {
                            modalContext.closeModal();
                            $message.text(originalMessage);

                            // Reschedule the warning modal
                            scheduleSessionWarning();
                        }, 1500);
                    } else {
                        // Show error message
                        $message.text(response.message || $t('Failed to extend session. Please try again.'));

                        // Restore the original message after delay
                        setTimeout(function () {
                            $message.text(originalMessage);
                        }, 3000);
                    }
                },
                error: function () {
                    // Show error message
                    $message.text($t('An error occurred. Please refresh the page.'));

                    // Restore the original message after delay
                    setTimeout(function () {
                        $message.text(originalMessage);
                    }, 3000);
                }
            });
        }

        // Initialise modal and schedule warning
        initSessionWarningModal();
        scheduleSessionWarning();
    };
});
