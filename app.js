/*
 * Frontend behavior bundle.
 * - Confirmation prompts for destructive/important forms.
 * - Auto-uppercase for tagged inputs.
 * - Client-side case-year vs crime-date-year consistency check.
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

    var crimeForm = document.querySelector('form[data-validate-crime]');
    if (crimeForm) {
        crimeForm.addEventListener('submit', function (event) {
            var caseInput = crimeForm.querySelector('input[name="case_number"]');
            var dateInput = crimeForm.querySelector('input[name="crime_datetime"]');
            if (!caseInput || !dateInput || !caseInput.value || !dateInput.value) {
                return;
            }

            var match = caseInput.value.toUpperCase().match(/^CASE-(\d{4})-\d{4}$/);
            if (!match) {
                return;
            }

            var caseYear = match[1];
            var dateYear = String(dateInput.value).slice(0, 4);
            if (caseYear !== dateYear) {
                event.preventDefault();
                window.alert('Case year must match the crime date year.');
            }
        });
    }
})();
