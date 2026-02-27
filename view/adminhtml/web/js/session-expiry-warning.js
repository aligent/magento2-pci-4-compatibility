define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    const STORAGE_KEY = 'aligent_session_expiry';

    function getSessionExpiry() {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored ? parseInt(stored, 10) : null;
    }

    function setSessionExpiry(timestamp) {
        localStorage.setItem(STORAGE_KEY, timestamp.toString());
    }

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
                opened: function () {
                    $('body').addClass('_session-warning-active');
                },
                closed: function () {
                    $('body').removeClass('_session-warning-active');
                },
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
         * Reset the modal UI back to the pre-expiry warning state
         */
        function resetModalState() {
            const $modalWrapper = $modal.closest('.modal-admintimeout'),
                modalInstance = $modal.data('mage-modal');

            $message.text(originalMessage);
            $modalWrapper.find('.modal-title').text($t('Session Expiring Soon'));
            $modalWrapper.find('.action-primary span').text($t('Extend Session'));
            $modalWrapper.removeClass('session-expired');

            if (modalInstance && modalInstance.options.keyEventHandlers && originalEscapeHandler) {
                modalInstance.options.keyEventHandlers.escapeKey = originalEscapeHandler;
                originalEscapeHandler = null;
            }

            sessionExpired = false;
        }

        /**
         * Perform a single countdown tick, deriving remaining time from the stored expiry timestamp
         */
        function tickCountdown() {
            const expiry = getSessionExpiry(),
                remaining = expiry ? Math.max(0, Math.round((expiry - Date.now()) / 1000)) : 0;

            if (remaining <= 0) {
                if (countdownHandle) {
                    clearInterval(countdownHandle);
                    countdownHandle = null;
                }

                onSessionExpired();
            } else {
                updateCountdownMessage(remaining);
            }
        }

        /**
         * Start the countdown timer displayed in the modal
         */
        function startCountdown() {
            if (countdownHandle) {
                clearInterval(countdownHandle);
                countdownHandle = null;
            }

            tickCountdown();
            countdownHandle = setInterval(tickCountdown, 1000);
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

            const $modalWrapper = $modal.closest('.modal-admintimeout'),
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
         * Schedule the session warning modal to appear, deriving timing from the stored expiry timestamp
         */
        function scheduleSessionWarning() {
            // Clear any existing timeout
            if (sessionTimeoutHandle) {
                clearTimeout(sessionTimeoutHandle);
                sessionTimeoutHandle = null;
            }

            // Clear any existing countdown
            if (countdownHandle) {
                clearInterval(countdownHandle);
                countdownHandle = null;
            }

            const expiry = getSessionExpiry();

            if (!expiry) {
                return;
            }

            const remaining = expiry - Date.now(),
                warningTime = warningOffset * 1000;

            if (remaining <= 0) {
                $modal.modal('openModal');
                onSessionExpired();
            } else if (remaining <= warningTime) {
                $modal.modal('openModal');
                startCountdown();
            } else {
                sessionTimeoutHandle = setTimeout(function () {
                    $modal.modal('openModal');
                    startCountdown();
                }, remaining - warningTime);
            }
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

                        setSessionExpiry(Date.now() + sessionLifetime * 1000);

                        // Show a success message
                        $message.text($t('Session extended successfully!'));

                        // Close modal after a short delay
                        setTimeout(function () {
                            resetModalState();
                            modalContext.closeModal();
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

        // Cross-tab synchronization via storage event
        $(window).on('storage', function (e) {
            if (e.originalEvent.key !== STORAGE_KEY) {
                return;
            }

            const newExpiry = parseInt(e.originalEvent.newValue, 10);

            if (isNaN(newExpiry)) {
                return;
            }

            if (sessionTimeoutHandle) {
                clearTimeout(sessionTimeoutHandle);
                sessionTimeoutHandle = null;
            }

            if (countdownHandle) {
                clearInterval(countdownHandle);
                countdownHandle = null;
            }

            const modalInstance = $modal.data('mage-modal'),
                isOpen = modalInstance && modalInstance.options.isOpen;

            if ((newExpiry - Date.now()) > warningOffset * 1000) {
                if (isOpen) {
                    resetModalState();
                    modalInstance.closeModal();
                }

                scheduleSessionWarning();
            } else if (newExpiry > Date.now()) {
                if (!isOpen) {
                    $modal.modal('openModal');
                }

                startCountdown();
            } else {
                if (!isOpen) {
                    $modal.modal('openModal');
                }

                onSessionExpired();
            }
        });

        // Background tab safety net — re-sync when tab becomes visible
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden && !sessionExpired) {
                scheduleSessionWarning();
            }
        });

        // Detect admin AJAX activity that extends the PHP session
        $(document).ajaxComplete(function (event, xhr) {
            if (sessionExpired || !xhr || xhr.status < 200 || xhr.status >= 300) {
                return;
            }

            setSessionExpiry(Date.now() + sessionLifetime * 1000);
            scheduleSessionWarning();
        });

        // Initialize: set expiry timestamp (page load refreshes the server session) and start
        setSessionExpiry(Date.now() + sessionLifetime * 1000);
        initSessionWarningModal();
        scheduleSessionWarning();
    };
});
