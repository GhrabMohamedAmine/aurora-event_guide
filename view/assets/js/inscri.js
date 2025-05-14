function validateForm(form) {
    let isValid = true;
    clearAllErrors();

    // CIN validation
    const cin = form.querySelector('[name="cin"]');
    if (!cin.value.trim()) {
        showError(cin, 'Le CIN est requis');
        isValid = false;
    } else if (!/^\d{8}$/.test(cin.value.trim())) {
        showError(cin, 'Le CIN doit contenir 8 chiffres');
        isValid = false;
    }

    // Nom validation
    const nom = form.querySelector('[name="nom"]');
    if (!nom.value.trim()) {
        showError(nom, 'Le nom est requis');
        isValid = false;
    } else if (!/^[a-zA-ZÀ-ÿ\s'-]{2,}$/.test(nom.value.trim())) {
        showError(nom, 'Le nom doit contenir au moins 2 caractères et uniquement des lettres');
        isValid = false;
    }

    // Prénom validation
    const prenom = form.querySelector('[name="prenom"]');
    if (!prenom.value.trim()) {
        showError(prenom, 'Le prénom est requis');
        isValid = false;
    } else if (!/^[a-zA-ZÀ-ÿ\s'-]{2,}$/.test(prenom.value.trim())) {
        showError(prenom, 'Le prénom doit contenir au moins 2 caractères et uniquement des lettres');
        isValid = false;
    }

    // Genre validation
    const genre = form.querySelector('[name="genre"]');
    if (!genre.value) {
        showError(genre, 'Le genre est requis');
        isValid = false;
    }

    // Téléphone validation
    const telephone = form.querySelector('[name="telephone"]');
    if (!telephone.value.trim()) {
        showError(telephone, 'Le numéro de téléphone est requis');
        isValid = false;
    } else if (!/^[0-9+\s]{8,}$/.test(telephone.value.trim())) {
        showError(telephone, 'Numéro de téléphone invalide (8 chiffres requis)');
        isValid = false;
    }

    // Date de naissance validation
    const dateNaissance = form.querySelector('[name="date_naissance"]');
    if (!dateNaissance.value) {
        showError(dateNaissance, 'La date de naissance est requise');
        isValid = false;
    } else {
        const birthDate = new Date(dateNaissance.value);
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        if (age < 13) {
            showError(dateNaissance, 'Vous devez avoir au moins 13 ans');
            isValid = false;
        }
    }

    // Email validation
    const email = form.querySelector('[name="email"]');
    if (!email.value.trim()) {
        showError(email, 'L\'email est requis');
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        showError(email, 'Adresse email invalide');
        isValid = false;
    }

    // Type validation
    const type = form.querySelector('[name="type"]');
    if (!type.value) {
        showError(type, 'Le type est requis');
        isValid = false;
    }

    // Password validation
    const password = form.querySelector('[name="mot_de_pass"]');
    if (!password.value) {
        showError(password, 'Le mot de passe est requis');
        isValid = false;
    } else if (password.value.length < 8) {
        showError(password, 'Le mot de passe doit contenir au moins 8 caractères');
        isValid = false;
    }

    return isValid;
}

function showError(input, message) {
    const errorDiv = input.parentElement.querySelector('.error-message');
    errorDiv.textContent = message;
    input.classList.add('is-invalid');
}

function clearAllErrors() {
    const form = document.getElementById('add-user-form');
    const errorMessages = form.querySelectorAll('.error-message');
    const invalidInputs = form.querySelectorAll('.is-invalid');
    
    errorMessages.forEach(error => error.textContent = '');
    invalidInputs.forEach(input => input.classList.remove('is-invalid'));
}

function validateField(input) {
    const name = input.name;
    let isValid = true;
    let message = '';

    switch(name) {
        case 'cin':
            if (!input.value.trim()) {
                message = 'Le CIN est requis';
                isValid = false;
            } else if (!/^\d{8}$/.test(input.value.trim())) {
                message = 'Le CIN doit contenir 8 chiffres';
                isValid = false;
            }
            break;

        case 'nom':
        case 'prenom':
            if (!input.value.trim()) {
                message = `Le ${name} est requis`;
                isValid = false;
            } else if (!/^[a-zA-ZÀ-ÿ\s'-]{2,}$/.test(input.value.trim())) {
                message = `Le ${name} doit contenir au moins 2 caractères et uniquement des lettres`;
                isValid = false;
            }
            break;

        case 'genre':
            if (!input.value) {
                message = 'Le genre est requis';
                isValid = false;
            }
            break;

        case 'telephone':
            if (!input.value.trim()) {
                message = 'Le numéro de téléphone est requis';
                isValid = false;
            } else if (!/^[0-9+\s]{8,}$/.test(input.value.trim())) {
                message = 'Numéro de téléphone invalide (8 chiffres requis)';
                isValid = false;
            }
            break;

        case 'date_naissance':
            if (!input.value) {
                message = 'La date de naissance est requise';
                isValid = false;
            } else {
                const birthDate = new Date(input.value);
                const today = new Date();
                const age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                if (age < 13) {
                    message = 'Vous devez avoir au moins 13 ans';
                    isValid = false;
                }
            }
            break;

        case 'email':
            if (!input.value.trim()) {
                message = 'L\'email est requis';
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())) {
                message = 'Adresse email invalide';
                isValid = false;
            }
            break;

        case 'type':
            if (!input.value) {
                message = 'Le type est requis';
                isValid = false;
            }
            break;

        case 'mot_de_pass':
            if (!input.value) {
                message = 'Le mot de passe est requis';
                isValid = false;
            } else if (input.value.length < 8) {
                message = 'Le mot de passe doit contenir au moins 8 caractères';
                isValid = false;
            }
            break;
    }

    if (!isValid) {
        showError(input, message);
    } else {
        clearError(input);
    }

    return isValid;
}

function clearError(input) {
    const errorDiv = input.parentElement.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.textContent = '';
    }
    input.classList.remove('is-invalid');
}

// Add real-time validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('add-user-form');
    if (!form) return;

    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            validateField(this);
        });
        input.addEventListener('change', function() {
            validateField(this);
        });
    });
});