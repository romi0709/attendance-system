// assets/js/admin.js
document.addEventListener('DOMContentLoaded', function () {

    // Délégué: intercept links with ?delete= to confirm
    document.body.addEventListener('click', function (e) {
        const a = e.target.closest('a');
        if (!a) return;
        const href = a.getAttribute('href') || '';
        if (href.includes('?delete=')) {
            if (!confirm('Êtes-vous sûr(e) de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        }
    });

    // Optional: prevent double submit on forms with button[name=add] or add/import
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (ev) {
            const btn = form.querySelector('button[type=submit]');
            if (btn) {
                btn.disabled = true;
                setTimeout(() => btn.disabled = false, 2000); // réactive après 2s
            }
        });
    });

});
