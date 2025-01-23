let cardView = true;

document.addEventListener('DOMContentLoaded', function () {
    
    loadCompetitions();
    document.getElementById('changeViewButton').addEventListener('click', changeView);
    document.getElementById('confirmDeleteButton').addEventListener('click', deleteCompetition);
    document.getElementById('applyFilterButton').addEventListener('click', loadCompetitions);
    document.getElementById('resetFilterButton').addEventListener('click', resetFilters);
});

function changeView() {
    cardView = !cardView;
    loadCompetitions();
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift(); 
}



function loadCompetitions() {
    const userRole = getCookie('role');
    const sortBy = document.getElementById('sortSelect').value || 'name';
    const sortOrder = document.getElementById('sortOrderSelect').value || 'asc';

    const startDateFilter = document.getElementById('startDateFilter').value;

    const endDateFilter = document.getElementById('endDateFilter').value;

    const params = new URLSearchParams({
        sortBy,
        sortOrder,
        startDateFilter,
        endDateFilter
    });

    fetch(`http://localhost/php/getCompetitions.php?${params.toString()}`)
        .then(response => response.json())
        .then(competitions => {
            const competitionsContainer = document.querySelector('#competitions-container');
            competitionsContainer.innerHTML = '';

            if (!cardView) {
                const table = document.createElement('table');
                table.className = 'text-dark table table-bordered table-competitions';
                
                const headerRow = document.createElement('tr'); 

                const headers = [
                    { name: 'ID', key: 'id' },
                    { name: 'Назва', key: 'name' },
                    { name: 'Опис', key: 'description' },
                    { name: 'Дата початку', key: 'startdate' },
                    { name: 'Дата кінця', key: 'deadline' },
                    { name: 'Статус', key: 'status' },
                    { name: 'Дії', key: null }
                ];

                headers.forEach(header => {
                    const th = document.createElement('th');
                    th.textContent = header.name;

                    if (header.key) {
                        th.style.cursor = 'pointer';

                        th.addEventListener('click', () => {
                            toggleSort(header.key);
                        });
                    }

                    headerRow.appendChild(th);
                });

                table.appendChild(headerRow);
                competitionsContainer.appendChild(table);
            }
            competitions.forEach(comp => {
            const competitionElement = createCompetitionElement(comp, userRole);

                if (cardView) {
                    competitionsContainer.appendChild(competitionElement);
                } else {
                    document.querySelector('#competitions-container table').appendChild(competitionElement);
                }
            });
        })
        .catch(error => {
            console.error('Не вдалося отримати конкурси:', error);
        });
}

function toggleSort(column) {
    const sortOrderSelect = document.getElementById('sortOrderSelect');
    const sortSelect = document.getElementById('sortSelect');

    if (sortSelect.value === column) {
        sortOrderSelect.value = sortOrderSelect.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortSelect.value = column;        
        sortOrderSelect.value = 'asc';
    }

    loadCompetitions();
}

