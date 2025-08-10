// =====================================================
// DASHBOARD JAVASCRIPT - SISTEMA DE CONTROL
// Modo local sin validación de base de datos
// =====================================================

// Verificar autenticación al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    checkAuthentication();
    loadUserInfo();
    loadRecentActivity();
    loadModuleStats();
});

// Verificar si el usuario está autenticado
function checkAuthentication() {
    const user = sessionStorage.getItem('user');
    const token = sessionStorage.getItem('token');
    
    if (!user || !token) {
        // Redirigir al login si no hay sesión
        window.location.href = 'index.html';
        return;
    }
    
    // En modo local, no verificamos con el servidor
    console.log('Usuario autenticado en modo local');
}

// Cargar información del usuario
function loadUserInfo() {
    const user = JSON.parse(sessionStorage.getItem('user') || '{}');
    
    if (user.username) {
        document.getElementById('userName').textContent = user.fullName || user.username;
        document.getElementById('userAvatar').textContent = (user.fullName || user.username).charAt(0).toUpperCase();
    }
}

// Cargar actividad reciente desde la API
async function loadRecentActivity() {
    const activityList = document.getElementById('recentActivity');
    
    if (!activityList) return;
    
    try {
        const response = await fetch('api/recent_activity.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            }
        });
        
        if (!response.ok) {
            throw new Error('Error al cargar actividad');
        }
        
        const data = await response.json();
        
        if (data.success) {
            displayRecentActivity(data.data);
        } else {
            // Si falla la API, mostrar actividad simulada
            displaySimulatedActivity();
        }
        
    } catch (error) {
        console.error('Error cargando actividad:', error);
        // Mostrar actividad simulada como fallback
        displaySimulatedActivity();
    }
}

// Mostrar actividad simulada
function displaySimulatedActivity() {
    const activityList = document.getElementById('recentActivity');
    
    if (!activityList) return;
    
    const simulatedActivities = [
        {
            accion: 'LOGIN',
            descripcion: 'Inicio de sesión exitoso',
            fecha: new Date()
        },
        {
            accion: 'SYSTEM',
            descripcion: 'Sistema iniciado correctamente',
            fecha: new Date(Date.now() - 5 * 60 * 1000)
        },
        {
            accion: 'CONFIG',
            descripcion: 'Configuración del sistema cargada',
            fecha: new Date(Date.now() - 10 * 60 * 1000)
        },
        {
            accion: 'USER',
            descripcion: 'Usuario accedió al dashboard',
            fecha: new Date(Date.now() - 15 * 60 * 1000)
        }
    ];
    
    displayRecentActivity(simulatedActivities);
}

// Mostrar actividad reciente
function displayRecentActivity(activities) {
    const activityList = document.getElementById('recentActivity');
    
    if (!activityList) return;
    
    activityList.innerHTML = '';
    
    activities.forEach(activity => {
        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item';
        
        // Usar el formato de la API o el formato simulado
        const icon = activity.icono || getActivityIcon(activity.accion || activity.tipo);
        const time = activity.tiempo || formatTime(activity.fecha);
        const description = activity.descripcion;
        
        activityItem.innerHTML = `
            <div class="activity-icon">
                <i class="${icon}"></i>
            </div>
            <div class="activity-content">
                <div class="activity-text">${description}</div>
                <div class="activity-time">${time}</div>
            </div>
        `;
        
        activityList.appendChild(activityItem);
    });
}

// Obtener icono según el tipo de actividad
function getActivityIcon(action) {
    const icons = {
        'LOGIN': 'fas fa-sign-in-alt',
        'LOGOUT': 'fas fa-sign-out-alt',
        'SYSTEM': 'fas fa-cog',
        'CONFIG': 'fas fa-wrench',
        'USER': 'fas fa-user',
        'SECURITY': 'fas fa-shield-alt',
        'MODULE': 'fas fa-cube'
    };
    
    return icons[action] || 'fas fa-info-circle';
}

// Formatear tiempo
function formatTime(date) {
    const now = new Date();
    const diff = now - new Date(date);
    
    const minutes = Math.floor(diff / (1000 * 60));
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (minutes < 1) return 'Hace un momento';
    if (minutes < 60) return `Hace ${minutes} minutos`;
    if (hours < 24) return `Hace ${hours} horas`;
    if (days < 7) return `Hace ${days} días`;
    
    return new Date(date).toLocaleDateString('es-ES');
}

// Cargar estadísticas de módulos desde la API
async function loadModuleStats() {
    try {
        const response = await fetch('api/dashboard_stats.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            }
        });
        
        if (!response.ok) {
            throw new Error('Error al cargar estadísticas');
        }
        
        const data = await response.json();
        
        if (data.success) {
            updateRealStats(data.data);
        } else {
            // Si falla la API, usar estadísticas simuladas
            updateSimulatedStats();
        }
        
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
        // Usar estadísticas simuladas como fallback
        updateSimulatedStats();
    }
}

