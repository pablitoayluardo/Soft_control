// =====================================================
// SCRIPT DE LOGIN - SISTEMA DE CONTROL
// Modo local sin validación de base de datos
// =====================================================

// Elementos del DOM
const loginForm = document.getElementById('loginForm');
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const togglePasswordBtn = document.getElementById('togglePassword');
const rememberMeCheckbox = document.getElementById('rememberMe');
const loginBtn = document.querySelector('.login-btn');
const errorMessages = document.querySelectorAll('.error-message');

// Usuarios de prueba para modo local
const testUsers = [
    {
        username: 'admin',
        password: '123456',
        fullName: 'Administrador del Sistema',
        email: 'admin@globocity.com.ec',
        role: 'admin'
    },
    {
        username: 'usuario1',
        password: '123456',
        fullName: 'Usuario Ejemplo 1',
        email: 'usuario1@globocity.com.ec',
        role: 'usuario'
    },
    {
        username: 'moderador1',
        password: '123456',
        fullName: 'Moderador Ejemplo',
        email: 'moderador1@globocity.com.ec',
        role: 'moderador'
    }
];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    loadRememberedUsername();
    checkOfflineStatus();
});

// Configurar event listeners
function setupEventListeners() {
    // Formulario de login
    loginForm.addEventListener('submit', handleLoginSubmit);
    
    // Toggle de contraseña
    togglePasswordBtn.addEventListener('click', togglePasswordVisibility);
    
    // Validación en tiempo real
    usernameInput.addEventListener('input', () => validateField('username'));
    passwordInput.addEventListener('input', () => validateField('password'));
    
    // Detectar cambios en la conexión
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
}

// Manejar envío del formulario
async function handleLoginSubmit(e) {
    e.preventDefault();

    if (!validateForm()) {
        return;
    }

    const originalText = loginBtn.innerHTML;
    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';
    loginBtn.disabled = true;

    const formData = {
        username: usernameInput.value.trim(),
        password: passwordInput.value,
        rememberMe: rememberMeCheckbox.checked
    };

    try {
        // Simular delay de red
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Validar credenciales localmente
        const user = validateLocalCredentials(formData.username, formData.password);
        
        if (user) {
            handleSuccessfulLogin(user, formData);
        } else {
            handleFailedLogin('Usuario o contraseña incorrectos');
        }

    } catch (error) {
        console.error('Error en login:', error);
        handleLoginError('Error de conexión. Verifique su conexión a internet.');
    } finally {
        loginBtn.innerHTML = originalText;
        loginBtn.disabled = false;
    }
}

// Validar credenciales localmente
function validateLocalCredentials(username, password) {
    return testUsers.find(user => 
        (user.username === username || user.email === username) && 
        user.password === password
    );
}

// Manejar login exitoso
function handleSuccessfulLogin(user, formData) {
    // Guardar datos de sesión
    const sessionData = {
        id: Math.floor(Math.random() * 1000) + 1,
        username: user.username,
        fullName: user.fullName,
        email: user.email,
        role: user.role
    };
    
    sessionStorage.setItem('user', JSON.stringify(sessionData));
    sessionStorage.setItem('token', generateMockToken());
    
    // Configurar "Recordarme"
    if (formData.rememberMe) {
        localStorage.setItem('rememberedUsername', user.username);
    } else {
        localStorage.removeItem('rememberedUsername');
    }
    
    // Mostrar notificación de éxito
    showFormSuccess('Inicio de sesión exitoso');
    
    // Redirigir al dashboard
    setTimeout(() => {
        window.location.href = 'dashboard.html';
    }, 1000);
}

// Manejar login fallido
function handleFailedLogin(message) {
    showFormError(message);
    clearForm();
}

// Manejar error de conexión
function handleLoginError(message) {
    showFormError(message);
}

// Generar token simulado
function generateMockToken() {
    return 'mock_token_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

// Validar formulario
function validateForm() {
    let isValid = true;
    
    isValid = validateField('username') && isValid;
    isValid = validateField('password') && isValid;
    
    return isValid;
}

// Validar campo específico
function validateField(fieldName) {
    const field = fieldName === 'username' ? usernameInput : passwordInput;
    const errorElement = document.getElementById(fieldName + '-error');
    
    let isValid = true;
    let errorMessage = '';
    
    switch (fieldName) {
        case 'username':
            if (!field.value.trim()) {
                errorMessage = 'El usuario es requerido';
                isValid = false;
            } else if (field.value.trim().length < 3) {
                errorMessage = 'El usuario debe tener al menos 3 caracteres';
                isValid = false;
            }
            break;
            
        case 'password':
            if (!field.value) {
                errorMessage = 'La contraseña es requerida';
                isValid = false;
            } else if (field.value.length < 6) {
                errorMessage = 'La contraseña debe tener al menos 6 caracteres';
                isValid = false;
            }
            break;
    }
    
    // Mostrar/ocultar error
    if (errorElement) {
        errorElement.textContent = errorMessage;
        errorElement.style.display = errorMessage ? 'block' : 'none';
    }
    
    // Aplicar clase de error al campo
    field.classList.toggle('error', !isValid);
    
    return isValid;
}

// Toggle de visibilidad de contraseña
function togglePasswordVisibility() {
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    
    const icon = togglePasswordBtn.querySelector('i');
    icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

// Cargar usuario recordado
function loadRememberedUsername() {
    const rememberedUsername = localStorage.getItem('rememberedUsername');
    if (rememberedUsername) {
        usernameInput.value = rememberedUsername;
        rememberMeCheckbox.checked = true;
    }
}

// Mostrar error del formulario
function showFormError(message) {
    // Limpiar errores anteriores
    clearFormErrors();
    
    // Crear mensaje de error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    // Insertar antes del formulario
    loginForm.parentNode.insertBefore(errorDiv, loginForm);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.remove();
        }
    }, 5000);
}

// Mostrar éxito del formulario
function showFormSuccess(message) {
    // Limpiar errores anteriores
    clearFormErrors();
    
    // Crear mensaje de éxito
    const successDiv = document.createElement('div');
    successDiv.className = 'form-success';
    successDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
    
    // Insertar antes del formulario
    loginForm.parentNode.insertBefore(successDiv, loginForm);
}

// Limpiar errores del formulario
function clearFormErrors() {
    const existingErrors = document.querySelectorAll('.form-error, .form-success');
    existingErrors.forEach(error => error.remove());
}

// Limpiar formulario
function clearForm() {
    passwordInput.value = '';
    passwordInput.type = 'password';
    togglePasswordBtn.querySelector('i').className = 'fas fa-eye';
}

// Manejar estado online
function handleOnline() {
    console.log('Conexión a internet restaurada');
    showFormSuccess('Conexión a internet restaurada');
}

// Manejar estado offline
function handleOffline() {
    console.log('Sin conexión a internet');
    showFormError('Sin conexión a internet. Verifique su conexión.');
}

// Verificar estado offline
function checkOfflineStatus() {
    if (!navigator.onLine) {
        handleOffline();
    }
}

// Función para mostrar notificaciones (compatible con dashboard)
function showNotification(message, type = 'info') {
    // Crear contenedor de notificaciones si no existe
    let container = document.querySelector('.notifications-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notifications-container';
        document.body.appendChild(container);
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    const icon = icons[type] || icons.info;
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="${icon}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(notification);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
} 