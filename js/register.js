function sanitizeInput(input) {
  return input.replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function validateEmail(email) {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailPattern.test(email);
}

function validateUkrainianName(name) {
  const ukrainianNamePattern = /^[А-ЩЬЮЯЄІЇҐа-щьюяєіїґ]{4,20}$/;
  return ukrainianNamePattern.test(name);
}

function validatePasswordStrength(password) {
  const strongPasswordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
  return strongPasswordPattern.test(password);
}

//повідомлення про успіх реєстрації
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
    window.location.href = '/html/cover.html';
  }, { once: true });
}

//повідомлення про помилку в реєстрації
function showErrorModal(message) {
  const errorModal = document.getElementById('errorModal');
  const errorModalBody = document.getElementById('errorModalBody');

  if (!errorModal || !errorModalBody) {
    console.error('Error modal elements not found in the document.');
    return;
  }

  errorModalBody.textContent = message;

  //створення нового модал елемента за допомогою JavaScript Bootstrap
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

function removeInvalidHighlight(input) {
  input.classList.remove('is-invalid');
  let feedbackElement = input.parentNode.querySelector('.invalid-feedback');
  if (feedbackElement) {
    feedbackElement.textContent = '';
  }
}

//Допоміжна функція для обробки валідації вхідних даних
function handleValidationError(input, isValid, errorMessage) {
  if (!isValid) {
    highlightInvalidInput(input, errorMessage);
    return false;
  }
  removeInvalidHighlight(input);
  return true;
}


document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');

  const formData = {
    email: form.elements['email'],
    password: form.elements['password'],
    lastName: form.elements['last_name'],
    firstName: form.elements['first_name'],
    role: form.elements['role']
  };

  function getClassInput() {
    const classFieldContainer = document.getElementById('classField');
    const isClassFieldVisible = classFieldContainer && classFieldContainer.style.display !== 'none';
    return isClassFieldVisible ? form.elements['class'] : null;
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    formData.class = getClassInput();

    const classInput = getClassInput();
    const lastName = sanitizeInput(formData.lastName.value.trim());
    const firstName = sanitizeInput(formData.firstName.value.trim());
    const role = formData.role.value;

    //Прізвище
    let validLastName = handleValidationError(
      formData.lastName,
      validateUkrainianName(lastName),
      'Прізвище має містити тільки українські літери та бути довжиною від 4 до 20 символів.'
    );

    //Ім'я
    let validFirstName = handleValidationError(
      formData.firstName,
      validateUkrainianName(firstName),
      'Ім\'я має містити тільки українські літери та бути довжиною від 4 до 20 символів.'
    );

    //клас
    let validClass = true;
    if (formData.role.value === 'student' && formData.class) {
      validClass = handleValidationError(
        formData.class,
        ['8', '9', '10', '11'].includes(formData.class.value),
        'Будь ласка, оберіть клас.'
      );
    }

    //емайл
    let isValidEmail = handleValidationError(
      formData.email,
      validateEmail(formData.email.value.trim()),
      'Введіть дійсну електронну адресу.'
    );

    //пароль
    let isValidPassword = handleValidationError(
      formData.password,
      validatePasswordStrength(formData.password.value.trim()),
      'Пароль повинен містити мінімум 8 символів, включати великі та малі літери, цифри та спеціальні символи.'
    );

    //роль
    let validRole = handleValidationError(
      formData.role,
      formData.role.value !== '',
      'Будь ласка, оберіть роль.'
    );

    //перевірка усіх валідацій
    if (validLastName && validFirstName && validRole &&
      (formData.role.value !== 'student' || validClass) &&
      isValidEmail && isValidPassword) {
      const userData = {
        email: formData.email.value.trim(),
        password: formData.password.value.trim(),
        lastName: lastName,
        firstName: firstName,
        role: formData.role.value,
        class: formData.role.value === 'student' ? formData.class.value : null
      };

      //Функція для обробки реєстрації користувачів
      registerUser(userData);
    }
  });

  function registerUser(userData) {
    fetch('/php/register.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(userData)  //Перетворення JavaScript-об'єкта в JSON-рядок
    })
      .then(response => response.json())  //Розбір відповіді JSON
      .then(data => {
        if (data.success) {
          console.log(data.success);
          showSuccessModal('Користувача успішно зареєстровано.');
          //Перенаправити на нову сторінку або виконати іншу дію
        } else {
          console.error(data.error);
          showErrorModal(data.error);
        }
      })
      .catch(error => {
        //Обробка помилок самої вибірки
        console.error('Error occurred during fetch operation:', error);
        showErrorModal('Помилка при спробі реєстрації.');
      });
  }
});