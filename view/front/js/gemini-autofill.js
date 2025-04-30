// Gemini API Integration for Auto-Filling Sponsor Form
const GEMINI_API_KEY = 'AIzaSyANf8Olo39MLx9V66llYli2dVbvO5KuRbc';
const GEMINI_MODEL = 'gemini-2.0-flash';
const API_URL = `https://generativelanguage.googleapis.com/v1/models/${GEMINI_MODEL}:generateContent?key=${GEMINI_API_KEY}`;

// Company name generator prompt
const COMPANY_NAME_PROMPT = "Generate a professional business name for a company that might sponsor events. Return just the name without any explanation.";

// Person name generator prompt
const PERSON_NAME_PROMPT = "Generate a realistic full name of a business professional. Return just the name without any explanation.";

// Email generator prompt
const EMAIL_PROMPT = "Generate a professional business email address for a company executive. Return just the email without any explanation.";

// Phone generator prompt
const PHONE_PROMPT = "Generate a valid Tunisian phone number with 8 digits. The number should start with one of these valid prefixes: 2, 5, 9, 4, or 7. Return just the number without any explanation.";

// Function to make API requests to Gemini
async function generateWithGemini(prompt) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                contents: [{
                    parts: [{
                        text: prompt
                    }]
                }]
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            console.error('API Error:', errorData);
            throw new Error(`Erreur API: ${errorData.error?.message || 'Erreur inconnue'}`);
        }

        const data = await response.json();
        
        if (data.candidates && data.candidates[0]?.content?.parts?.[0]?.text) {
            return data.candidates[0].content.parts[0].text.trim();
        } else {
            console.error('Unexpected API response format:', data);
            throw new Error('Format de réponse API inattendu');
        }
    } catch (error) {
        console.error('Error calling Gemini API:', error);
        throw error;
    }
}

// Function to ensure a valid Tunisian phone number format
function validateTunisianPhoneNumber(phone) {
    // Remove any non-digit characters
    let cleanPhone = phone.replace(/\D/g, '');
    
    // Valid Tunisian mobile prefixes
    const validPrefixes = ['2', '5', '9', '4', '7'];
    
    // If the number has country code (+216), remove it
    if (cleanPhone.startsWith('216')) {
        cleanPhone = cleanPhone.substring(3);
    }
    
    // If the number is not 8 digits or doesn't start with valid prefix
    if (cleanPhone.length !== 8 || !validPrefixes.includes(cleanPhone[0])) {
        // Generate a valid number using a random prefix
        const randomPrefix = validPrefixes[Math.floor(Math.random() * validPrefixes.length)];
        // Generate random 7 digits
        const randomDigits = Array(7).fill().map(() => Math.floor(Math.random() * 10)).join('');
        return randomPrefix + randomDigits;
    }
    
    return cleanPhone;
}

