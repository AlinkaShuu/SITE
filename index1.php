<?php
$dbParams = require('db.php');
$db = new PDO (
    "mysql:host=localhost;dbname=".$dbParams['database'].";charset=utf8",
    $dbParams['username'],
    $dbParams['password']
);
$sentiments = $db->query('
	SELECT * FROM `sentiments`
')->fetchAll();

if (isset($_GET['sentiment'])) {
    // В условии нахожится сортировка (ORDER BY RAND()) фильм выбирается в случайном порядке с помощью SQL функции.
    // Так же добавлено ограничение в одну запись LIMIT 1, выбирается только одно значение.
    $query = $db->prepare('
		SELECT `films`.`id_film`, `films`.`name_film`, `films`.`year`, `films`.`continuance`, `films`.`short_description` 
		FROM `films`
		INNER JOIN `sentiments` ON `sentiments`.`id_sentiment` = `films`.`id_sentiment`
		WHERE `sentiments`.`id_sentiment` = :sentiment 
		ORDER BY RAND()
		LIMIT 1
	');
    $query->execute(['sentiment' => $_GET['sentiment']]);
    $films = $query->fetchAll();
}
?>
<html>
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
          integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB"
          crossorigin="anonymous">
    <meta charset="utf-8">
    <title>Фильм под настроение</title>
</head>
<body>
<div class="container">
    <nav class="navbar-light bg-light">
        <div class="row">
            <div class="col-sm"></div>
            <div class="col-lg-auto">
                <h1>Фильм под настроение</h1>
            </div>
            <div class="col-sm"></div>
        </div>
    </nav>
    <div class="row">
        <div class="col-lg">
            <div class="jumbotron">
                Добро пожаловать! Данное приложение посвящено подборке фильмов под ваше настроение! Ежегодно на
                экранах
                по всему
                миру выпускаются тысячи кинофильмов и зачастую зрителю очень сложно найти подходящий для просмотра.
                Мы
                постараемся
                вам помочь.Просто выберете какое у вас настроение и нажмите на кнопку «Подобрать».
                <p class="lead">Приятного просмотра!</p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm"></div>
        <div class="col-lg-auto">
            <h3>Какое у вас сейчас настроение?</h3>
        </div>
        <div class="col-sm"></div>
    </div>
    <div class="row">
        <div class="col-sm"></div>
        <form method="GET" action="index1.php">
            <div class="col-sm">
                <div class="form-group">
                    <select class="form-control-lg" id="sentiment" name="sentiment">
                        <?php
                        foreach ($sentiments as $sentiment) { ?>
                            <option value="<?= htmlspecialchars($sentiment['id_sentiment']) ?>" <?php
                            if (isset($_GET['sentiment']) && $_GET['sentiment'] == $sentiment['id_sentiment']) {
                                echo ' selected';
                            } ?>>
                                <?= htmlspecialchars($sentiment['name_sentiment']) ?>
                            </option>
                            <?php
                        } ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-sm"></div>
                    <div class="col-lg-auto">
                        <input type="submit" class="btn btn-success btn-lg" value="Подобрать">
                    </div>
                    <div class="col-sm"></div>
                </div>
            </div>
        </form>
        <div class="col-sm"></div>
    </div>
    <?php
    // Проверка на существование переменной films, Иначе возникает ошибка о том, что такая переменная не существует.
    // И присваиваем переменным значения

    if (isset($films)) {
        foreach ($films as $film) {
            $id = htmlspecialchars($film ['id_film']);
            $name = htmlspecialchars($film ['name_film']);
            $year = htmlspecialchars($film ['year']);
            $continuance = htmlspecialchars($film ['continuance']);
            $short_description = htmlspecialchars($film ['short_description']);
        }
    }
    //без проверки, сразу на форме отображается год выпуска и т.д.
    if (isset($id, $name, $short_description, $year, $continuance)) { ?>
        <div class="shadow p-3 mb-5 bg-white rounded fadein jumbotron ">
            <div class="row">
                <div class="col-3">
                    <?php // Проверяем есть ли Главная картинка к фильму, если нет, то не выводим. // __DIR__ Хранит в себе полный путь до директории, где лежит файл index1.php.
                    if (file_exists(__DIR__ . "/images/main_{$id}.jpg")) { ?>
                        <img width="200px" src="/images/main_<?php echo $id ?>.jpg" alt="">
                    <?php } ?>
                </div>
                <div class="col-9">
                    <h3><?php echo $name ?></h3>
                    <p class="lead">Год выпуска: <?php echo $year ?> год.</p>
                    <p class="lead">Продолжительность: <?php echo $continuance ?> мин.</p>
                    <p class="lead">Кадры из фильма: </p>
                    <p class="lead">
                        <?php for ($i = 1; $i <= 3; $i++) {
                            // Проверяем есть ли еще картинки, то есть кадры из фильма,их может быть максимум 3
                            if (!file_exists(__DIR__ . "/images/img{$i}_{$id}.jpg")) {
                                continue;
                            } ?>
                            <img width="200px" src="/images/img<?php echo $i; ?>_<?php echo $id ?>.jpg" alt="">
                        <?php } ?>
                    </p>
                    <p><?php echo $short_description ?></p>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
</body>
</html>
