document.addEventListener('DOMContentLoaded', function() {

    // 1. Éléments du DOM
    const sidebar       = document.getElementById('ispag-contact-sidebar-modal');
    const form          = document.getElementById('ispag-create-contact-form');
    const emailInput    = document.getElementById('c_email');
    const firstName     = document.getElementById('c_first_name');
    const lastName      = document.getElementById('c_last_name');
    const phoneInput    = document.getElementById('c_phone'); 
    const extraFields   = document.getElementById('contact-extra-fields');
    const submitBtn     = document.getElementById('btn-submit-contact');
    const triggerBtn    = document.getElementById('trigger-add-contact');
    const closeBtns     = document.querySelectorAll('.ispag-modal-close');

    // Sécurité : On ne continue l'initialisation que si on est sur une page avec le formulaire/sidebar
    if (!sidebar || !form) return;

    // 2. Initialisation du téléphone (intl-tel-input)
    let iti;
    if (phoneInput && typeof window.intlTelInput !== 'undefined') {
        iti = window.intlTelInput(phoneInput, {
            geoIpLookup: function(callback) {
                fetch("https://ipapi.co/json")
                    .then(res => res.json())
                    .then(data => callback(data.country_code))
                    .catch(() => callback("ch"));
            },
            initialCountry: "ch",
            preferredCountries: ["ch", "fr", "be"],
            allowDropdown: true,
            dropdownContainer: document.body, 
            utilsScript: ispag_params.utils_url,
            separateDialCode: true 
        });
        phoneInput.style.width = "100%";
    }

    // 3. Ouverture / Fermeture
    if (triggerBtn) {
        triggerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.add('active');
            document.body.classList.add('sidebar-open');
        });
    }

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sidebar.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        });
    });

    // 4. Logique de progression (HubSpot-style)
    function checkFormProgression() {
        if (!emailInput || !firstName || !lastName || !extraFields || !submitBtn) return;

        const emailValue = emailInput.value.trim();
        const hasEmail = emailValue.includes('@') && emailValue.includes('.');
        const hasName  = firstName.value.trim().length > 1 || lastName.value.trim().length > 1;

        if (hasEmail && hasName) {
            extraFields.style.opacity = "1";
            extraFields.style.pointerEvents = "auto";
            submitBtn.disabled = false;
        } else {
            extraFields.style.opacity = "0.4";
            extraFields.style.pointerEvents = "none";
            submitBtn.disabled = true;
        }
    }

    [emailInput, firstName, lastName].forEach(el => {
        if (el) el.addEventListener('input', checkFormProgression);
    });

    // 5. Envoi du formulaire en AJAX
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        submitBtn.disabled = true;
        const originalText = submitBtn.textContent;
        submitBtn.textContent = "Creating...";

        const formData = new FormData(form);
        
        if (iti) {
            formData.set('phone', iti.getNumber()); 
        }

        formData.append('action', 'ispag_create_contact');
        formData.append('nonce', ispag_params.nonce);

        fetch(ispag_params.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                window.location.href = res.data.redirect_url;
            } else {
                alert(res.data.message || 'Error occurred');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });

    // 6. Vérification disponibilité Email
    let emailTimer;
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            clearTimeout(emailTimer);
            const email = emailInput.value;

            emailTimer = setTimeout(() => {
                if (email.includes('@') && email.includes('.')) {
                    checkEmailAvailability(email);
                }
            }, 500);
        });
    }

    function checkEmailAvailability(email) {
        const formData = new FormData();
        formData.append('action', 'ispag_check_email_exists');
        formData.append('email', email);
        formData.append('nonce', ispag_params.nonce);

        fetch(ispag_params.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            const errorMsg = document.getElementById('email-exists-error');
            
            if (res.success && res.data.exists) {
                emailInput.style.borderColor = "#de350b"; 
                if (errorMsg) {
                    errorMsg.innerHTML = `Ce contact existe déjà : <a href="${res.data.view_url}">${res.data.name}</a>`;
                    errorMsg.style.display = "block";
                }
                submitBtn.disabled = true;
            } else {
                emailInput.style.borderColor = ""; 
                if (errorMsg) errorMsg.style.display = "none";
                checkFormProgression(); 
            }
        })
        .catch(err => console.error("Erreur check email:", err));
    }
});