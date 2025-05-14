/**
 * Dashboard functionality for the sponsor dashboard
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide notifications after 5 seconds
    const notifications = document.querySelectorAll('.notification');
    if (notifications.length > 0) {
        setTimeout(function() {
            notifications.forEach(function(notification) {
                notification.style.opacity = '0';
                setTimeout(function() {
                    notification.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }

    // Show/Hide dashboard sections
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabButtons.length > 0) {
        tabButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                // Get tab target from onclick attribute if data-target is not present
                const targetId = this.getAttribute('data-target') || this.getAttribute('onclick')?.match(/showTab\('(.+?)'\)/)?.[1];
                
                if (targetId) {
                    // Hide all tabs
                    tabContents.forEach(function(content) {
                        content.classList.remove('active');
                    });
                    
                    // Remove active class from all buttons
                    tabButtons.forEach(function(btn) {
                        btn.classList.remove('active');
                    });
                    
                    // Show selected tab
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        targetElement.classList.add('active');
                    }
                    
                    // Set active class on clicked button
                    this.classList.add('active');
                }
            });
        });
    }

    // Initialize existing showTab function for compatibility
    window.showTab = function(tabId) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(function(content) {
            content.classList.remove('active');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-button').forEach(function(btn) {
            btn.classList.remove('active');
        });
        
        // Show selected tab
        const targetElement = document.getElementById(tabId);
        if (targetElement) {
            targetElement.classList.add('active');
        }
        
        // Set active class on button with matching onclick
        const button = document.querySelector(`.tab-button[onclick="showTab('${tabId}')"]`);
        if (button) {
            button.classList.add('active');
        }
    };

    // Filter sponsorships by status if the filter exists
    const statusFilter = document.getElementById('status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const status = this.value;
            const items = document.querySelectorAll('.request-item');
            
            items.forEach(function(item) {
                if (status === 'all' || item.classList.contains('status-' + status)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Animate stats on load
    const statValues = document.querySelectorAll('.stat-content h3');
    if (statValues.length > 0) {
        statValues.forEach(function(element) {
            const finalValue = element.textContent;
            
            // Only animate if it's a number
            if (!isNaN(parseFloat(finalValue.replace(/[^\d.-]/g, '')))) {
                // Start from zero
                element.textContent = '0';
                
                // Animate to final value
                const finalNum = parseFloat(finalValue.replace(/[^\d.-]/g, ''));
                const duration = 1500; // Animation duration in ms
                const steps = 20;
                const increment = finalNum / steps;
                
                let currentValue = 0;
                const interval = duration / steps;
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    
                    if (currentValue >= finalNum) {
                        clearInterval(timer);
                        element.textContent = finalValue; // Set exact final value
                    } else {
                        // Format number based on whether it has euro symbol
                        if (finalValue.includes('€')) {
                            element.textContent = Math.round(currentValue).toLocaleString('fr-FR') + ' €';
                        } else {
                            element.textContent = Math.round(currentValue);
                        }
                    }
                }, interval);
            }
        });
    }

    // Confirm deletion
    window.confirmDelete = function(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce sponsor ?')) {
            // Create and submit a form programmatically
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'front.php';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            form.appendChild(actionInput);
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    };

    // Date formatting helper
    window.formatDate = function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    // Number formatting helper
    window.formatCurrency = function(amount) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    };
}); 