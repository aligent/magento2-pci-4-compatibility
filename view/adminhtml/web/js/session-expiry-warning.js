define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    return function (config, element) {
        let sessionTimeoutHandle = null,
            countdownHandle = null,
            sessionExpired = false,
            sessionLifetime = config.sessionLifetime || 900,
            warningOffset = 60, // Show warning 60 seconds before expiry
            extendSessionUrl = config.extendSessionUrl,
            formKey = config.formKey,
            loginUrl = config.loginUrl,
            $modal = $(element),
            $message = $modal.find('#session-warning-message'),
            originalMessage = $message.text(),
            originalEscapeHandler = null;

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
         * Start the countdown timer displayed in the modal
         */
        function startCountdown() {
            let remainingSeconds = warningOffset;

            updateCountdownMessage(remainingSeconds);

            countdownHandle = setInterval(function () {
                remainingSeconds--;

                if (remainingSeconds <= 0) {
                    clearInterval(countdownHandle);
                    countdownHandle = null;
                    onSessionExpired();
                } else {
                    updateCountdownMessage(remainingSeconds);
                }
            }, 1000);
        }

        /**
         * Update the modal message with the remaining seconds
         *
         * @param {number} seconds
         */
        function updateCountdownMessage(seconds) {
            $message.text(
                $t('Your session will expire in %1 seconds. Please save your changes or extend your session to continue working.')
                    .replace('%1', seconds)
            );
        }

        /**
         * Handle session expiry — update modal to expired state
         */
        function onSessionExpired() {
            sessionExpired = true;

            let $modalWrapper = $modal.closest('.modal-admintimeout'),
                modalInstance = $modal.data('mage-modal');

            $message.text($t('Your session has expired. Please log in again to continue.'));

            // Update modal title
            $modalWrapper.find('.modal-title').text($t('Session Expired'));

            // Update primary button text
            $modalWrapper.find('.action-primary span').text($t('Go to Login'));

            // Hide Close button and header X button via CSS class
            $modalWrapper.addClass('session-expired');

            // Prevent Escape key from closing the modal
            if (modalInstance && modalInstance.options.keyEventHandlers) {
                originalEscapeHandler = modalInstance.options.keyEventHandlers.escapeKey;
                modalInstance.options.keyEventHandlers.escapeKey = function () {};
            }

            // Prevent overlay click from closing the modal
            if (modalInstance && modalInstance.overlay) {
                modalInstance.overlay.off('click');
            }
        }

        /**
         * Schedule the session warning modal to appear
         */
        function scheduleSessionWarning() {
            // Clear any existing timeout
            if (sessionTimeoutHandle) {
                clearTimeout(sessionTimeoutHandle);
            }

            // Clear any existing countdown
            if (countdownHandle) {
                clearInterval(countdownHandle);
                countdownHandle = null;
            }

            // Calculate delay: (sessionLifetime - warningOffset) * 1000 milliseconds
            const delay = (sessionLifetime - warningOffset) * 1000;

            sessionTimeoutHandle = setTimeout(function () {
                $modal.modal('openModal');
                startCountdown();
            }, delay);
        }

        /**
         * Extend the admin session via AJAX
         *
         * @param {Object} modalContext - The modal instance context
         */
        function extendSession(modalContext) {
            if (sessionExpired) {
                window.location.href = loginUrl;
                return;
            }

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
                        // Clear countdown
                        if (countdownHandle) {
                            clearInterval(countdownHandle);
                            countdownHandle = null;
                        }

                        sessionExpired = false;

                        // Show a success message
                        $message.text($t('Session extended successfully!'));

                        // Close modal after a short delay
                        setTimeout(function () {
                            let $modalWrapper = $modal.closest('.modal-admintimeout'),
                                modalInstance = $modal.data('mage-modal');

                            modalContext.closeModal();
                            $message.text(originalMessage);

                            // Reset modal title and button text
                            $modalWrapper.find('.modal-title').text($t('Session Expiring Soon'));
                            $modalWrapper.find('.action-primary span').text($t('Extend Session'));

                            // Restore Close button and header X button
                            $modalWrapper.removeClass('session-expired');

                            // Restore Escape key handler
                            if (modalInstance && modalInstance.options.keyEventHandlers && originalEscapeHandler) {
                                modalInstance.options.keyEventHandlers.escapeKey = originalEscapeHandler;
                                originalEscapeHandler = null;
                            }

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
