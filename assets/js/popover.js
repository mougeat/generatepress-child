document.addEventListener('DOMContentLoaded', function() {
    const popover = document.getElementById('ispag-field-popover');
    const container = document.getElementById('popover-input-container');
    let currentTarget = null;
    let itiPopover = null;

    console.log("🚀 ISPAG : Script popover.js chargé");

    // --- NOUVEAU : CHARGEMENT FORCÉ DES UTILS ---
    function ensureIntlUtils() {
        return new Promise((resolve) => {
            if (typeof intlTelInputUtils !== 'undefined') {
                resolve(true);
            } else {
                console.log("⏳ ISPAG : Chargement manuel de utils.js...");
                const script = document.createElement('script');
                script.src = ispag_params.utils_url;
                script.onload = () => {
                    console.log("✅ ISPAG : utils.js chargé avec succès.");
                    resolve(true);
                };
                script.onerror = () => {
                    console.error("❌ ISPAG : Échec du chargement de utils.js");
                    resolve(false);
                };
                document.head.appendChild(script);
            }
        });
    }

    // 1. AUTO-FORMATAGE VIA INTERSECTION OBSERVER
    const phoneObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(async entry => {
            if (entry.isIntersecting) {
                const field = entry.target;
                
                // On attend que les utils soient là
                const isReady = await ensureIntlUtils();

                if (isReady && typeof intlTelInputUtils !== 'undefined') {
                    const rawValue = field.dataset.value;
                    const displaySpan = field.querySelector('.current-value');
                    
                    if (rawValue && displaySpan) {
                        try {
                            const formatted = intlTelInputUtils.formatNumber(rawValue, "CH", intlTelInputUtils.numberFormat.INTERNATIONAL);
                            displaySpan.innerText = formatted;
                            console.log("✨ Formatage appliqué :", formatted);
                            observer.unobserve(field);
                        } catch (e) {
                            console.error("❌ Erreur formatage:", e);
                        }
                    }
                }
            }
        });
    }, { threshold: 0.1 });

    function initPhoneObservation() {
        const fields = document.querySelectorAll('.ispag-popover-field[data-field-type="phone"]');
        if (fields.length > 0) {
            console.log(`📡 ISPAG : Observation lancée sur ${fields.length} champ(s)`);
            fields.forEach(field => phoneObserver.observe(field));
        }
    }

    initPhoneObservation();

    // 2. GESTION DU CLIC (Conservation de ta logique existante)
    document.querySelectorAll('.ispag-popover-field').forEach(field => {
        field.addEventListener('click', function(e) {
            console.log("🖱️ Clic sur champ:", this.dataset.name);
            currentTarget = this;
            const type = this.dataset.fieldType;

            if (type === 'avatar') {
                e.preventDefault();
                openWordPressMediaLibrary(this);
                return; // On ne montre pas le popover classique pour l'avatar
            }

            console.log("🖱️ Clic sur champ:", this.dataset.name);
            const value = this.dataset.value || '';
            const optionsString = this.dataset.options || '';

            const rect = this.getBoundingClientRect();
            popover.style.top = `${rect.top + window.scrollY - 10}px`;
            popover.style.left = `${rect.right + 15}px`;
            popover.style.display = 'block';

            if(itiPopover) { itiPopover.destroy(); itiPopover = null; }
            container.innerHTML = '';

            if (type === 'phone') {
                container.innerHTML = `<input type="tel" id="popover-phone-input" value="${value}" style="width:100%">`;
                const input = document.getElementById('popover-phone-input');
                itiPopover = window.intlTelInput(input, {
                    initialCountry: "ch",
                    preferredCountries: ["ch", "fr"],
                    separateDialCode: true,
                    utilsScript: ispag_params.utils_url
                });
                setTimeout(() => input.focus(), 100);
            } else if (type === 'date') {
                container.innerHTML = `<input type="date" id="popover-date-input" value="${value}" style="width:100%; padding:5px;">`;
                const input = document.getElementById('popover-date-input');
                setTimeout(() => input.focus(), 100);
            } else if (type === 'select') {
                let selectHtml = `<select id="popover-select-input" style="width:100%; padding:5px;">`;
                const options = optionsString.split(';').filter(opt => opt.includes(':'));
                options.forEach(opt => {
                    const [val, label] = opt.split(':');
                    const selected = (val === value) ? 'selected' : '';
                    selectHtml += `<option value="${val}" ${selected}>${label}</option>`;
                });
                selectHtml += `</select>`;
                container.innerHTML = selectHtml;
            }else if (type === 'email') {
                container.innerHTML = `<input type="email" id="popover-email-input" value="${value}" style="width:100%; padding:5px;">`;
                const input = document.getElementById('popover-email-input');
                setTimeout(() => input.focus(), 100);
            } else {
                container.innerHTML = `<input type="text" id="popover-text-input" value="${value}" style="width:100%; padding:5px;">`;
                const textInput = document.getElementById('popover-text-input');
                if(textInput) setTimeout(() => textInput.focus(), 100);
            }
        });
    });

    // --- 3. ACTIONS BOUTONS (Correction Sécurisée) ---
    if (popover) {
        const cancelBtn = popover.querySelector('.popover-cancel');
        const saveBtn = popover.querySelector('.popover-save');
        if (cancelBtn) {
            cancelBtn.onclick = () => {
                popover.style.display = 'none';
                if (itiPopover) { 
                    itiPopover.destroy(); 
                    itiPopover = null; 
                }
            };
        } else {
            console.warn("⚠️ ISPAG : Bouton .popover-cancel non trouvé dans le popover.");
        }
        
    } else {
        // console.error("❌ ISPAG : L'élément #ispag-field-popover est introuvable dans le DOM.");
    }

    popover.querySelector('.popover-save').onclick = () => {
        if (!currentTarget) return;
        const type = currentTarget.dataset.fieldType;
        const fieldName = currentTarget.dataset.name;
        // Nettoyage des IDs : on s'assure d'avoir soit un ID propre, soit null
        const contactId = (currentTarget.dataset.contactId && currentTarget.dataset.contactId !== "undefined") ? currentTarget.dataset.contactId : null;
        const companyId = (currentTarget.dataset.companyId && currentTarget.dataset.companyId !== "undefined") ? currentTarget.dataset.companyId : null;

        console.log('ID Check:', { contactId, companyId, fieldName });
        
        if (!contactId && !companyId) {
            console.log('ERRREUR AUCUN ID DE DEFINI');
            popover.style.display = 'none';
            return;
        }

        let valToSave = '';
        if (type === 'phone') {
            if (!itiPopover.isValidNumber()) {
                alert("Numéro de téléphone invalide");
                return;
            }
            valToSave = itiPopover.getNumber(); 
        } else if (type === 'select') {
            valToSave = document.getElementById('popover-select-input').value;
        } else {
            const input = container.querySelector('input');
            valToSave = input ? input.value : '';
        }

        saveField(fieldName, valToSave, contactId, companyId);
    };

    function saveField(fieldName, value, contactId, companyId) {
        console.group(`💾 ISPAG SAVE: ${fieldName}`);
        console.log('received datas', fieldName, value, contactId, companyId);
        const saveBtn = popover.querySelector('.popover-save');
        const originalBtnText = saveBtn.innerText;
        saveBtn.innerText = '...'; 
        saveBtn.disabled = true;

        const formData = new FormData();
        if (companyId) {
            console.log('IN save_company_field');
            formData.append('action', 'save_company_field');
            formData.append('company_id', companyId);
        } 
        // Sinon, si on a un ID de contact
        else if (contactId) {
            console.log('IN save_contact_field');
            formData.append('action', 'save_contact_field');
            formData.append('contact_id', contactId);
        } 
        else {
            console.error('ERREUR : Aucun ID (Contact ou Company) trouvé');
            saveBtn.innerText = originalBtnText;
            saveBtn.disabled = false;
            return;
        }
        
        formData.append('nonce', ispag_params.nonce);
        formData.append('field_name', fieldName);
        formData.append('new_value', value); 

        fetch(ispag_params.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const displayElement = currentTarget.querySelector('.current-value') || currentTarget;
                let finalValueToDisplay = res.data.display_value || value;

                if (currentTarget.dataset.fieldType === 'phone' && typeof intlTelInputUtils !== 'undefined') {
                    finalValueToDisplay = intlTelInputUtils.formatNumber(value, "CH", intlTelInputUtils.numberFormat.INTERNATIONAL);
                }

                displayElement.innerHTML = finalValueToDisplay;
                currentTarget.dataset.value = value;
                popover.style.display = 'none';
                if(itiPopover) { itiPopover.destroy(); itiPopover = null; }
                saveBtn.innerText = '✔';
            } else {
                alert('Erreur : ' + (res.data?.message || 'Impossible de sauvegarder'));
                saveBtn.innerText = originalBtnText;
            }
        })
        .catch(err => { console.error('Erreur AJAX:', err); saveBtn.innerText = originalBtnText; })
        .finally(() => {
            saveBtn.disabled = false;
            setTimeout(() => { saveBtn.innerText = originalBtnText; }, 2000);
            console.groupEnd();
        });
    }

    // --- NOUVELLE FONCTION : MEDIA LIBRARY ---
    function openWordPressMediaLibrary(element) {
        if (typeof wp === 'undefined' || !wp.media) {
            console.error("La Media Library WordPress n'est pas chargée (wp_enqueue_media missing)");
            return;
        }

        const frame = wp.media({
            title: 'Sélectionner une image',
            button: { text: 'Utiliser cette image' },
            multiple: false
        });

        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            
            // Récupération des deux IDs possibles
            const contactId = element.dataset.contactId;
            const companyId = element.dataset.companyId;

            // On passe les deux à la fonction de sauvegarde
            saveAvatar(contactId, companyId, attachment.id, attachment.url, element);
        });

        frame.open();
    }

    function saveAvatar(contactId, companyId, attachmentId, imageUrl, element) {
        const displayContainer = element.querySelector('.current-value') || element;
        const originalContent = displayContainer.innerHTML;
        
        displayContainer.style.opacity = '0.5';

        const formData = new FormData();
        formData.append('nonce', ispag_params.nonce);
        formData.append('attachment_id', attachmentId);

        // LOGIQUE D'AIGUILLAGE
        if (companyId && companyId !== "undefined" && companyId !== "") {
            console.log("🏢 Sauvegarde Favicon Entreprise ID:", companyId);
            formData.append('action', 'save_company_favicon');
            formData.append('company_id', companyId);
        } else {
            console.log("👤 Sauvegarde Avatar Contact ID:", contactId);
            formData.append('action', 'save_contact_avatar');
            formData.append('contact_id', contactId);
        }

        fetch(ispag_params.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                displayContainer.innerHTML = `<img src="${imageUrl}" class="ispag-avatar-img" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">`;
                element.classList.add('has-avatar');
            } else {
                alert('Erreur : ' + (res.data?.message || 'Impossible de sauvegarder'));
                displayContainer.innerHTML = originalContent;
            }
        })
        .catch(err => {
            console.error("Erreur AJAX Image:", err);
            displayContainer.innerHTML = originalContent;
        })
        .finally(() => {
            displayContainer.style.opacity = '1';
        });
    }
});