// Function to generate all form data at once
async function generateAllFormData() {
    const generateButton = document.getElementById('gemini-generate');
    if (generateButton) {
        generateButton.disabled = true;
        generateButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération en cours...';
    }

    try {
        // Generate person name and company first
        const person = await generateWithGemini(PERSON_NAME_PROMPT).catch(() => '');
        const company = await generateWithGemini(COMPANY_NAME_PROMPT).catch(() => '');
        
        // Generate email based on person and company names if available
        let email = '';
        if (person && company) {
            // Construct email from first name and company domain
            const firstName = person.split(' ')[0].toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); // remove accents
            const companyDomain = company.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // remove accents
                .replace(/[^\w\s]/gi, '') // remove special chars
                .replace(/\s+/g, ''); // remove spaces
            
            email = `${firstName}@${companyDomain}.com`;
        } else {
            // Fallback to generating a random email
            email = await generateWithGemini(EMAIL_PROMPT).catch(() => '');
        }
        
        // Generate phone number and validate it's in Tunisian format
        let phone = await generateWithGemini(PHONE_PROMPT).catch(() => '');
        phone = validateTunisianPhoneNumber(phone);

        // Check if any generation failed completely
        if (!person && !company && !email && !phone) {
            throw new Error('Impossible de générer les données. Veuillez vérifier votre connexion Internet et réessayer.');
        }

        // Fill form fields
        document.getElementById('nom_sponsor').value = person || '';
        document.getElementById('entreprise').value = company || '';
        document.getElementById('mail').value = email || '';
        document.getElementById('telephone').value = phone || '';

        // Trigger validation if available
        const fields = ['nom_sponsor', 'entreprise', 'mail', 'telephone'];
        fields.forEach(fieldId => {
            const element = document.getElementById(fieldId);
            if (element) {
                // Trigger input event to activate any validation
                const event = new Event('input', { bubbles: true });
                element.dispatchEvent(event);
            }
        });
    } catch (error) {
        console.error('Error generating form data:', error);
        // Create a more user-friendly error message
        const errorMessage = document.createElement('div');
        errorMessage.className = 'error-message';
        errorMessage.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${error.message || 'Erreur lors de la génération des données. Veuillez réessayer.'}`;
        
        // Show error message at the top of the form
        const form = document.querySelector('form[method="POST"]');
        if (form) {
            // Remove any existing error messages
            const existingError = form.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            // Insert at the beginning of the form
            form.insertBefore(errorMessage, form.firstChild);
            
            // Auto-remove after 10 seconds
            setTimeout(() => {
                errorMessage.style.opacity = '0';
                errorMessage.style.transition = 'opacity 0.5s ease';
                setTimeout(() => errorMessage.remove(), 500);
            }, 10000);
        } else {
            // Fallback to alert if form not found
            alert(error.message || 'Erreur lors de la génération des données. Veuillez réessayer.');
        }
    } finally {
        // Reset button state
        if (generateButton) {
            generateButton.disabled = false;
            generateButton.innerHTML = '<i class="fas fa-magic"></i> Générer automatiquement';
        }
    }
}

// Function to test if the API key is valid
async function testApiKey() {
    try {
        const response = await fetch(`https://generativelanguage.googleapis.com/v1/models/${GEMINI_MODEL}?key=${GEMINI_API_KEY}`);
        if (!response.ok) {
            console.error('API key validation failed:', await response.json());
            return false;
        }
        return true;
    } catch (error) {
        console.error('API key test error:', error);
        return false;
    }
}

// Initialize when the document is loaded
document.addEventListener('DOMContentLoaded', async function() {
    // First verify the API key is valid
    const isApiKeyValid = await testApiKey();
    
    // Create and add the generation button to the form
    const sponsorForm = document.querySelector('form[method="POST"]');
    if (sponsorForm && isApiKeyValid) {
        const buttonContainer = document.createElement('div');
        buttonContainer.className = 'btn-container';
        buttonContainer.style.marginBottom = '25px';
        
        // Add description text
        const descriptionText = document.createElement('p');
        descriptionText.style.fontSize = '0.9rem';
        descriptionText.style.color = '#666';
        descriptionText.style.marginBottom = '10px';
        descriptionText.innerHTML = '<i class="fas fa-info-circle"></i> Ce bouton utilise l\'IA de Google Gemini pour générer automatiquement des données professionnelles de sponsor.';
        
        const generateButton = document.createElement('button');
        generateButton.type = 'button';
        generateButton.id = 'gemini-generate';
        generateButton.className = 'btn btn-purple';
        generateButton.innerHTML = '<i class="fas fa-magic"></i> Générer automatiquement';
        generateButton.title = 'Utilise l\'IA Gemini pour remplir automatiquement le formulaire avec des données professionnelles';
        generateButton.onclick = generateAllFormData;
        
        buttonContainer.appendChild(descriptionText);
        buttonContainer.appendChild(generateButton);
        
        // Insert at the top of the form, before the first form-group
        const firstFormGroup = sponsorForm.querySelector('.form-group');
        if (firstFormGroup) {
            sponsorForm.insertBefore(buttonContainer, firstFormGroup);
        } else {
            sponsorForm.prepend(buttonContainer);
        }
    } else if (sponsorForm && !isApiKeyValid) {
        console.error('Gemini API key is invalid or API is unreachable. Auto-fill button disabled.');
    }
}); 