function createCompetitionElement(comp, userRole) {
    const isExpired = comp.status === 'expired';

    if (cardView) {
        const anchor = document.createElement('a');
        anchor.setAttribute('data-id', comp.id);
        anchor.href = comp.website.startsWith('http://') || comp.website.startsWith('https://') ? comp.website : `http://${comp.website}`;
        anchor.className = 'text-decoration-none';
        anchor.target = '_blank';

        const colDiv = document.createElement('div');
        colDiv.className = 'col';

        const cardDiv = document.createElement('div');
        cardDiv.className = 'card card-cover h-100 overflow-hidden text-bg-light rounded-4 shadow-lg';

        const contentDiv = document.createElement('div');
        contentDiv.className = 'd-flex flex-column h-100 p-5 pb-3 text-dark text-shadow-1';

        const h3Header = document.createElement('h3');
        h3Header.className = 'pt-5 mt-5 mb-4 display-6 lh-1 fw-bold';
        h3Header.textContent = comp.name;

        const ul = document.createElement('ul');
        ul.className = 'd-flex list-unstyled mt-auto';

        const deadlineLi = document.createElement('li');
        deadlineLi.className = 'd-flex align-items-center me-3';
        const deadlineSmall = document.createElement('small');
        deadlineSmall.textContent = `До ${comp.deadline}`;
        deadlineLi.appendChild(deadlineSmall);
        ul.appendChild(deadlineLi);

        const startDateLi = document.createElement('li');
        startDateLi.className = 'd-flex align-items-center me-3';
        const startDateSmall = document.createElement('small');
        startDateSmall.textContent = `Початок: ${comp.startdate}`;
        startDateLi.appendChild(startDateSmall);
        ul.insertBefore(startDateLi, deadlineLi);

        const classesLi = document.createElement('li');
        classesLi.className = 'd-flex align-items-center';
        const classesSmall = document.createElement('small');
        classesSmall.textContent = `Для класів: ${comp.classes_allowed}`;
        classesLi.appendChild(classesSmall);
        ul.appendChild(classesLi);

        const descDiv = document.createElement('div');
        descDiv.className = 'mb-4 text-dark';
        descDiv.textContent = comp.description;

        contentDiv.appendChild(h3Header);
        contentDiv.appendChild(descDiv);
        contentDiv.appendChild(ul);

        const editButton = document.createElement('button');
        editButton.className = 'btn btn-sm btn-warning mt-2';
        editButton.textContent = 'Редагувати';

        editButton.addEventListener('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            toggleDeleteButton(deleteButton);
        });

        if (userRole === 'teacher') {
            contentDiv.appendChild(editButton);
        }

        const deleteButton = document.createElement('button');
        deleteButton.className = 'btn btn-sm btn-danger mt-2';
        deleteButton.textContent = 'Видалити змагання';

        deleteButton.style.display = 'none';

        deleteButton.addEventListener('click', function (event) {
            event.stopPropagation();
            event.preventDefault();
            confirmDelete(comp.id);
        });

        if (userRole === 'teacher') {
            contentDiv.appendChild(deleteButton);
        }

        cardDiv.appendChild(contentDiv);
        colDiv.appendChild(cardDiv);
        anchor.appendChild(colDiv);

        return anchor;
    } else {
        const tr = document.createElement('tr');

        if (isExpired) {
            tr.classList.add('table-danger');
        }

        const idTd = document.createElement('td');
        idTd.textContent = comp.id;

        const nameTd = document.createElement('td');
        const nameLink = document.createElement('a');
        nameLink.href = comp.website.startsWith('http://') || comp.website.startsWith('https://') ? comp.website : `http://${comp.website}`;
        nameLink.textContent = comp.name;
        nameLink.className = 'text-decoration-none';
        nameLink.target = '_blank';
        nameTd.appendChild(nameLink);

        const descriptionTd = document.createElement('td');
        descriptionTd.textContent = comp.description;

        const startDateTd = document.createElement('td');
        startDateTd.textContent = comp.startdate;

        const deadlineTd = document.createElement('td');
        deadlineTd.textContent = comp.deadline;

        const statusTd = document.createElement('td');
        statusTd.textContent = isExpired ? 'Закінчився' : 'Активний';

        const actionTd = document.createElement('td');

        if (userRole === 'teacher') {
            const editButton = document.createElement('button');
            editButton.className = 'btn btn-sm btn-warning me-2';
            editButton.textContent = 'Редагувати';

            editButton.addEventListener('click', function () {
                toggleDeleteButton(deleteButton);
            });
            actionTd.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.className = 'btn btn-sm btn-danger';
            deleteButton.textContent = 'Видалити змагання';
            deleteButton.style.display = 'none';

            deleteButton.addEventListener('click', function () {
                confirmDelete(comp.id);
            });
            actionTd.appendChild(deleteButton);
        }

        tr.appendChild(idTd);
        tr.appendChild(nameTd);
        tr.appendChild(descriptionTd);
        tr.appendChild(startDateTd);
        tr.appendChild(deadlineTd);
        tr.appendChild(statusTd);
        tr.appendChild(actionTd);

        return tr;
    }
}

function toggleDeleteButton(deleteButton) {
    if (deleteButton.style.display === 'none') {
        deleteButton.style.display = 'inline-block';
    } else {
        deleteButton.style.display = 'none';
    }
}

function confirmDelete(compId) {
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    confirmDeleteButton.setAttribute('data-id', compId);

    const deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    deleteConfirmationModal.show();
}

function deleteCompetition() {
    const compId = document.getElementById('confirmDeleteButton').getAttribute('data-id');

    fetch('http://localhost/php/deleteCompetition.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${compId}`,
    })
        .then(response => {
            if (response.ok) {
                loadCompetitions();
            } else {
                console.error('Failed to delete competition');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

