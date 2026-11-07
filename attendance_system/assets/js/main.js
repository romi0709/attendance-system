// ===============================
// main.js (VERSION JQUERY)
// ===============================

// ---- Alerte simple (tu peux remplacer par un modal plus tard)
function showAlert(message, type = 'info') {
    // type : info | success | error
    // Remplace alert() avec un joli composant plus tard
    alert(message);
}

// ---- Confirmer une action (supprimer, logout, etc.)
function confirmAction(message) {
    return confirm(message);
}

// ---- Convertir FormData → Object (utile pour debug ou JSON)
function formDataToObject(fd) {
    let obj = {};
    for (let pair of fd.entries()) {
        obj[pair[0]] = pair[1];
    }
    return obj;
}

// ---- Helper Fetch POST JSON (avec jQuery)
async function postJSON(url, data) {
    try {
        const response = await $.ajax({
            url: url,
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify(data)
        });

        return response;

    } catch (error) {
        console.error("POST JSON ERROR :", error);
        return { success: false, error: "Network or server error" };
    }
}

// ===============================
//       UTILITÉS DOM JQUERY
// ===============================

// ---- Attendre que la page soit chargée
$(document).ready(function () {

    console.log("jQuery main.js loaded ✔");

    // Exemple : gérer un bouton logout
    $(document).on("click", "#logoutBtn", function () {
        if (confirmAction("Are you sure you want to logout?")) {
            localStorage.removeItem("user");
            window.location.href = "login.html";
        }
    });

    // Exemple : gérer un formulaire
    $("form.ajaxForm").on("submit", async function (e) {
        e.preventDefault();

        let fd = new FormData(this);
        let data = formDataToObject(fd);

        console.log("🔍 Form Data:", data);

        let url = $(this).attr("action");

        let result = await postJSON(url, data);

        if (result.success) {
            showAlert("Success!", "success");
        } else {
            showAlert("Error: " + (result.error || "Unknown error"), "error");
        }
    });

});
