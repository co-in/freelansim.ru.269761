# freelansim.ru.269761
https://freelansim.ru/tasks/269761


Пример конфигурации
```php
'components' => [
    'telegram' => [
        'class' => 'aki\telegram\TelegramBot',
        'botToken' => 'ТОКЕН_БОТА',
        'output' => [
            //Есть возможность расширить вывод ошибок допустим в базу или на почту
            'class' => 'aki\telegram\FileOutput',
             //Ошибки логируются в файл
            'fileName' => __DIR__ . '/../tg.log',
        ],
    ],
]
```


Получение информации о боте
```php
Yii::$app->telegram->getMe();
```

Отравка сообщения
```php
Yii::$app->tg->sendMessage([
	'chat_id' => 123456789,
	'text' => 'Тестовое сообщение',
]);
```

Пример загрузки нового файла. Проверяется по file_exists
```php
Yii::$app->tg->sendAudio([
	'chat_id' => 123456789,
	'audio' => '/home/coin/Downloads/test.mp3', //путь к файлу
	'caption' => 'Создание Файла',
	'duration' => 0,
]);
```

Пример загрузки существующего файла. Проверяется по file_exists
```php
Yii::$app->tg->sendAudio([
	'chat_id' => 123456789,
	'audio' => 'CQADAgADxgQAAluH0Ui880sYZ9eVgBYE', //file_id
	'caption' => 'Создание Файла',
	'duration' => 0,
]);
```

Пример загрузки медиа группы

```php
Yii::$app->tg->sendMediaGroup([
    'chat_id' => 212856439,
    'media' => [
        [
            'type' => 'photo',
            'media' =>'/home/coin/Downloads/test.png',
        ],
        [
            'type' => 'photo',
            'media' => 'AgADAgADfqwxG1uH0UiUZPD1RWskV7cDuA8ABAEAAwIAA3gAA2C6BAABFgQ',
        ],
        [
            'type' => 'video',
            'media' => '/home/coin/Downloads/test.mp4', 
        ],
    ],
]);
```
