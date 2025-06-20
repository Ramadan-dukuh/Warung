
     // Complete JavaScript implementation for the POS System

document.addEventListener('DOMContentLoaded', function() {
    // Cache DOM elements
    const searchInput = document.getElementById('searchInput');
    const productTableBody = document.getElementById('productTableBody');
    const pagination = document.getElementById('pagination');
    const productSelect = document.getElementById('productSelect');
    const quantityInput = document.getElementById('quantityInput');
    const itemPrice = document.getElementById('itemPrice');
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const stockInfo = document.getElementById('stockInfo');
    const quickAddForm = document.getElementById('quickAddForm');
    const cartItems = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    const cartSummary = document.getElementById('cartSummary');
    const cartTotal = document.getElementById('cartTotal');
    const paymentForm = document.getElementById('paymentForm');
    const paymentAmount = document.getElementById('paymentAmount');
    const clearAfterPayment = document.getElementById('clearAfterPayment');
    const processPayment = document.getElementById('processPayment');
    const clearCart = document.getElementById('clearCart');
    const paymentResult = document.getElementById('paymentResult');
    const paymentDisplay = document.getElementById('paymentDisplay');
    const changeDisplay = document.getElementById('changeDisplay');
    const backToTop = document.getElementById('backToTop');
    const notificationContainer = document.getElementById('notification-container');

    // Sample product data (would be loaded from your PHP backend in a real app)
    const products = [
        { kodeBarang: '001', namaBarang: 'Laptop Acer Aspire', hargaBarang: 7500000, stok: 15, idKategori: '1' },
        { kodeBarang: '002', namaBarang: 'Mouse Wireless Logitech', hargaBarang: 250000, stok: 30, idKategori: '1' },
        { kodeBarang: '003', namaBarang: 'Keyboard Mechanical', hargaBarang: 450000, stok: 25, idKategori: '1' },
        { kodeBarang: '004', namaBarang: 'Monitor LED 24"', hargaBarang: 1800000, stok: 10, idKategori: '1' },
        { kodeBarang: '005', namaBarang: 'Headset Gaming', hargaBarang: 350000, stok: 20, idKategori: '1' },
        { kodeBarang: '006', namaBarang: 'Flashdisk 32GB', hargaBarang: 85000, stok: 50, idKategori: '1' },
        { kodeBarang: '007', namaBarang: 'SSD 512GB', hargaBarang: 950000, stok: 15, idKategori: '1' },
        { kodeBarang: '008', namaBarang: 'Power Bank 10000mAh', hargaBarang: 299000, stok: 25, idKategori: '2' },
        { kodeBarang: '009', namaBarang: 'Charger Laptop Universal', hargaBarang: 180000, stok: 15, idKategori: '2' },
        { kodeBarang: '010', namaBarang: 'USB Hub 4 Port', hargaBarang: 75000, stok: 30, idKategori: '1' },
        { kodeBarang: '011', namaBarang: 'Webcam HD', hargaBarang: 350000, stok: 10, idKategori: '1' },
        { kodeBarang: '012', namaBarang: 'Cooling Pad Laptop', hargaBarang: 150000, stok: 20, idKategori: '1' }
    ];

    // Initialize cart
    let cart = [];
    let currentPage = 1;
    const itemsPerPage = 5;

    // Load products and populate dropdowns
    loadProducts();
    populateProductDropdown();

    // Event listeners
    searchInput.addEventListener('input', function() {
        currentPage = 1;
        loadProducts();
    });

    backToTop.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({top: 0, behavior: 'smooth'});
    });

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 100) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });

    productSelect.addEventListener('change', updatePriceCalculation);
    quantityInput.addEventListener('input', updatePriceCalculation);

    quickAddForm.addEventListener('submit', function(e) {
        e.preventDefault();
        addToCart();
    });

    clearCart.addEventListener('click', function() {
        if (confirm('Kosongkan keranjang?')) {
            cart = [];
            updateCartDisplay();
            showNotification('Keranjang berhasil dikosongkan', 'success');
        }
    });

    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        processPaymentAction();
    });

    // Functions
    function loadProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const filteredProducts = products.filter(product => 
            product.namaBarang.toLowerCase().includes(searchTerm)
        );

        // Calculate pagination
        const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const paginatedProducts = filteredProducts.slice(startIndex, startIndex + itemsPerPage);

        // Render products
        renderProducts(paginatedProducts);
        renderPagination(totalPages);
    }

    function renderProducts(productsList) {
        productTableBody.innerHTML = '';

        if (productsList.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `
                <td colspan="4" class="text-center">
                    <div class="empty-state">
                        <p>Tidak ada data barang yang tersedia.</p>
                        <p>Silakan ubah kata kunci pencarian.</p>
                    </div>
                </td>
            `;
            productTableBody.appendChild(emptyRow);
            return;
        }

        productsList.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="product-name">${product.namaBarang}</div>
                </td>
                <td>Rp ${formatNumber(product.hargaBarang)}</td>
                <td>${product.stok}</td>
                <td class="product-action">
                    <button class="quick-add-btn" data-code="${product.kodeBarang}">+ Tambah</button>
                </td>
            `;
            productTableBody.appendChild(row);

            // Add event listener to the add button
            const addButton = row.querySelector('.quick-add-btn');
            addButton.addEventListener('click', function() {
                const productCode = this.getAttribute('data-code');
                quickAddProduct(productCode);
            });
        });
    }

    function renderPagination(totalPages) {
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let paginationHTML = '';
        
        // Previous button
        const prevDisabled = currentPage <= 1 ? 'disabled' : '';
        paginationHTML += `<a href="#" class="pagination-item ${prevDisabled}" data-page="prev">«</a>`;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const active = i === currentPage ? 'active' : '';
            paginationHTML += `<a href="#" class="pagination-item ${active}" data-page="${i}">${i}</a>`;
        }

        // Next button
        const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
        paginationHTML += `<a href="#" class="pagination-item ${nextDisabled}" data-page="next">»</a>`;

        pagination.innerHTML = paginationHTML;

        // Add event listeners to pagination items
        const paginationItems = pagination.querySelectorAll('.pagination-item');
        paginationItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (this.classList.contains('disabled')) {
                    return;
                }

                const page = this.getAttribute('data-page');
                
                if (page === 'prev') {
                    currentPage--;
                } else if (page === 'next') {
                    currentPage++;
                } else {
                    currentPage = parseInt(page);
                }

                loadProducts();
                window.scrollTo({top: 0, behavior: 'smooth'});
            });
        });
    }

    function populateProductDropdown() {
        productSelect.innerHTML = '<option value="">-- Pilih Produk --</option>';
        
        products.forEach(product => {
            const option = document.createElement('option');
            option.value = product.kodeBarang;
            option.textContent = `${product.namaBarang} - Rp ${formatNumber(product.hargaBarang)}`;
            productSelect.appendChild(option);
        });
    }

    function updatePriceCalculation() {
        const selectedCode = productSelect.value;
        const quantity = parseInt(quantityInput.value) || 0;
        
        if (!selectedCode || quantity <= 0) {
            itemPrice.value = '';
            subtotalDisplay.value = '';
            stockInfo.textContent = '';
            return;
        }
        
        const product = products.find(p => p.kodeBarang === selectedCode);
        if (product) {
            itemPrice.value = `Rp ${formatNumber(product.hargaBarang)}`;
            subtotalDisplay.value = `Rp ${formatNumber(product.hargaBarang * quantity)}`;
            stockInfo.textContent = `Stok tersedia: ${product.stok}`;
            
            // Change color if quantity exceeds stock
            if (quantity > product.stok) {
                stockInfo.classList.add('text-danger');
            } else {
                stockInfo.classList.remove('text-danger');
            }
        }
    }

    function quickAddProduct(productCode) {
        const product = products.find(p => p.kodeBarang === productCode);
        if (!product) return;

        productSelect.value = productCode;
        quantityInput.value = 1;
        updatePriceCalculation();
        
        // Scroll to the quick add form
        quickAddForm.scrollIntoView({ behavior: 'smooth' });
    }

    function addToCart() {
        const selectedCode = productSelect.value;
        const quantity = parseInt(quantityInput.value) || 0;
        
        if (!selectedCode || quantity <= 0) {
            showNotification('Silakan pilih produk dan masukkan jumlah yang valid', 'error');
            return;
        }
        
        const product = products.find(p => p.kodeBarang === selectedCode);
        if (!product) {
            showNotification('Produk tidak ditemukan', 'error');
            return;
        }
        
        if (quantity > product.stok) {
            showNotification('Jumlah melebihi stok yang tersedia', 'error');
            return;
        }
        
        // Check if product already exists in cart
        const existingItemIndex = cart.findIndex(item => item.kodeBarang === selectedCode);
        
        if (existingItemIndex !== -1) {
            // Update quantity if item exists
            const newQuantity = cart[existingItemIndex].quantity + quantity;
            
            if (newQuantity > product.stok) {
                showNotification('Total jumlah melebihi stok yang tersedia', 'error');
                return;
            }
            
            cart[existingItemIndex].quantity = newQuantity;
            showNotification('Jumlah barang berhasil diperbarui', 'success');
        } else {
            // Add new item to cart
            cart.push({
                kodeBarang: product.kodeBarang,
                namaBarang: product.namaBarang,
                price: product.hargaBarang,
                quantity: quantity
            });
            showNotification('Barang berhasil ditambahkan ke keranjang', 'success');
        }
        
        // Reset form
        productSelect.value = '';
        quantityInput.value = 1;
        itemPrice.value = '';
        subtotalDisplay.value = '';
        stockInfo.textContent = '';
        
        // Update cart display
        updateCartDisplay();
    }

    function updateCartDisplay() {
        // Show/hide empty cart message
        if (cart.length === 0) {
            emptyCart.style.display = 'block';
            cartSummary.style.display = 'none';
            paymentForm.style.display = 'none';
            paymentResult.style.display = 'none';
            cartItems.innerHTML = '';
            cartItems.appendChild(emptyCart);
            return;
        }
        
        emptyCart.style.display = 'none';
        cartSummary.style.display = 'block';
        paymentForm.style.display = 'block';
        
        // Clear cart items
        cartItems.innerHTML = '';
        
        // Calculate total
        let total = 0;
        
        // Add items to cart display
        cart.forEach((item, index) => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item fade-in';
            cartItem.innerHTML = `
                <div class="cart-info">
                    <div class="cart-item-name">${item.namaBarang}</div>
                    <div class="cart-item-price">${item.quantity} x Rp ${formatNumber(item.price)}</div>
                </div>
                <div class="cart-item-subtotal">Rp ${formatNumber(subtotal)}</div>
                <button class="remove-btn" data-index="${index}">×</button>
            `;
            
            cartItems.appendChild(cartItem);
            
            // Add event listener to remove button
            const removeBtn = cartItem.querySelector('.remove-btn');
            removeBtn.addEventListener('click', function() {
                const itemIndex = parseInt(this.getAttribute('data-index'));
                removeCartItem(itemIndex);
            });
        });
        
        // Update total
        cartTotal.textContent = `Rp ${formatNumber(total)}`;
        
        // Set minimum payment amount
        paymentAmount.min = total;
        paymentAmount.value = total;
    }

    function removeCartItem(index) {
        if (index >= 0 && index < cart.length) {
            const itemName = cart[index].namaBarang;
            cart.splice(index, 1);
            updateCartDisplay();
            showNotification(`${itemName} dihapus dari keranjang`, 'success');
        }
    }

    function processPaymentAction() {
        const payment = parseFloat(paymentAmount.value) || 0;
        const total = calculateTotal();
        
        if (payment < total) {
            showNotification('Jumlah pembayaran kurang dari total belanja', 'error');
            return;
        }
        
        const change = payment - total;
        
        // Display payment result
        paymentDisplay.textContent = `Rp ${formatNumber(payment)}`;
        changeDisplay.textContent = `Rp ${formatNumber(change)}`;
        paymentResult.style.display = 'flex';
        
        // Clear cart if option is checked
        if (clearAfterPayment.checked) {
            cart = [];
            updateCartDisplay();
        }
        
        showNotification('Pembayaran berhasil diproses', 'success');
    }

    function calculateTotal() {
        return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} fade-in`;
        notification.textContent = message;
        
        notificationContainer.appendChild(notification);
        
        // Remove notification after 5 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }
});
document.addEventListener('DOMContentLoaded', function() {
    // Handle pagination clicks
    document.querySelectorAll('.pagination-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            loadData(this.dataset.page, this.dataset.keyword);
        });
    });
    
    // Initial load
    loadData(1, '');
});

function loadData(page, keyword) {
    // Show loading indicator
    document.getElementById('table-container').innerHTML = '<div class="loading">Memuat data...</div>';
    
    // Create AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `get_data_barang.php?page=${page}&cari=${encodeURIComponent(keyword)}`, true);
    
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById('table-container').innerHTML = this.responseText;
            
            // Re-attach event listeners to new pagination links
            document.querySelectorAll('.pagination-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadData(this.dataset.page, this.dataset.keyword);
                });
            });
        } else {
            document.getElementById('table-container').innerHTML = 
                '<div class="error">Gagal memuat data. Silakan coba lagi.</div>';
        }
    };
    
    xhr.onerror = function() {
        document.getElementById('table-container').innerHTML = 
            '<div class="error">Terjadi kesalahan koneksi.</div>';
    };
    
    xhr.send();
}