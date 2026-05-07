/*
 * Frontend behavior bundle.
 * - Confirmation prompts for destructive/important forms.
 * - Auto-uppercase for tagged inputs.
 * - Lightweight input helpers for form usability.
 */
(function () {
    var forms = document.querySelectorAll('form[data-confirm]');
    for (var i = 0; i < forms.length; i++) {
        forms[i].addEventListener('submit', function (event) {
            var message = this.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    }

    var upperInputs = document.querySelectorAll('[data-upper]');
    for (var j = 0; j < upperInputs.length; j++) {
        upperInputs[j].addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
    }

    var actionGroups = document.querySelectorAll('[data-action-group]');
    for (var k = 0; k < actionGroups.length; k++) {
        (function (group) {
            var groupName = group.getAttribute('data-action-group');
            if (!groupName) {
                return;
            }

            var formsContainer = document.querySelector('[data-action-forms="' + groupName + '"]');
            if (!formsContainer) {
                return;
            }

            var buttons = group.querySelectorAll('[data-action-target]');
            var forms = formsContainer.querySelectorAll('[data-action-form]');

            var setActive = function (target) {
                for (var i = 0; i < forms.length; i++) {
                    var form = forms[i];
                    var isActive = form.getAttribute('data-action-form') === target;
                    form.classList.toggle('is-active', isActive);
                    form.hidden = !isActive;
                    form.style.display = isActive ? 'block' : 'none';
                }

                for (var b = 0; b < buttons.length; b++) {
                    var button = buttons[b];
                    var isSelected = button.getAttribute('data-action-target') === target;
                    button.classList.toggle('is-active', isSelected);
                    button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                }
            };

            for (var m = 0; m < buttons.length; m++) {
                buttons[m].addEventListener('click', function () {
                    setActive(this.getAttribute('data-action-target'));
                });
            }

            var defaultTarget = group.getAttribute('data-default-action');
            if (defaultTarget) {
                var exists = formsContainer.querySelector('[data-action-form="' + defaultTarget + '"]');
                if (!exists) {
                    defaultTarget = null;
                }
            }
            if (!defaultTarget && forms.length > 0) {
                defaultTarget = forms[0].getAttribute('data-action-form');
            }
            if (defaultTarget) {
                setActive(defaultTarget);
            }
        })(actionGroups[k]);
    }

})();
