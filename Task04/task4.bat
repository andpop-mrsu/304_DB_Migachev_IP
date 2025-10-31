#!/bin/bash
chcp 65001

sqlite3 movies_rating.db < db_init.sql

echo 1. Найти все пары пользователей, оценивших один и тот же фильм. Устранить дубликаты, проверить отсутствие пар с самим собой. Для каждой пары должны быть указаны имена пользователей и название фильма, который они оценили. В списке оставить первые 100 записей.
echo --------------------------------------------------
sqlite3 movies_rating.db -box "SELECT DISTINCT u1.name as user1, u2.name as user2, m.title as movie FROM ratings r1 JOIN ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id JOIN users u1 ON r1.user_id = u1.id JOIN users u2 ON r2.user_id = u2.id JOIN movies m ON r1.movie_id = m.id LIMIT 100;"
echo " "

echo 2. Найти 10 самых старых оценок от разных пользователей, вывести названия фильмов, имена пользователей, оценку, дату отзыва в формате ГГГГ-ММ-ДД.
echo --------------------------------------------------
sqlite3 movies_rating.db -box "SELECT m.title as 'Название фильма', u.name as 'Имя пользователя', r.rating as 'Оценка', date(r.timestamp, 'unixepoch') as 'Дата отзыва' FROM ratings r JOIN movies m ON r.movie_id = m.id JOIN users u ON r.user_id = u.id ORDER BY r.timestamp ASC LIMIT 10;"
echo " "

echo 3. Вывести в одном списке все фильмы с максимальным средним рейтингом и все фильмы с минимальным средним рейтингом. Общий список отсортировать по году выпуска и названию фильма. В зависимости от рейтинга в колонке "Рекомендуем" для фильмов должно быть написано "Да" или "Нет".
echo --------------------------------------------------
sqlite3 movies_rating.db -box "WITH movie_ratings AS (SELECT m.id, m.title, m.year, AVG(r.rating) as avg_rating FROM movies m JOIN ratings r ON m.id = r.movie_id GROUP BY m.id, m.title, m.year), max_min_ratings AS (SELECT MAX(avg_rating) as max_rating, MIN(avg_rating) as min_rating FROM movie_ratings) SELECT mr.title as 'Название фильма', mr.year as 'Год', ROUND(mr.avg_rating, 2) as 'Средний рейтинг', CASE WHEN mr.avg_rating = (SELECT max_rating FROM max_min_ratings) THEN 'Да' WHEN mr.avg_rating = (SELECT min_rating FROM max_min_ratings) THEN 'Нет' END as 'Рекомендуем' FROM movie_ratings mr WHERE mr.avg_rating = (SELECT max_rating FROM max_min_ratings) OR mr.avg_rating = (SELECT min_rating FROM max_min_ratings) ORDER BY mr.year, mr.title;"
echo " "

echo 4. Вычислить количество оценок и среднюю оценку, которую дали фильмам пользователи-мужчины в период с 2011 по 2014 год.
echo --------------------------------------------------
sqlite3 movies_rating.db -box "SELECT COUNT(*) as 'Количество оценок', ROUND(AVG(r.rating), 2) as 'Средняя оценка' FROM ratings r JOIN users u ON r.user_id = u.id WHERE u.gender = 'M' AND strftime('%%Y', datetime(r.timestamp, 'unixepoch')) BETWEEN '2011' AND '2014';"
echo " "

echo 5. Составить список фильмов с указанием средней оценки и количества пользователей, которые их оценили. Полученный список отсортировать по году выпуска и названиям фильмов. В списке оставить первые 20 записей.
echo --------------------------------------------------
sqlite3 movies_rating.db -box "SELECT m.title as 'Название фильма', m.year as 'Год', ROUND(AVG(r.rating), 2) as 'Средняя оценка', COUNT(DISTINCT r.user_id) as 'Количество оценок' FROM movies m JOIN ratings r ON m.id = r.movie_id GROUP BY m.id, m.title, m.year ORDER BY m.year, m.title LIMIT 20;"
echo " "

echo 6. Определить самый распространенный жанр фильма и количество фильмов в этом жанре. Отдельную таблицу для жанров не использовать, жанры нужно извлекать из таблицы movies.
echo --------------------------------------------------
sqlite3 movies_rating.db -box "WITH split(genre, rest) AS (SELECT CASE WHEN instr(genres, '|') > 0 THEN substr(genres, 1, instr(genres, '|') - 1) ELSE genres END, CASE WHEN instr(genres, '|') > 0 THEN substr(genres, instr(genres, '|') + 1) ELSE '' END FROM movies WHERE genres != '' AND genres IS NOT NULL UNION ALL SELECT CASE WHEN instr(rest, '|') > 0 THEN substr(rest, 1, instr(rest, '|') - 1) ELSE rest END, CASE WHEN instr(rest, '|') > 0 THEN substr(rest, instr(rest, '|') + 1) ELSE '' END FROM split WHERE rest != '') SELECT trim(genre) as 'Самый распространенный жанр', COUNT(*) as 'Количество фильмов' FROM split WHERE genre != '' AND genre IS NOT NULL GROUP BY trim(genre) ORDER BY COUNT(*) DESC LIMIT 1;"
echo " "

echo 7. Вывести список из 10 последних зарегистрированных пользователей в формате "Фамилия Имя|Дата регистрации" (сначала фамилия, потом имя).
echo --------------------------------------------------
sqlite3 movies_rating.db -box "SELECT substr(name, instr(name, ' ') + 1) || ' ' || substr(name, 1, instr(name, ' ') - 1) || '|' || register_date as 'Пользователь|Дата регистрации' FROM users ORDER BY register_date DESC LIMIT 10;"
echo " "

echo 8. С помощью рекурсивного CTE определить, на какие дни недели приходился ваш день рождения в каждом году.
echo --------------------------------------------------
sqlite3 movies_rating.db -box "WITH RECURSIVE birthday_years(year) AS (SELECT 2005 UNION ALL SELECT year + 1 FROM birthday_years WHERE year < 2025) SELECT year as 'Год', CASE strftime('%%w', year || '-05-17') WHEN '0' THEN 'Воскресенье' WHEN '1' THEN 'Понедельник' WHEN '2' THEN 'Вторник' WHEN '3' THEN 'Среда' WHEN '4' THEN 'Четверг' WHEN '5' THEN 'Пятница' WHEN '6' THEN 'Суббота' END as 'День недели' FROM birthday_years ORDER BY year;"
echo " "