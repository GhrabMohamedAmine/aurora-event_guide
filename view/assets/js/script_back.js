// Handle flash messages from URL parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const type = urlParams.get('type');
    
    if (message && type) {
        showFlashMessage(type, decodeURIComponent(message));
    }

    // Flash message display
    function showFlashMessage(type, message) {
        const container = document.getElementById('flash-message-container');
        const flash = document.createElement('div');
        flash.className = `flash-message ${type}`;
        flash.innerHTML = `
            <span>${message}</span>
            <button class="close-flash">&times;</button>
        `;
        container.prepend(flash);
        
        setTimeout(() => flash.remove(), 5000);
        flash.querySelector('.close-flash')?.addEventListener('click', () => flash.remove());
    }

    // Real-time validation functions
    function validateCIN(cin) {
        return /^\d{8}$/.test(cin);
    }

    function validateName(name) {
        return /^[a-zA-ZÀ-ÿ\s]{2,30}$/.test(name);
    }

    function validatePhone(phone) {
        return /^[2459]\d{7}$/.test(phone);
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function validatePassword(password) {
        return password.length >= 8;
    }

    function validateDate(date) {
        const birthDate = new Date(date);
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (birthDate > today) return false;
        if (age < 16 || (age === 16 && monthDiff < 0)) return false;
        if (age > 100) return false;
        
        return true;
    }

    // Form validation
    function validateForm(form) {
        let isValid = true;
        clearErrors(form);

        // CIN validation
        const cin = form.querySelector('[name="cin"]');
        if (!cin.value.trim()) {
            showError(cin, 'Le CIN est obligatoire');
            isValid = false;
        } else if (!validateCIN(cin.value)) {
            showError(cin, 'Le CIN doit contenir exactement 8 chiffres');
            isValid = false;
        }

        // Nom validation
        const nom = form.querySelector('[name="nom"]');
        if (!nom.value.trim()) {
            showError(nom, 'Le nom est obligatoire');
            isValid = false;
        } else if (!validateName(nom.value.trim())) {
            showError(nom, 'Le nom doit contenir uniquement des lettres (2 à 30 caractères)');
            isValid = false;
        }

        // Prénom validation
        const prenom = form.querySelector('[name="prenom"]');
        if (!prenom.value.trim()) {
            showError(prenom, 'Le prénom est obligatoire');
            isValid = false;
        } else if (!validateName(prenom.value.trim())) {
            showError(prenom, 'Le prénom doit contenir uniquement des lettres (2 à 30 caractères)');
            isValid = false;
        }

        // Genre validation
        const genre = form.querySelector('[name="genre"]');
        if (!genre.value) {
            showError(genre, 'Le genre est obligatoire');
            isValid = false;
        }

        // Date de naissance validation
        const dateNaissance = form.querySelector('[name="date_naissance"]');
        if (!dateNaissance.value) {
            showError(dateNaissance, 'La date de naissance est obligatoire');
            isValid = false;
        } else if (!validateDate(dateNaissance.value)) {
            showError(dateNaissance, 'Date de naissance invalide (âge minimum 16 ans, maximum 100 ans)');
            isValid = false;
        }

        // Type validation
        const type = form.querySelector('[name="type"]');
        if (!type.value) {
            showError(type, 'Le type est obligatoire');
            isValid = false;
        }

        // Téléphone validation
        const telephone = form.querySelector('[name="telephone"]');
        if (!telephone.value.trim()) {
            showError(telephone, 'Le numéro de téléphone est obligatoire');
            isValid = false;
        } else if (!validatePhone(telephone.value)) {
            showError(telephone, 'Le numéro doit contenir 8 chiffres et commencer par 2, 4, 5 ou 9');
            isValid = false;
        }

        // Email validation
        const email = form.querySelector('[name="email"]');
        if (!email.value.trim()) {
            showError(email, 'L\'email est obligatoire');
            isValid = false;
        } else if (!validateEmail(email.value)) {
            showError(email, 'Format d\'email invalide');
            isValid = false;
        }

        // Password validation
        const password = form.querySelector('[name="password"]');
        if (!password.value) {
            showError(password, 'Le mot de passe est obligatoire');
            isValid = false;
        } else if (!validatePassword(password.value)) {
            showError(password, 'Le mot de passe doit contenir au moins 8 caractères');
            isValid = false;
        }

        return isValid;
    }

    function showError(input, message) {
        if (!input) return;
        const formGroup = input.closest('.form-group');
        const errorSpan = formGroup?.querySelector('.error-message');
        if (errorSpan) {
            errorSpan.textContent = message;
        }
        input.classList.add('invalid');
    }

    function clearErrors(form) {
        form.querySelectorAll('.error-message').forEach(span => span.textContent = '');
        form.querySelectorAll('.invalid').forEach(input => input.classList.remove('invalid'));
    }

    // Add form validation to forms
    const addForm = document.getElementById('add-user-form');
    if (addForm) {
        // Real-time validation for each field
        addForm.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', function() {
                clearErrors(addForm);
                switch(this.name) {
                    case 'cin':
                        if (!validateCIN(this.value) && this.value) {
                            showError(this, 'Le CIN doit contenir exactement 8 chiffres');
                        }
                        break;
                    case 'nom':
                    case 'prenom':
                        if (!validateName(this.value) && this.value) {
                            showError(this, 'Doit contenir uniquement des lettres (2 à 30 caractères)');
                        }
                        break;
                    case 'telephone':
                        if (!validatePhone(this.value) && this.value) {
                            showError(this, 'Le numéro doit contenir 8 chiffres et commencer par 2, 4, 5 ou 9');
                        }
                        break;
                    case 'email':
                        if (!validateEmail(this.value) && this.value) {
                            showError(this, 'Format d\'email invalide');
                        }
                        break;
                    case 'date_naissance':
                        if (!validateDate(this.value) && this.value) {
                            showError(this, 'Date de naissance invalide (âge minimum 16 ans, maximum 100 ans)');
                        }
                        break;
                    case 'password':
                        if (!validatePassword(this.value) && this.value) {
                            showError(this, 'Le mot de passe doit contenir au moins 8 caractères');
                        }
                        break;
                }
            });
        });

        addForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    }

    // Add edit form validation
    const editForm = document.getElementById('edit-user-form');
    if (editForm) {
        // Real-time validation for edit form
        editForm.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', function() {
                clearErrors(editForm);
                switch(this.name) {
                    case 'cin':
                        if (!validateCIN(this.value) && this.value) {
                            showError(this, 'Le CIN doit contenir exactement 8 chiffres');
                        }
                        break;
                    case 'nom':
                    case 'prenom':
                        if (!validateName(this.value) && this.value) {
                            showError(this, 'Doit contenir uniquement des lettres (2 à 30 caractères)');
                        }
                        break;
                    case 'telephone':
                        if (!validatePhone(this.value) && this.value) {
                            showError(this, 'Le numéro doit contenir 8 chiffres et commencer par 2, 4, 5 ou 9');
                        }
                        break;
                    case 'email':
                        if (!validateEmail(this.value) && this.value) {
                            showError(this, 'Format d\'email invalide');
                        }
                        break;
                    case 'date_naissance':
                        if (!validateDate(this.value) && this.value) {
                            showError(this, 'Date de naissance invalide (âge minimum 16 ans, maximum 100 ans)');
                        }
                        break;
                    case 'mot_de_pass':
                        if (this.value && !validatePassword(this.value)) {
                            showError(this, 'Le mot de passe doit contenir au moins 8 caractères');
                        }
                        break;
                }
            });
        });

        editForm.addEventListener('submit', function(e) {
            let isValid = true;
            clearErrors(this);

            // Validate required fields except password
            const requiredFields = ['cin', 'nom', 'prenom', 'genre', 'date_naissance', 'type', 'telephone', 'email'];
            requiredFields.forEach(field => {
                const input = this.querySelector(`[name="${field}"]`);
                if (!input || !input.value.trim()) {
                    showError(input, 'Ce champ est obligatoire');
                    isValid = false;
                }
            });

            // Validate each field's format if it has a value
            const cin = this.querySelector('[name="cin"]');
            if (cin?.value && !validateCIN(cin.value)) {
                showError(cin, 'Le CIN doit contenir exactement 8 chiffres');
                isValid = false;
            }

            const nom = this.querySelector('[name="nom"]');
            if (nom?.value && !validateName(nom.value)) {
                showError(nom, 'Le nom doit contenir uniquement des lettres (2 à 30 caractères)');
                isValid = false;
            }

            const prenom = this.querySelector('[name="prenom"]');
            if (prenom?.value && !validateName(prenom.value)) {
                showError(prenom, 'Le prénom doit contenir uniquement des lettres (2 à 30 caractères)');
                isValid = false;
            }

            const telephone = this.querySelector('[name="telephone"]');
            if (telephone?.value && !validatePhone(telephone.value)) {
                showError(telephone, 'Le numéro doit contenir 8 chiffres et commencer par 2, 4, 5 ou 9');
                isValid = false;
            }

            const email = this.querySelector('[name="email"]');
            if (email?.value && !validateEmail(email.value)) {
                showError(email, 'Format d\'email invalide');
                isValid = false;
            }

            const dateNaissance = this.querySelector('[name="date_naissance"]');
            if (dateNaissance?.value && !validateDate(dateNaissance.value)) {
                showError(dateNaissance, 'Date de naissance invalide (âge minimum 16 ans, maximum 100 ans)');
                isValid = false;
            }

            // Only validate password if one is provided
            // Only validate password if one is provided
            const password = this.querySelector('[name="mot_de_pass"]');
            if (password?.value && !validatePassword(password.value)) {
                showError(password, 'Le mot de passe doit contenir au moins 8 caractères');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchText = e.target.value.toLowerCase();
            const tbody = document.getElementById('users-table-body');
            
            if (!tbody) return;
            
            Array.from(tbody.getElementsByTagName('tr')).forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });
    }

    // Refresh button functionality
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function(e) {
            e.preventDefault();
            location.reload();
        });
    }
});