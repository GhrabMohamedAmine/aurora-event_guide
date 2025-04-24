document.addEventListener("DOMContentLoaded", function () {
  // Common function to handle modal transitions
  function switchModals(closeModalId, openModalId) {
    const closeModal = bootstrap.Modal.getInstance(document.getElementById(closeModalId));
    if (closeModal) {
      closeModal.hide();
    }
    setTimeout(() => {
      const openModal = new bootstrap.Modal(document.getElementById(openModalId));
      openModal.show();
    }, 150);
  }

  // Gestion du clic sur "Inscrivez-vous" dans la modale de connexion
  document.getElementById("loginToSignupBtn").addEventListener("click", function (e) {
    e.preventDefault();
    switchModals("loginModal", "signupModal");
  });

  // Gestion du clic sur "Connectez-vous"
  document.getElementById("showLoginBtn").addEventListener("click", function (e) {
    e.preventDefault();
    switchModals("signupModal", "loginModal");
  });

  // Gestion du clic sur "Mot de passe oublié"
  document.getElementById("showResetPassword")?.addEventListener("click", function (e) {
    e.preventDefault();
    switchModals("loginModal", "resetPasswordModal");
  });

  // Gestion des étapes de réinitialisation (optionnel)
  const resetSteps = {
    currentStep: 1,

    init: function () {
      // Étape 1: Soumission de l'email
      document
        .getElementById("reset-submit-email")
        .addEventListener("click", (e) => {
          e.preventDefault();
          const email = document.getElementById("reset-email").value;

          if (this.validateEmail(email)) {
            this.goToStep(2);
            document.getElementById("reset-user-email").textContent = email;
          } else {
            this.showError(
              "reset-email-error",
              "Veuillez entrer une adresse email valide"
            );
          }
        });

      // Étape 2: Vérification du code
      document
        .getElementById("reset-submit-code")
        .addEventListener("click", (e) => {
          e.preventDefault();
          const code = document.getElementById("reset-code").value;

          if (code.length === 6) {
            this.goToStep(3);
          } else {
            this.showError(
              "reset-code-error",
              "Le code doit contenir 6 chiffres"
            );
          }
        });

      // Étape 3: Nouveau mot de passe
      document
        .getElementById("reset-submit-password")
        .addEventListener("click", (e) => {
          e.preventDefault();
          const password = document.getElementById("new-password").value;
          const confirmPassword = document.getElementById(
            "confirm-new-password"
          ).value;

          if (password.length < 8) {
            this.showError(
              "new-password-error",
              "Le mot de passe doit contenir au moins 8 caractères"
            );
          } else if (password !== confirmPassword) {
            this.showError(
              "new-password-error",
              "Les mots de passe ne correspondent pas"
            );
          } else {
            this.showConfirmation();
          }
        });

      // Renvoyer le code
      document
        .getElementById("reset-resend-code")
        .addEventListener("click", (e) => {
          e.preventDefault();
          alert("Un nouveau code a été envoyé à votre adresse email");
        });
    },

    goToStep: function (step) {
      // Cache toutes les étapes
      document.querySelectorAll('[id^="reset-step"]').forEach((el) => {
        el.classList.add("hidden");
      });

      // Affiche l'étape courante
      document.getElementById(`reset-step${step}`).classList.remove("hidden");
      this.currentStep = step;

      // Met à jour les indicateurs d'étape
      document.querySelectorAll(".step").forEach((el, index) => {
        if (index + 1 < step) {
          el.classList.add("completed");
          el.classList.remove("active");
        } else if (index + 1 === step) {
          el.classList.add("active");
          el.classList.remove("completed");
        } else {
          el.classList.remove("active", "completed");
        }
      });
    },

    showConfirmation: function () {
      document.getElementById("reset-step3").classList.add("hidden");
      document.getElementById("reset-confirmation").classList.remove("hidden");
    },

    validateEmail: function (email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    },

    showError: function (id, message) {
      const errorElement = document.getElementById(id);
      errorElement.textContent = message;
      errorElement.classList.remove("hidden");
      setTimeout(() => errorElement.classList.add("hidden"), 3000);
    },
  };

  // Initialise les étapes de réinitialisation
  resetSteps.init();

  // Pour tester: ouvre automatiquement la modale de connexion au chargement
  // var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
  // loginModal.show();
});
