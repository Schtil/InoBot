# InoBot
Исходники бота для Slack'a по типу "ThankYouBot".


## Как это заставить работать?

1.  Выгрузить всё куда-нибудь на хостинг с ssl и доменом.

2. Зарегистрировать бота в api.slack.com/apps
2.1. Необходимые разрешения Scopes: (Bot Token Scopes)
2.1.1 **chat:write**
2.1.2 **channels:read**
2.1.3 **emoji:read**
2.1.4 **reactions:read**
2.1.5 **reactions:write**
2.1.6 **users:read**
2.1.7 **users:read.email**
2.1.8 **users:write**

3. Создать .env файл (можно использовать пример .env-example) и настроить его.

4. Создать необходимые таблицы в БД (или использовать пример в tables-example.sql)

5. Подключить бота в нужный workspace

6. Важно! Необходимо также пригласить бота в нужный канал.
Для этого в этом канале пропишите /invite @NAME_BOT,  где NAME_BOT - имя вашего бота


## Структура ENV файла
* TOKEN - токен бота в api.slack.com/apps
* LIMIT_COMPLIMENT - лимит благодарностей (по умолчанию 5)
* NAME_REACTION - название emoji, которым бот сигнализирует что благодарность принята
* MYSQL_* - Всё что касается подключения к БД.
