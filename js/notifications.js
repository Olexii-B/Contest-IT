document.addEventListener('DOMContentLoaded', () => {
    const notificationsPanel = document.getElementById('notifications');

    // Функція для завантаження повідомлень
    const loadNotifications = () => {
        fetch('../php/getNotifications.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                notificationsPanel.innerHTML = ''; // Очищення попередніх повідомлень

                // Якщо є повідомлення
                if (data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        const div = document.createElement('div');
                        div.className = `notification-item ${notification.type}`;
                        div.innerHTML = `
                            <span>${notification.content}</span>
                            <button class="close-btn" data-id="${notification.id}">✖</button>
                        `;
                        notificationsPanel.append(div);

                        // Автоматичне зникнення через 10 секунд
                        setTimeout(() => div.remove(), 10000);
                    });
                } else {
                    // Якщо повідомлень немає
                    notificationsPanel.innerHTML = '<p>Повідомлення відсутні.</p>';
                }
            })
            .catch(error => {
                console.error('Помилка завантаження повідомлень:', error);
                notificationsPanel.innerHTML = '<p>Не вдалося завантажити повідомлення.</p>';
            });
    };

    // Підключення закриття повідомлення
    notificationsPanel.addEventListener('click', (e) => {
        if (e.target.classList.contains('close-btn')) {
            const notificationId = e.target.dataset.id;

            // Надсилання запиту на сервер для позначення прочитаним
            fetch('../php/markAsRead.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${notificationId}`
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      e.target.parentElement.remove();
                  }
              });
        }
    });

    loadNotifications(); // Завантажити повідомлення при завантаженні сторінки

    // Оновлення повідомлень кожні 10 секунд
    setInterval(loadNotifications, 10000);
});
