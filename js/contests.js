function sanitizeInput(input) {
    return input.replace(/</g, "<").replace(/>/g, ">");
}

function getSelectedClasses() {
    const classCheckboxes = document.querySelectorAll('input[type="checkbox"][name="class[]"]:checked');
    let selectedClasses = [];
    classCheckboxes.forEach(checkbox => {
        selectedClasses.push(checkbox.value);
    });
    return selectedClasses;
}

function showSuccessModal(message) {
    const successModal = document.getElementById('successModal');
    const successModalBody = document.getElementById('successModalBody');

    if (!successModal || !successModalBody) {
        console.error('Success modal elements not found in the document.');
        return;
    }

    successModalBody.textContent = message;

    const successModalInstance = new bootstrap.Modal(successModal, {
        backdrop: 'static'
    });
    successModalInstance.show();

    successModal.addEventListener('hidden.bs.modal', function onModalHidden() {
        window.location.href = '/html/teachers.html';
    }, { once: true });
}

function showErrorModal(message) {
    const errorModal = document.getElementById('errorModal');
    const errorModalBody = document.getElementById('errorModalBody');

    if (!errorModal || !errorModalBody) {
        console.error('Error modal elements not found in the document.');
        return;
    }

    errorModalBody.textContent = message;

    const errorModalInstance = new bootstrap.Modal(errorModal, {
        backdrop: 'static'
    });
    errorModalInstance.show();

    return new Promise((resolve) => {
        errorModal.addEventListener('hidden.bs.modal', function onModalHidden() {
            errorModal.removeEventListener('hidden.bs.modal', onModalHidden);
            resolve();
        }, { once: true });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form.needs-validation');

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const name = sanitizeInput(form.elements['comp_name'].value.trim());
        const website = sanitizeInput(form.elements['comp_link'].value.trim());
        const startdate = sanitizeInput(form.elements['start_date'].value.trim());
        const deadline = sanitizeInput(form.elements['deadline'].value.trim());
        const description = sanitizeInput(form.elements['comp_desc'].value.trim());
        const classesAllowed = getSelectedClasses();
        const competitionData = {
            name: name,
            website: website,
            startdate: startdate,
            deadline: deadline,
            description: description,
            classesAllowed: classesAllowed
        };

        addCompetition(competitionData);
    });

    function addCompetition(competitionData) {
        fetch('/php/addCompetition.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(competitionData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessModal('Конкурс успішно додано.');
                } else {
                    showErrorModal(data.error);
                }
            })
            .catch(error => {
                showErrorModal('Помилка при додаванні конкурсу.');
            });
    }
});
