 // DOM Elements
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const rememberCheckbox = document.getElementById('remember');
    const btnLogin = document.getElementById('btn-login');
    const alert = document.getElementById('alert');
    const alertIcon = document.getElementById('alert-icon');
    const alertMessage = document.getElementById('alert-message');
    const forgotLink = document.getElementById('forgot-link');

    // Credentials (en production, utiliser une vraie API)
    const VALID_CREDENTIALS = [
      { username: 'admin', password: 'admin123', role: 'administrateur' },
      { username: 'technicien', password: 'tech2024', role: 'technicien' },
      { username: 'superviseur', password: 'super2024', role: 'superviseur' }
    ];

    // Check if already logged in
    window.addEventListener('DOMContentLoaded', () => {
      const isLoggedIn = sessionStorage.getItem('isLoggedIn');
      const rememberMe = localStorage.getItem('rememberMe');
      
      if (isLoggedIn === 'true' || rememberMe === 'true') {
        // Already logged in, redirect immediately
        window.location.href = 'dashboard.html';
      }

      // Check for remembered username
      const savedUsername = localStorage.getItem('savedUsername');
      if (savedUsername) {
        usernameInput.value = savedUsername;
        rememberCheckbox.checked = true;
      }
    });

    // Show alert
    function showAlert(message, type = 'error') {
      alert.className = `alert ${type} show`;
      alertIcon.textContent = type === 'error' ? '❌' : '✅';
      alertMessage.textContent = message;
      
      setTimeout(() => {
        alert.classList.remove('show');
      }, 5000);
    }

    // Login function
    function handleLogin() {
      const username = usernameInput.value.trim();
      const password = passwordInput.value.trim();

      // Validation
      if (!username || !password) {
        showAlert('Veuillez remplir tous les champs', 'error');
        return;
      }

      // Show loading
      btnLogin.classList.add('loading');
      btnLogin.disabled = true;

      // Simulate API call delay
      setTimeout(() => {
        // Check credentials
        const user = VALID_CREDENTIALS.find(
          u => u.username === username && u.password === password
        );

        if (user) {
          // Success
          showAlert(`Bienvenue ${user.role} !`, 'success');
          
          // Save session
          sessionStorage.setItem('isLoggedIn', 'true');
          sessionStorage.setItem('username', username);
          sessionStorage.setItem('userRole', user.role);
          sessionStorage.setItem('loginTime', new Date().toISOString());

          // Remember me
          if (rememberCheckbox.checked) {
            localStorage.setItem('rememberMe', 'true');
            localStorage.setItem('savedUsername', username);
          } else {
            localStorage.removeItem('rememberMe');
            localStorage.removeItem('savedUsername');
          }

          // Redirect after short delay
          setTimeout(() => {
            window.location.href = 'dashboard.html';
          }, 1000);
        } else {
          // Failed
          btnLogin.classList.remove('loading');
          btnLogin.disabled = false;
          showAlert('Identifiant ou mot de passe incorrect', 'error');
          passwordInput.value = '';
          passwordInput.focus();
        }
      }, 1500); // Simulate network delay
    }

    // Event listeners
    btnLogin.addEventListener('click', handleLogin);

    usernameInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        passwordInput.focus();
      }
    });

    passwordInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        handleLogin();
      }
    });

    forgotLink.addEventListener('click', (e) => {
      e.preventDefault();
      showAlert('Contactez l\'administrateur système pour réinitialiser votre mot de passe', 'error');
    });

    // Auto-focus on username
    usernameInput.focus();