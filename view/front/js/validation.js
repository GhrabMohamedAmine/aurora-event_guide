// Règles de validation
const VALIDATION_RULES = {
    cin: {
        pattern: /^[0-9]{8}$/,
        message: 'Le CIN doit contenir exactement 8 chiffres'
    },
    email: {
        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        message: 'Veuillez entrer une adresse email valide'
    },
    telephone: {
        pattern: /^[0-9]{8}$/,
        message: 'Le numéro de téléphone doit contenir exactement 8 chiffres'
    },
    entreprise: {
        minLength: 2,
        message: 'Le nom de l\'entreprise doit contenir au moins 2 caractères'
    }
};

// Messages d'erreur stylisés
function showError(inputElement, message) {
    // Supprimer l'ancien message d'erreur s'il existe
    removeError(inputElement);

    // Créer le nouvel élément d'erreur
    const errorDiv = document.createElement('div');
    errorDiv.className = 'validation-error';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    // Insérer après l'input
    inputElement.parentNode.insertBefore(errorDiv, inputElement.nextSibling);
    
    // Ajouter la classe d'erreur à l'input
    inputElement.classList.add('input-error');
}

function removeError(inputElement) {
    // Supprimer le message d'erreur
    const errorDiv = inputElement.parentNode.querySelector('.validation-error');
    if (errorDiv) {
        errorDiv.remove();
    }
    
    // Supprimer la classe d'erreur
    inputElement.classList.remove('input-error');
}

// Validation individuelle des champs
function validateField(inputElement, rule) {
    const value = inputElement.value.trim();
    
    // Vérification si le champ est vide
    if (!value) {
        showError(inputElement, 'Ce champ est obligatoire');
        return false;
    }

    // Vérification de la longueur minimale
    if (rule.minLength && value.length < rule.minLength) {
        showError(inputElement, rule.message);
        return false;
    }

    // Vérification du pattern
    if (rule.pattern && !rule.pattern.test(value)) {
        showError(inputElement, rule.message);
        return false;
    }

    // Si tout est ok, supprimer les erreurs
    removeError(inputElement);
    return true;
}

// Validation du formulaire complet
function validateSponsorForm(formElement) {
    let isValid = true;

    // Valider chaque champ
    Object.keys(VALIDATION_RULES).forEach(fieldName => {
        const inputElement = formElement.querySelector(`[name="${fieldName}"]`);
        if (inputElement) {
            if (!validateField(inputElement, VALIDATION_RULES[fieldName])) {
                isValid = false;
            }
        }
    });

    return isValid;
}

// Gestionnaire d'événements pour la validation en temps réel
function setupFormValidation(formElement) {
    if (!formElement) return;

    // Validation à la soumission
    formElement.addEventListener('submit', function(e) {
        if (!validateSponsorForm(this)) {
            e.preventDefault();
        }
    });

    // Validation en temps réel
    Object.keys(VALIDATION_RULES).forEach(fieldName => {
        const inputElement = formElement.querySelector(`[name="${fieldName}"]`);
        if (inputElement) {
            inputElement.addEventListener('input', function() {
                validateField(this, VALIDATION_RULES[fieldName]);
            });

            inputElement.addEventListener('blur', function() {
                validateField(this, VALIDATION_RULES[fieldName]);
            });
        }
    });
}

// Styles CSS pour les erreurs
const style = document.createElement('style');
style.textContent = `
    .validation-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .input-error {
        border-color: #dc3545 !important;
    }

    .input-error:focus {
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
`;
document.head.appendChild(style);

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    // Pour le formulaire d'ajout
    setupFormValidation(document.querySelector('form[action*="add_sponsor_front.php"]'));
    
    // Pour le formulaire de modification
    setupFormValidation(document.querySelector('form[action*="edit_sponsor_front.php"]'));
});
