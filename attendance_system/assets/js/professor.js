// assets/js/professor.js
document.addEventListener('DOMContentLoaded', function () {

    // Exemple : boutons "Présent" / "Absent" ayant data attributes
    // <button class="mark" data-session="1" data-student="5" data-status="present">Présent</button>
    document.body.addEventListener('click', async function (e) {
        const btn = e.target.closest('.mark');
        if (!btn) return;
        const sessionId = btn.dataset.session;
        const studentId = btn.dataset.student;
        const status = btn.dataset.status; // 'present' ou 'absent'

        if (!sessionId || !studentId || !status) return;

        // Optionnel confirmation
        if (!confirm(`Marquer ${status} pour cet étudiant ?`)) return;

        // Envoie via fetch à un endpoint PHP (à créer) : professor/mark.php
        try {
            const res = await fetch('/attendance_system/professor/mark.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ session_id: sessionId, student_id: studentId, status: status })
            });
            const json = await res.json();
            if (json.success) {
                // feedback visuel : changer classe, texte, etc.
                btn.classList.add('done');
                btn.innerText = (status === 'present') ? 'Présent ✓' : 'Absent ✗';
            } else {
                alert('Erreur: ' + (json.message || 'Impossible d\'enregistrer'));
            }
        } catch (err) {
            console.error(err);
            alert('Erreur réseau ou serveur.');
        }
    });

});
