let cardView = true; //Змінна, яка визначає, чи відображати змагання у вигляді карток

//слухач події, який виконується після завантаження документа DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
    
    //змагання при завантаженні сторінки
    loadCompetitions();
    
    //слухач події на кнопку зміни виду
    document.getElementById('changeViewButton').addEventListener('click', changeView);

    //слухач події на кнопку підтвердження видалення
    document.getElementById('confirmDeleteButton').addEventListener('click', deleteCompetition);

    //слухач події, щоб застосувати кнопку фільтрації
    document.getElementById('applyFilterButton').addEventListener('click', loadCompetitions);
});

//функція для зміни виду відображення змагань
function changeView() {
    cardView = !cardView; //зміна на протилежне значення
    loadCompetitions(); //перезвантажити змагання з новим видом
}

//Функція для отримання значення кукі за його ім'ям
function getCookie(name) {
    const value = `; ${document.cookie}`; //Додати крапку з комою для коректного парсингу кукі
    const parts = value.split(`; ${name}=`); //Розділити кукі за ім'ям
    //знайдено => повертаємо його значення
    if (parts.length === 2) return parts.pop().split(';').shift(); 
}

//Функція для завантаження змагань з БД
function loadCompetitions() {
    //Отримати роль користувача з кукі (учень/вчитель)
    const userRole = getCookie('role');

    //Отримати вхідні дані для фільтрування та сортування
    //Сортування за замовчуванням - за назвою
    const sortBy = document.getElementById('sortSelect').value || 'name';

    //Порядок сортування за замовчуванням - зростання
    const sortOrder = document.getElementById('sortOrderSelect').value || 'asc';

    //Фільтр дати початку
    const startDateFilter = document.getElementById('startDateFilter').value;

    //Фільтр дати закінчення
    const endDateFilter = document.getElementById('endDateFilter').value;

    //Конструювання параметрів запиту для отримання даних
    const params = new URLSearchParams({
        sortBy,
        sortOrder,
        startDateFilter,
        endDateFilter
    });

    //Виконуємо HTTP запит до сервера для отримання змагань
    fetch(`http://localhost/php/getCompetitions.php?${params.toString()}`)
        .then(response => response.json()) //відповідь у формат JSON
        .then(competitions => {
            //Отримати контейнер для змагань
            const competitionsContainer = document.querySelector('#competitions-container');
            competitionsContainer.innerHTML = ''; //Очистка, контейнера перед відображенням нових даних

            //Якщо режим перегляду - таблиця, створюємо таблицю для відображення змагань
            if (!cardView) {
                const table = document.createElement('table'); //Створюємо новий елемент таблиці
                table.className = 'text-dark table table-bordered table-competitions'; //класи для стилізації бутстрап
                
                const headerRow = document.createElement('tr'); //рядок заголовків таблиці 

                //Масив об'єктів, для опису заголовків таблиці: ID, Назва, Опис, Дата Початку, 
                //Дата Кінця, Статус (завершено чи ні), Дії (для вчителів)
                const headers = [
                    { name: 'ID', key: 'id' },
                    { name: 'Назва', key: 'name' },
                    { name: 'Опис', key: 'description' },
                    { name: 'Дата початку', key: 'startdate' },
                    { name: 'Дата кінця', key: 'deadline' },
                    { name: 'Статус', key: 'status' },
                    { name: 'Дії', key: null }  //Стовпець Дії не потребує сортування
                ];

                //Створити заголовки таблиці на основі масиву headers
                headers.forEach(header => {
                    const th = document.createElement('th'); //Створити новий елемент заголовка стовпця
                    th.textContent = header.name; //Встановити текст заголовка

                    //Додати приймач подій кліку лише для відсортованих стовпців
                    if (header.key) {
                        //Змінити курсор на вказівник при наведенні на заголовок стовпця
                        th.style.cursor = 'pointer';

                        //Викликаємо функцію для перемикання сортування при кліку на заголовок стовпця
                        th.addEventListener('click', () => {
                            toggleSort(header.key);
                        });
                    }

                    headerRow.appendChild(th);
                });

                table.appendChild(headerRow);
                competitionsContainer.appendChild(table);
            }
            //Для кожного змагання створюємо елемент і додаємо його до контейнера в залежності від виду перегляду
            competitions.forEach(comp => {
            const competitionElement = createCompetitionElement(comp, userRole); //Створюємо елемент змагання

                if (cardView) {
                    //Додаємо елемент до контейнера у вигляді картки
                    competitionsContainer.appendChild(competitionElement);
                } else {
                      //Додаємо елемент до таблиці
                    document.querySelector('#competitions-container table').appendChild(competitionElement);
                }
            });
        })
        .catch(error => {
            console.error('Не вдалося отримати конкурси:', error);
        });
}

