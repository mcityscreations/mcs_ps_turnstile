(function () {
    var config = window.mcsTurnstileConfig;
    if (!config || !config.siteKey) {
        return;
    }

    var forms = config.forms || {};

    function findSubmit(container) {
        var submit = container.querySelector('button[type="submit"], input[type="submit"]');
        while (submit && submit.parentNode !== container) {
            submit = submit.parentNode;
        }

        return submit;
    }

    function addWidget(container, formName, before) {
        if (!container || container.querySelector('.cf-turnstile')) {
            return;
        }

        var widget = document.createElement('div');
        widget.className = 'cf-turnstile';
        widget.setAttribute('data-sitekey', config.siteKey);
        widget.setAttribute('data-theme', 'light');
        widget.setAttribute('data-mcs-form', formName);

        container.insertBefore(widget, before || findSubmit(container));
    }

    function addToForms(selector, formName, predicate) {
        var candidates = document.querySelectorAll(selector);
        for (var i = 0; i < candidates.length; i++) {
            if (!predicate || predicate(candidates[i])) {
                addWidget(candidates[i], formName);
            }
        }
    }

    if (forms.login) {
        addToForms('#login-form', 'login');
    }

    if (forms.registration && !document.getElementById('mcs-turnstile-registration-fallback')) {
        addToForms('#customer-form, .js-customer-form', 'registration');
    }

    if (forms.reset_password) {
        addToForms('form', 'reset_password', function (form) {
            return form.classList.contains('forgotten-password')
                || !!form.querySelector('input[name="passwd"], input[name="confirmation"]');
        });
    }

    if (forms.contact) {
        addToForms('form', 'contact', function (form) {
            return !!form.querySelector('[name="submitMessage"], textarea[name="message"]');
        });
    }

    if (forms.newsletter) {
        addToForms('.block_newsletter form, .email_subscription form', 'newsletter');
    }

    if (forms.back_in_stock) {
        var alerts = document.querySelectorAll('.js-mailalert');
        for (var j = 0; j < alerts.length; j++) {
            addWidget(alerts[j], 'back_in_stock', alerts[j].querySelector('.js-mailalert-add'));
        }

        if (window.jQuery && window.jQuery.ajaxPrefilter) {
            window.jQuery.ajaxPrefilter(function (options) {
                if (!options.url || options.url.indexOf('ps_emailalerts') === -1 || options.url.indexOf('process=add') === -1) {
                    return;
                }

                var alert = document.querySelector('.js-mailalert');
                var tokenField = alert && alert.querySelector('input[name="cf-turnstile-response"]');
                if (!tokenField || !tokenField.value) {
                    return;
                }

                if (typeof options.data === 'string') {
                    options.data += (options.data ? '&' : '') + 'cf-turnstile-response=' + encodeURIComponent(tokenField.value);
                } else if (options.data && typeof options.data === 'object') {
                    options.data['cf-turnstile-response'] = tokenField.value;
                }
            });
        }
    }
})();
