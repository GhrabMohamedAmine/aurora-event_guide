document.addEventListener("DOMContentLoaded", function () {
  // Initialisation des modals Bootstrap
  const loginModal = new bootstrap.Modal(document.getElementById("loginModal"));
  const signupModal = new bootstrap.Modal(
    document.getElementById("signupModal")
  );

  // Gestion du clic sur "Inscrivez-vous"
  document
    .getElementById("showSignupBtn")
    ?.addEventListener("click", function (e) {
      e.preventDefault();
      loginModal.hide();
      signupModal.show();
    });

  // Gestion du clic sur "Connectez-vous"
  document
    .getElementById("showLoginBtn")
    ?.addEventListener("click", function (e) {
      e.preventDefault();
      signupModal.hide();
      loginModal.show();
    });

  // Gestion du mot de passe oublié
  document
    .getElementById("showResetPassword")
    ?.addEventListener("click", function (e) {
      e.preventDefault();
      // Ici vous pouvez ajouter la logique pour le mot de passe oublié
      alert("Fonctionnalité mot de passe oublié à implémenter");
    });
});
