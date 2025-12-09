/**
 * Gerenciamento do Carrinho via AJAX
 */
document.addEventListener('DOMContentLoaded', function () {

    // 1. Interceptar formulários de "Adicionar ao Carrinho"
    const addCartForms = document.querySelectorAll('form[action="/carrinho/add"]');

    addCartForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalContent = submitBtn.innerHTML;
            const originalWidth = submitBtn.offsetWidth;

            submitBtn.style.width = originalWidth + 'px';
            submitBtn.innerHTML = '<span class="loading-spinner">...</span>';
            submitBtn.disabled = true;

            const formData = new FormData(form);

            fetch('/carrinho/add', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        updateCartCount(data.cart_count);
                    } else {
                        // Se precisar redirecionar (não autenticado)
                        if (data.redirect) {
                            showToast(data.message, 'error');
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        } else {
                            showToast(data.message || 'Erro ao adicionar produto', 'error');
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showToast('Erro de conexão', 'error');
                })
                .finally(() => {
                    setTimeout(() => {
                        submitBtn.innerHTML = originalContent;
                        submitBtn.disabled = false;
                        submitBtn.style.width = '';
                    }, 500);
                });
        });
    });

    // 2. Interceptar SUBMIT dos formulários no carrinho (update e remove)
    const cartContainer = document.querySelector('.cart-container');

    if (cartContainer) {
        cartContainer.addEventListener('submit', function (e) {
            const form = e.target;

            // Atualizar quantidade (+ ou -)
            if (form.action.includes('/carrinho/update')) {
                e.preventDefault();

                const formData = new FormData(form);
                const cartItem = form.closest('.cart-item');
                if (cartItem) cartItem.style.opacity = '0.5';

                fetch('/carrinho/update', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                if (cartItem) cartItem.style.opacity = '1';
                                showToast('Erro ao atualizar quantidade', 'error');
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Erro:', err);
                        if (cartItem) cartItem.style.opacity = '1';
                        showToast('Erro de conexão', 'error');
                    });
            }

            // Remover item (não cupom)
            else if (form.action.includes('/carrinho/remove') && !form.action.includes('cupom')) {
                e.preventDefault();

                if (!confirm('Tem certeza que deseja remover este item?')) return;

                const formData = new FormData(form);
                const cartItem = form.closest('.cart-item');

                if (cartItem) {
                    cartItem.style.opacity = '0.5';
                    cartItem.style.transition = 'opacity 0.3s, transform 0.3s';
                }

                fetch('/carrinho/remove', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (cartItem) {
                                cartItem.style.transform = 'translateX(-100%)';
                                setTimeout(() => location.reload(), 300);
                            } else {
                                location.reload();
                            }
                        } else {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                if (cartItem) cartItem.style.opacity = '1';
                                showToast('Erro ao remover item', 'error');
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Erro:', err);
                        if (cartItem) cartItem.style.opacity = '1';
                        showToast('Erro de conexão', 'error');
                    });
            }
        });
    }

    // Função auxiliar para atualizar contador no header
    function updateCartCount(count) {
        const badges = document.querySelectorAll('.cart-count-badge');
        badges.forEach(badge => badge.textContent = count);

        const cartTitle = document.querySelector('.cart-header h1');
        if (cartTitle) cartTitle.textContent = `Meu Carrinho (${count} itens)`;
    }

    // Função de Toast (Notificação flutuante)
    function showToast(message, type = 'success') {
        const existingToast = document.querySelector('.custom-toast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.className = `custom-toast toast-${type}`;
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span>${type === 'success' ? '✓' : '!'}</span>
                <span>${message}</span>
            </div>
        `;

        Object.assign(toast.style, {
            position: 'fixed',
            bottom: '20px',
            right: '20px',
            padding: '12px 24px',
            background: type === 'success' ? '#4ade80' : '#f87171',
            color: '#1a1a2e',
            borderRadius: '8px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
            zIndex: '9999',
            fontWeight: '600',
            fontSize: '0.95rem',
            transform: 'translateY(100px)',
            transition: 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
            display: 'flex',
            alignItems: 'center'
        });

        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            toast.style.transform = 'translateY(0)';
        });

        setTimeout(() => {
            toast.style.transform = 'translateY(100px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
