
//замінює символи '<' та '>' на їх HTML-еквіваленти '&lt;' та '&gt;'
function sanitizeInput(input) {
    return input.replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

//перевіряє, що є принаймні один символ перед символом '@', 
//є принаймні один символ після '@' і перед '.' і є принаймні один символ після '.'.
function validateEmail(email) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
}

// перевіряє, що
// Пароль містить принаймні одну малу літеру (a-z).
// Містить принаймні одну велику літеру (A-Z).
// Містить принаймні одну цифру (0-9).
// Містить принаймні один спеціальний символ (наприклад, !@#$%^&*).
// Довжина пароля становить не менше 8 символів.

function validatePasswordStrength(password) {
    const strongPasswordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    return strongPasswordPattern.test(password);
}


// Функція highlightInvalidInput додає клас 'is-invalid' до input-поля
// та створює елемент 'invalid-feedback' з відповідним повідомленням

function highlightInvalidInput(input, message) {
    input.classList.add('is-invalid');
    let feedbackElement = input.parentNode.querySelector('.invalid-feedback');
    if (!feedbackElement) {
        feedbackElement = document.createElement('div');
        feedbackElement.className = 'invalid-feedback';
        input.parentNode.append(feedbackElement);
    }
    feedbackElement.textContent = message;
}

// Функція removeInvalidHighlight видаляє клас 'is-invalid' з input-поля
// та очищує текст елемента 'invalid-feedback'

function removeInvalidHighlight(input) {
    input.classList.remove('is-invalid');
    let feedbackElement = input.parentNode.querySelector('.invalid-feedback');
    if (feedbackElement) {
        feedbackElement.textContent = '';
    }
}

// Функція handleValidationError перевіряє валідність введених даних
// та відповідно додає або видаляє клас 'is-invalid' з input-поля

function handleValidationError(input, isValid, errorMessage) {
    if (!isValid) {
        highlightInvalidInput(input, errorMessage);
        return false;
    }
    removeInvalidHighlight(input);
    return true;
}

// Функція showErrorModal відображає модальне вікно з помилкою
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

// Функція showSuccessModal відображає модальне вікно з успішним входом
// та перенаправляє користувача на відповідну сторінку (students.html або teachers.html)
function showSuccessModal(message, role) {
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
        if (role === 'student') {
            window.location.href = '/html/students.html';
        } else {
            window.location.href = '/html/teachers.html';
        }
    }, { once: true });
}

// Обробник події 'DOMContentLoaded' ініціалізує форму входу
// та додає обробник події 'submit' для перевірки введених даних
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form.needs-validation');
    if (!form) {
        console.error("Form not found in the document.");
        return;
    }

    const emailInput = form.elements['email'];
    const passwordInput = form.elements['password'];

    if (!emailInput || !passwordInput) {
        console.error("Form elements not found in the document.");
        return;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();

        const isValidEmail = handleValidationError(
            emailInput,
            validateEmail(email),
            'Введіть дійсну електронну адресу.'
        );

        const isValidPassword = handleValidationError(
            passwordInput,
            validatePasswordStrength(password),
            'Пароль повинен містити мінімум 8 символів, включати великі та малі літери, цифри та спеціальні символи.'
        );

        if (isValidEmail && isValidPassword) {
            loginUser({
                email: email,
                password: password
            });
        }
    });
});

// Функція loginUser відправляє дані користувача на сервер
// та обробляє відповідь (успішний вхід або помилка)

function loginUser(userData) {
    fetch('/php/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
    })
        .then(response => response.json())
        .then(data => {
            console.log('Response data:', data);
            if (data.error) {
                showErrorModal(data.error);
            } else if (data.success) {
                showSuccessModal('Користувача успішно ввійшов!', data.role);
            } else {
                console.error('Unexpected response structure:', data);
            }
        })
        
        .catch(error => {
            console.error('Error during login operation:', error);
            showErrorModal('Сталася помилка при вході в систему.');
        });
}