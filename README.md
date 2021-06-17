# InoBot
Исходники бота для Slack'a по типу "ThankYouBot".


## Как это заставить работать?

1.  Выгрузить всё куда-нибудь на хостинг с ssl и доменом.

2. Зарегистрировать бота в api.slack.com/apps <br>
2.1. Необходимые разрешения Scopes: (Bot Token Scopes) <br>
2.1.1 **chat:write** <br>
2.1.2 **channels:read** <br>
2.1.3 **emoji:read** <br>
2.1.4 **reactions:read** <br>
2.1.5 **reactions:write** <br>
2.1.6 **users:read** <br>
2.1.7 **users:read.email** <br>
2.1.8 **users:write** <br>

3. Включить Event Subscriptions в настройках бота и прописать endpoint на файл Callback.php <br>
3.1 Включить событие message.channels в подписке на события.

4. Создать .env файл (можно использовать пример .env-example) и настроить его.

5. Создать необходимые таблицы в БД (или использовать пример в tables-example.sql)

6. Подключить бота в нужный workspace

7. Важно! Необходимо также пригласить бота в нужный канал. <br>
Для этого в этом канале пропишите /invite @NAME_BOT,  где NAME_BOT - имя вашего бота


## Структура ENV файла
* TOKEN - токен бота в api.slack.com/apps
* LIMIT_COMPLIMENT - лимит благодарностей (по умолчанию 5)
* NAME_REACTION - название emoji, которым бот сигнализирует что благодарность принята
* KEY_COMPLIMENT_TEXT - текст (или emoji), по которому определить что сообщение является благодарностью.
* MYSQL_* - Всё что касается подключения к БД.
* TEAM_WEB - ID команды (workspace), который отобразить в веб-морде
* LOG_MODE (1/0) - включить/отключить логирование всех запросов в файл log.txt