// Функція для перемикання порядку сортування на основі вибраного стовпця
function toggleSort(column) {
    const sortOrderSelect = document.getElementById('sortOrderSelect'); //Отримуємо селектор порядку сортування
    const sortSelect = document.getElementById('sortSelect'); //Отримуємо селектор стовпця для сортування

    //Перемикаємо порядок сортування, якщо натиснуто один і той самий стовпець, інакше за замовчуванням - за зростанням
    if (sortSelect.value === column) {
        //Перемикаємо між "asc" і "desc"
        sortOrderSelect.value = sortOrderSelect.value === 'asc' ? 'desc' : 'asc';
    } else {
        //Встановлюємо новий стовпець для сортування
        sortSelect.value = column;        
        sortOrderSelect.value = 'asc';
        //Скидаємо порядок сортування на "asc"
    }

    //Перезавантажуємо змагання з новим сортуванням та фільтрами
    loadCompetitions();
}

// Функція createCompetitionElement створює елемент змагання (картку або рядок таблиці) 
//на основі наданих даних про змагання (`comp`) і ролі користувача (`userRole`).
function createCompetitionElement(comp, userRole) {
    // Перевіряє, чи змагання завершилося
    const isExpired = comp.status === 'expired';

    // Обробляє вигляд картки
    if (cardView) {
        //Створює посилання на сайт змагання
        const anchor = document.createElement('a');
        anchor.setAttribute('data-id', comp.id);
        anchor.href = comp.website.startsWith('http://') || comp.website.startsWith('https://') ? comp.website : `http://${comp.website}`;
        anchor.className = 'text-decoration-none';
        anchor.target = '_blank';

        // Створює контейнер для картки
        const colDiv = document.createElement('div');
        colDiv.className = 'col';

        const cardDiv = document.createElement('div');
        cardDiv.className = 'card card-cover h-100 overflow-hidden text-bg-light rounded-4 shadow-lg';

        const contentDiv = document.createElement('div');
        contentDiv.className = 'd-flex flex-column h-100 p-5 pb-3 text-dark text-shadow-1';

        // Заголовок змагання
        const h3Header = document.createElement('h3');
        h3Header.className = 'pt-5 mt-5 mb-4 display-6 lh-1 fw-bold';
        h3Header.textContent = comp.name;

        // Створює список для відображення термінів змагання
        const ul = document.createElement('ul');
        ul.className = 'd-flex list-unstyled mt-auto';

        // Дата закінчення змагання
        const deadlineLi = document.createElement('li');
        deadlineLi.className = 'd-flex align-items-center me-3';
        const deadlineSmall = document.createElement('small');
        deadlineSmall.textContent = `До ${comp.deadline}`;
        deadlineLi.appendChild(deadlineSmall);
        ul.appendChild(deadlineLi);

        // Дата початку змагання
        const startDateLi = document.createElement('li');
        startDateLi.className = 'd-flex align-items-center me-3';
        const startDateSmall = document.createElement('small');
        startDateSmall.textContent = `Початок: ${comp.startdate}`;
        startDateLi.appendChild(startDateSmall);
        ul.insertBefore(startDateLi, deadlineLi); // Додає дату початку перед терміном

        // Класи, для яких дозволено змагання
        const classesLi = document.createElement('li');
        classesLi.className = 'd-flex align-items-center';
        const classesSmall = document.createElement('small');
        classesSmall.textContent = `Для класів: ${comp.classes_allowed}`;
        classesLi.appendChild(classesSmall);
        ul.appendChild(classesLi);

        // Опис змагання
        const descDiv = document.createElement('div');
        descDiv.className = 'mb-4 text-dark';
        descDiv.textContent = comp.description;

        // Додає заголовок, опис та список до контенту картки
        contentDiv.appendChild(h3Header);
        contentDiv.appendChild(descDiv);
        contentDiv.appendChild(ul);

        // Кнопка редагування змагання
        const editButton = document.createElement('button');
        editButton.className = 'btn btn-sm btn-warning mt-2';
        editButton.textContent = 'Редагувати';

        // Додає обробник подій для кнопки редагування
        editButton.addEventListener('click', function (event) {
            event.stopPropagation(); // Зупиняє подію від подальшого поширення
            event.preventDefault();  // Запобігає стандартній поведінці кнопки
            toggleDeleteButton(deleteButton); // Викликає функцію для перемикання видимості кнопки видалення
        });

        // Перевірити, чи роль користувача є 'teacher'
        if (userRole === 'teacher') {
            contentDiv.appendChild(editButton); // Додає кнопку редагування, якщо користувач - вчитель
        }

        // Кнопка видалення змагання
        const deleteButton = document.createElement('button');
        deleteButton.className = 'btn btn-sm btn-danger mt-2';
        deleteButton.textContent = 'Видалити змагання';

        deleteButton.style.display = 'none'; // Спочатку кнопка прихована

        // Додає обробник подій для кнопки видалення
        deleteButton.addEventListener('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            confirmDelete(comp.id); // Викликає функцію підтвердження видалення змагання
        });

        // Перевірити, чи роль користувача є 'teacher'
        if (userRole === 'teacher') {
            contentDiv.appendChild(deleteButton); // Додає кнопку видалення, якщо користувач - вчитель
        }

        cardDiv.appendChild(contentDiv); // Додає контент до картки
        colDiv.appendChild(cardDiv);      // Додає картку до стовпця
        anchor.appendChild(colDiv);         // Додає стовпець до посилання

        return anchor;  // Повертає створене посилання як результат функції
    } else {
        // Табличне представлення
        const tr = document.createElement('tr');  // Створює новий рядок таблиці

        if (isExpired) {
            tr.classList.add('table-danger'); // Підсвічує рядки, що завершилися
        }

        // Створення комірок таблиці для відображення даних про змагання
        const idTd = document.createElement('td');
        idTd.textContent = comp.id;

        const nameTd = document.createElement('td');
        const nameLink = document.createElement('a');  // Створює посилання на назву змагання
        nameLink.href = comp.website.startsWith('http://') || comp.website.startsWith('https://') ? comp.website : `http://${comp.website}`;
        nameLink.textContent = comp.name;
        nameLink.className = 'text-decoration-none';
        nameLink.target = '_blank';  // Відкриває посилання в новій вкладці
        nameTd.appendChild(nameLink);

        const descriptionTd = document.createElement('td');
        descriptionTd.textContent = comp.description;

        const startDateTd = document.createElement('td');
        startDateTd.textContent = comp.startdate;

        const deadlineTd = document.createElement('td');
        deadlineTd.textContent = comp.deadline;

        // Стовпчик стану змагання (активний/закінчився)
        const statusTd = document.createElement('td');
        statusTd.textContent = isExpired ? 'Закінчився' : 'Активний';

        // Стовпчик дій (редагування/видалення)
        const actionTd = document.createElement('td');

        if (userRole === 'teacher') {  // Якщо користувач - вчитель, додаємо кнопки дій
            const editButton = document.createElement('button');
            editButton.className = 'btn btn-sm btn-warning me-2';
            editButton.textContent = 'Редагувати';

            editButton.addEventListener('click', function () {
                toggleDeleteButton(deleteButton);  // Викликає функцію для перемикання видимості кнопки видалення
            });
            actionTd.appendChild(editButton);  // Додає кнопку редагування

            const deleteButton = document.createElement('button');
            deleteButton.className = 'btn btn-sm btn-danger';
            deleteButton.textContent = 'Видалити змагання';
            deleteButton.style.display = 'none';  // Спочатку кнопка прихована

            deleteButton.addEventListener('click', function () {
                confirmDelete(comp.id);  // Викликає функцію підтвердження видалення змагання
            });
            actionTd.appendChild(deleteButton);  // Додає кнопку видалення до стовпчика дій
        }

        // Додаємо кожну комірку до рядка в правильному порядку
        tr.appendChild(idTd);
        tr.appendChild(nameTd);
        tr.appendChild(descriptionTd);
        tr.appendChild(startDateTd);
        tr.appendChild(deadlineTd);
        tr.appendChild(statusTd);
        tr.appendChild(actionTd);

        return tr;  // Повертає створений рядок таблиці як результат функції
    }
}

