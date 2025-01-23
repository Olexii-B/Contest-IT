document.addEventListener('DOMContentLoaded', () => {
    const notificationsPanel = document.getElementById('notifications');

    const loadNotifications = () => {
        fetch('../php/getNotifications.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                notificationsPanel.innerHTML = '';

                if (data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        const div = document.createElement('div');
                        div.className = `notification-item ${notification.type}`;
                        div.innerHTML = `
                            <span>${notification.content}</span>
                            <button class="close-btn" data-id="${notification.id}">✖</button>
                        `;
                        notificationsPanel.append(div);

                        setTimeout(() => div.remove(), 10000);
                    });
                } else {
                    notificationsPanel.innerHTML = '<p>Повідомлення відсутні.</p>';
                }
            })
            .catch(error => {
                console.error('Помилка завантаження повідомлень:', error);
                notificationsPanel.innerHTML = '<p>Не вдалося завантажити повідомлення.</p>';
            });
    };

    notificationsPanel.addEventListener('click', (e) => {
        if (e.target.classList.contains('close-btn')) {
            const notificationId = e.target.dataset.id;

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

    loadNotifications();

    // Оновлення повідомлень кожні 3 секунд
    setInterval(loadNotifications, 3000);
});

