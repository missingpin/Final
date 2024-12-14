document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menu-toggle');
    const navList = document.getElementById('nav-list');
    const alertContainer = document.getElementById('alert-container');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            navList.style.display = navList.style.display === 'flex' ? 'none' : 'flex';
        });
    }

    alertContainer.style.display = 'none';
    fetchLowStockProducts();
    setInterval(fetchLowStockProducts, 10000);

    document.addEventListener('click', (event) => {
        if (!alertContainer.contains(event.target) && !event.target.classList.contains('bx-bell')) {
            alertContainer.style.display = 'none';
        }
    });
});

function toggleAlerts() {
    const alertContainer = document.getElementById('alert-container');
    alertContainer.style.display = alertContainer.style.display === 'none' ? 'block' : 'none';
}

function fetchLowStockProducts() {
    console.log("Fetching low stock products...");
    fetch('alertcheck.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log("Fetched alerts:", data);
            displayLowStockAlerts(data);
        })
        .catch(error => console.error('Error fetching low stock products:', error));
}

function displayLowStockAlerts(products) {
    const alertContainer = document.getElementById('low-stock-alerts');
    alertContainer.innerHTML = '';

    if (products.length === 0) {
        alertContainer.innerHTML = '<p>No low stock alerts.</p>';
    } else {
        const limitedProducts = products.slice(0, 5);
        limitedProducts.forEach(product => {
            const alertItem = document.createElement('div');
            alertItem.className = 'alert alert-warning';
            alertItem.textContent = `Low-stock for ${product.productname}: ${product.quantity} left`;
            alertContainer.appendChild(alertItem);
        });
    }
}

function toggleUserMenu() {
    const userMenu = document.getElementById('user-menu');
    userMenu.style.display = userMenu.style.display === 'none' || userMenu.style.display === '' ? 'block' : 'none';
}

// Close the user menu if clicking outside
document.addEventListener('click', (event) => {
    const userMenu = document.getElementById('user-menu');
    const userIcon = document.getElementById('user-icon');

    if (!userIcon.contains(event.target) && !userMenu.contains(event.target)) {
        userMenu.style.display = 'none';
    }
});