// Функція toggleDeleteButton перемикає видимість кнопки видалення.
function toggleDeleteButton(deleteButton) {
    if (deleteButton.style.display === 'none') {
        deleteButton.style.display = 'inline-block';  // Показує кнопку, якщо вона прихована
    } else {
        deleteButton.style.display = 'none';  // Приховує кнопку, якщо вона показана
    }
}

// Функція confirmDelete відкриває модальне вікно підтвердження видалення.
function confirmDelete(compId) {
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    confirmDeleteButton.setAttribute('data-id', compId);  // Зберігає ID змагання для видалення

    const deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    deleteConfirmationModal.show();  // Відкриває модальне вікно підтвердження видалення
}

// Функція deleteCompetition виконує запит на видалення змагання за його ID.
function deleteCompetition() {
    const compId = document.getElementById('confirmDeleteButton').getAttribute('data-id');  // Отримує ID змагання

    fetch('http://localhost/php/deleteCompetition.php', {  // Виконує запит на сервер для видалення змагання
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${compId}`,  // Передає ID у тілі запиту
    })
        .then(response => {
            if (response.ok) {
                loadCompetitions();  // Якщо запит успішний, перезавантажує список змагань
            } else {
                console.error('Failed to delete competition');  // Виводить помилку у консолі, якщо не вдалося видалити змагання
            }
        })
        .catch(error => {
            console.error('Error:', error);  // Виводить помилку у консолі при виникненні помилки запиту
        });
}
