# УКРАЇНСЬКИЙ README

## Встановлення на Windows та Linux

### Вимоги:
- Сервер Apache (на Windows можна використовувати XAMPP).
- PHP (версія 7.4+).
- MariaDB/MySQL.
- phpMyAdmin для роботи з базою даних.
- Git для клонування репозиторію.

### Кроки для Windows 10/11:
1) Встановіть XAMPP. Це надасть Apache, PHP та MySQL.

2) Клонування проєкту:
`git clone https://github.com/your-repo/contest-it.git`

3) Перенесіть файли у директорію htdocs (зазвичай це `C:\xampp\htdocs\`)

4) Запустіть XAMPP і включіть Apache та MySQL.

5) Створіть базу даних у phpMyAdmin (`http://localhost/phpmyadmin`)

6) Відкрийте веббраузер і введіть `http://localhost`

### Кроки для Arch Linux:

1) встановити необхідне програмне забезпечення
```
sudo pacman -S apache php mariadb git
```

2) Активуйте Apache:
```
sudo systemctl start httpd
sudo systemctl enable httpd
```

3) Налаштуйте MariaDB:
```
sudo systemctl start mariadb
sudo mysql_secure_installation
```

4) Клонування проєкту:
```
git clone https://github.com/Olexii-B/Contest-IT.git
```

5) Перемістіть файли у `/srv/http`:
```
sudo cp -r contest-it/* /srv/http/
```

6) Відкрийте браузер і введіть `http://localhost`


## Структура проєкту
```
├── assets          # Зображення, іконки
├── css             # CSS-стилі
├── html            # HTML-файли для різних сторінок
├── js              # JavaScript-файли (логіка сайту)
├── logs            # Логи PHP
├── php             # Серверна логіка
└── README.md       # Цей файл
```


## Опис функціоналу

### Для вчителів:
- Керування конкурсами: Створюйте, редагуйте та видаляйте конкурси.
- Класи: Додавайте учнів до класів для надання їм можливостей взяти участь в цьому конкурсі.
- Роботи учнів: Переглядайте та завантажуйте на свій пристрій роботи учнів.
- Новин-пости: Створюйте, редагуйте та видаляйте новини, коментуйте їх, та оцінюйте коментарі інших користувачів.

### Для учнів:
- Участь у конкурсах: Вибирайте конкурс, ознайомлюйтеся з умовами і завантажуйте свої роботи.
- Класи: Вступайте до класів, створених вчителями.
- Роботи: Подавайте роботи на конкурси, завантажуючи їх у клас в який ви вступили.
- Новин-пости: Переглядайте новини, коментуйте їх, та оцінюйте коментарі інших користувачів.

### Для гостей
- Огляд: Переглядайте новини.

### Для Адміністратора Веб-Сайта
- Керуйте всією БД. Доступ до адмін панелі myphpadmin


## ENGLISH README

## Installation on Windows and Linux

### Requirements:
- Apache server (XAMPP can be used on Windows).
- PHP (version 7.4+).
- MariaDB/MySQL.
- phpMyAdmin to work with the database.
- Git to clone the repository.

### Steps for Windows 10/11:
1) Install XAMPP. This will provide Apache, PHP, and MySQL.

2) Clone the project:
```
git clone https://github.com/Olexii-B/Contest-IT.git
```

3) Transfer the files to the htdocs directory (usually `C:\xampp\htdocs\`)

4) Run XAMPP and enable Apache and MySQL.

5) Create a database in phpMyAdmin (`http://localhost/phpmyadmin`)

6) Open a web browser and enter `http://localhost`



### Steps for Arch Linux:

1) install the required software
```
sudo pacman -S apache php mariadb git
```

2) Activate Apache:
```
sudo systemctl start httpd
sudo systemctl enable httpd
```

3) Configure MariaDB:
```
sudo systemctl start mariadb
sudo mysql_secure_installation
```

4) Clone the project:
```
git clone https://github.com/your-repo/contest-it.git
```

5) Move the files to `/srv/http`:
```
sudo cp -r contest-it/* /srv/http/
```

6) Import the database via phpMyAdmin or CLI:
```
mysql -u root -p < contest-it.sql
```

7) Open a browser and enter `http://localhost`


## Project structure
```
├── assets # Images, icons
├── css # CSS styles
├── html # HTML files for different pages
├── js # JavaScript files (site logic)
├── logs # PHP logs
├── php # Server logic
└── README.md # This file
```


### For teachers:
- Manage contests: Create, edit, and delete contests.
- Classes: Add students to classes to give them opportunities to participate in this competition.
- Student work: View and download student work to your device.
- News posts: Create, edit, and delete news items, comment on them, and rate other users' comments.

### For students:
- Participate in contests: Choose a competition, read the terms and conditions, and upload your work.
- Classes: Join classes created by teachers.
- Entries: Submit your work to contests by uploading it to the class you've joined.
- News posts: View news posts, comment on them, and rate other users' comments.

### For guests
- Overview: View news stories.

### For Website Administrator
- Manage the entire database. Access to the myphpadmin admin panel
