document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form.needs-validation');
    
    if (!form) {
        console.error("Form not found in the document.");
        return;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const classCode = document.getElementById('floatingClassCode').value.trim();
        const classPassword = document.getElementById('floatingClassPassword').value.trim();

        if (!classCode || !classPassword) {
            showErrorModal('Будь ласка, заповніть всі необхідні поля.');
            return;
        }

        //AJAX-виклик для перевірки коду класу та паролю
        fetch('/php/enterClass.php', {
            method: 'POST', //метод запиту POST
            headers: {
                'Content-Type': 'application/json' //тип контенту як JSON
            },
            body: JSON.stringify({ //Перетворюємо дані в формат JSON
                class_code: classCode, //Код класу
                class_password: classPassword //Пароль класу
            })
        })
        .then(response => response.json()) //Обробляємо відповідь, перетворюючи її в JSON
        .then(data => {
            if (data.success) {
                showSuccessModal('Ви успішно вступили до класу! ');
                window.location.href = '/html/students.html?entered=true';
            } else {
                showErrorModal(data.error || 'Не вдалося увійти до класу. Спробуйте ще раз.');
            }
        })
        .catch(error => {
            console.error('Error during class entry operation:', error);
            showErrorModal('Виникла помилка. Будь ласка, спробуйте ще раз.');
        });
    });


function showErrorModal(message) {
    const errorModal = document.getElementById('errorModal');
    const errorModalBody = document.getElementById('errorModalBody');
    
    errorModalBody.textContent = message;
    const errorModalInstance = new bootstrap.Modal(errorModal);
    errorModalInstance.show();
}

function showSuccessModal(message) {
    const successModal = document.getElementById('successModal');
    const successModalBody = document.getElementById('successModalBody');
    
    successModalBody.textContent = message;
    const successModalInstance = new bootstrap.Modal(successModal);
    successModalInstance.show();
}

});