// Actualizar estadísticas reales desde la API
function updateRealStats(stats) {
    // Actualizar contadores de módulos
    if (document.getElementById('facturacion-total')) {
        document.getElementById('facturacion-total').textContent = stats.facturacion.total;
    }
    
    if (document.getElementById('inventarios-total')) {
        document.getElementById('inventarios-total').textContent = stats.inventarios.disponibles;
    }
    
    if (document.getElementById('pagos-total')) {
        document.getElementById('pagos-total').textContent = stats.pagos.hoy;
    }
    
    if (document.getElementById('gastos-total')) {
        document.getElementById('gastos-total').textContent = stats.gastos.hoy;
    }
    
    if (document.getElementById('productos-total')) {
        document.getElementById('productos-total').textContent = stats.productos.total;
    }
}

// Actualizar estadísticas simuladas (fallback)
function updateSimulatedStats() {
    // Usar estadísticas simuladas como fallback
    const elements = {
        'facturacion-total': Math.floor(Math.random() * 50) + 10,
        'inventarios-total': Math.floor(Math.random() * 30) + 15,
        'pagos-total': Math.floor(Math.random() * 40) + 20,
        'gastos-total': Math.floor(Math.random() * 25) + 8,
        'productos-total': Math.floor(Math.random() * 35) + 12
    };
    
    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = elements[id];
        }
    });
}

// Función de logout (modo local)
async function logout() {
    try {
        // Simular llamada a API
        await new Promise(resolve => setTimeout(resolve, 500));
        
        // Limpiar datos de sesión
        sessionStorage.removeItem('user');
        sessionStorage.removeItem('token');
        localStorage.removeItem('rememberedUsername');

        // Redirigir al login
        window.location.href = 'index.html';
        
    } catch (error) {
        console.error('Error en logout:', error);

        // Limpiar datos de sesión de todas formas
        sessionStorage.removeItem('user');
        sessionStorage.removeItem('token');
        localStorage.removeItem('rememberedUsername');

        // Redirigir al login
        window.location.href = 'index.html';
    }
}

// Mostrar sección (para configuración)
function showSection(sectionName) {
    // Ocultar todas las secciones
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Mostrar la sección seleccionada
    const targetSection = document.getElementById(sectionName);
    if (targetSection) {
        targetSection.classList.add('active');
    }
}

// Mostrar notificación
function showNotification(message, type = 'info') {
    const container = document.querySelector('.notifications-container');
    
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icon = getNotificationIcon(type);
    
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

// Obtener icono de notificación
function getNotificationIcon(type) {
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    return icons[type] || icons.info;
}

// Función para abrir módulos
function openModule(moduleName) {
    // Mostrar notificación de módulo en desarrollo
    showNotification(`Módulo ${moduleName} en desarrollo`, 'info');
    
    // Aquí puedes agregar la lógica para abrir cada módulo
    switch(moduleName) {
        case 'facturacion':
            // window.location.href = 'facturacion.html';
            showNotification('Sistema de facturación próximamente disponible', 'info');
            break;
        case 'inventarios':
            // window.location.href = 'inventarios.html';
            showNotification('Sistema de inventarios próximamente disponible', 'info');
            break;
        case 'gastos':
            // window.location.href = 'gastos.html';
            showNotification('Sistema de gastos próximamente disponible', 'info');
            break;
        case 'pagos':
            // window.location.href = 'pagos.html';
            showNotification('Sistema de pagos próximamente disponible', 'info');
            break;
        case 'precios':
            // window.location.href = 'precios.html';
            showNotification('Sistema de precios próximamente disponible', 'info');
            break;
        case 'productos':
            // window.location.href = 'productos.html';
            showNotification('Sistema de productos próximamente disponible', 'info');
            break;
        case 'configuracion':
            showSection('configuracion');
            showNotification('Configuración del sistema', 'info');
            break;
    }
}

// Función para refrescar actividad
function refreshActivity() {
    loadRecentActivity();
    showNotification('Actividad actualizada', 'success');
}

// Función para refrescar estadísticas
function refreshStats() {
    loadModuleStats();
    showNotification('Estadísticas actualizadas', 'success');
}

// Detectar conexión a internet
window.addEventListener('online', function() {
    showNotification('Conexión a internet restaurada', 'success');
});

window.addEventListener('offline', function() {
    showNotification('Sin conexión a internet', 'warning');
});

// Función para manejar errores globales
window.addEventListener('error', function(e) {
    console.error('Error global:', e.error);
    showNotification('Ha ocurrido un error inesperado', 'error');
});

// Función para manejar promesas rechazadas
window.addEventListener('unhandledrejection', function(e) {
    console.error('Promesa rechazada:', e.reason);
    showNotification('Error en la operación', 'error');
}); 