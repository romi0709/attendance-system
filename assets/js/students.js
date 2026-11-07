// assets/js/students.js
document.addEventListener('DOMContentLoaded', function () {

    // Validation simple pour le formulaire d'ajout d'étudiant
    const addForm = document.querySelector('form[method="POST"]:not([enctype])'); // approx
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            const email = addForm.querySelector('input[type="email"]');
            const name = addForm.querySelector('input[name="fullname"]');
            if (email && name) {
                if (!name.value.trim() || !email.value.trim()) {
                    e.preventDefault();
                    alert('Nom et email sont requis.');
                    return;
                }
            }
        });
    }

    // Formulaire d'import Excel (enctype multipart)
    const importForm = document.querySelector('form[enctype="multipart/form-data"]');
    if (importForm) {
        importForm.addEventListener('submit', function (e) {
            const fileInput = importForm.querySelector('input[type="file"][name="excel"]');
            if (fileInput && fileInput.files.length === 0) {
                e.preventDefault();
                alert('Veuillez choisir un fichier .xlsx avant d\'importer.');
                return;
            }

            // Optionnel : vérifier extension
            const f = fileInput.files[0];
            if (f && !f.name.match(/\.xlsx$/i)) {
                e.preventDefault();
                alert('Seuls les fichiers .xlsx sont acceptés.');
                return;
            }

            // Afficher message (l'import se fait côté serveur)
            const btn = importForm.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Import en cours...';
            }
        });
    }

});
