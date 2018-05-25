<?php
function delete($id) {
    if (file_exists(__DIR__ . "/images/main_{$id}.jpg")) {
        unlink(__DIR__ . "/images/main_{$id}.jpg");
    }
    if (file_exists(__DIR__ . "/images/img1_{$id}.jpg")) {
        unlink(__DIR__ . "/images/img1_{$id}.jpg");
    }
    if (file_exists(__DIR__ . "/images/img2_{$id}.jpg")) {
        unlink(__DIR__ . "/images/img2_{$id}.jpg");
    }
    if (file_exists(__DIR__ . "/images/img3_{$id}.jpg")) {
        unlink(__DIR__ . "/images/img3_{$id}.jpg");
    }
}

function upload($id) {
    delete($id);
    $uploaddir = __DIR__ . '/images/';
    if ($_FILES['main']['name']) {
        $uploadfile = $uploaddir . "main_{$id}.jpg";
        move_uploaded_file($_FILES['main']['tmp_name'], $uploadfile);      
    }
    if ($_FILES['img1']['name']) {
        $uploadfile = $uploaddir . "img1_{$id}.jpg";
        move_uploaded_file($_FILES['img1']['tmp_name'], $uploadfile);
    }
    if ($_FILES['img2']['name']) {
        $uploadfile = $uploaddir . "img2_{$id}.jpg";
        move_uploaded_file($_FILES['img2']['tmp_name'], $uploadfile);
    }
    if ($_FILES['img3']['name']) {
        $uploadfile = $uploaddir . "img3_{$id}.jpg";
        move_uploaded_file($_FILES['img3']['tmp_name'], $uploadfile);
    }
}

$dbParams = require('db.php');
$db = new PDO (
    "mysql:host=localhost;dbname=".$dbParams['database'].";charset=utf8",
    $dbParams['username'],
    $dbParams['password']
);
if (isset($_GET['delete'])) {                      
    $db->query('
	DELETE FROM `films` WHERE id_film = ' . (int)$_GET['delete'] . ';
	');
    delete($_GET['delete']); 
    header('Location: /admin.php'); //конструкция, необходмая для Перенаправление браузера 
    exit();
}
if (isset($_GET['edit'])) {
    $filmEdit = $db->query('SELECT * FROM `films` WHERE id_film = ' . (int)$_GET['edit'])->fetch();
    if (!$filmEdit) {
        if (isset($_POST['id'], $_POST['name'], $_POST['short_description'], $_POST['continuance'])) {
            $sql = "
			INSERT INTO `films` (name_film, year, continuance, short_description, id_sentiment) 
			VALUE (:name_film, :year, :continuance, :short_description, :id_sentiment)
			";
            $query = $db->prepare($sql);
            $filmSaved = $query->execute([
                ':name_film' => $_POST['name'],
                ':year' => $_POST['year'],
                ':continuance' => $_POST['continuance'],
                ':short_description' => $_POST['short_description'],
                ':id_sentiment' => $_POST['id_sentiment'],
            ]);
            $id = $db->lastInsertId();
            upload($id);
            header('Location: /admin.php');
            exit();
        }
    } else {
        if (isset($_POST['id'], $_POST['name'], $_POST['short_description'], $_POST['continuance'])) {
            $sql = "
				UPDATE `films` SET 
				name_film = :name_film, 
				year = :year, 
				continuance = :continuance, 
				short_description = :short_description, 
				id_sentiment = :id_sentiment
				WHERE  id_film = :id_film
			";
            $query = $db->prepare($sql);
            $query->execute([
                ':id_film' => $_POST['id'],
                ':name_film' => $_POST['name'],
                ':year' => $_POST['year'],
                ':continuance' => $_POST['continuance'],
                ':short_description' => $_POST['short_description'],
                ':id_sentiment' => $_POST['id_sentiment'],
            ]);
            upload($_POST['id']);
            header('Location: /admin.php');
            exit();
        }
        $id = $filmEdit['id_film'];
        $name = $filmEdit['name_film'];
        $year = $filmEdit['year'];
        $continuance = $filmEdit['continuance'];
        $short_description = $filmEdit['short_description'];
        $id_sentiment = $filmEdit['id_sentiment'];
    }
}
$sentiments = $db->query('
	SELECT * FROM `sentiments`
')->fetchAll();
function getSentiment($id, $sentiments) {  
    foreach ($sentiments as $sentiment) {
        if ($sentiment['id_sentiment'] == $id) {
            return $sentiment['name_sentiment'];
        }
    }
}
?>
<html>
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
    <meta charset="utf-8">
    <title>Администрирование</title>
</head>
<body>
<div class="container">
    <?php if (isset($_GET['edit'])){ ?>
        <nav class="navbar-light bg-light">
            <div class="row">
                <div class="col-sm"></div>
                <div class="col-lg-auto">
                    <h1>Администрирование</h1>
                </div>
                <div class="col-sm"></div>
            </div>
        </nav>
        <a href="admin.php" class="btn btn-success">Назад</a>        
        <form enctype="multipart/form-data" action="/admin.php?edit=<?= $id ?>" method="post">
            <input type="hidden" id="id" value="<?= $id ?>" class="form-control" name="id" maxlength="255">
            <div class="form-group">
                <label class="control-label" for="name">Название</label>
                <input required type="text" id="name" value="<?= $name ?>" class="form-control" name="name" maxlength="255">
            </div>
            <div class="form-group">
                <label class="control-label" for="year">Год</label>
                <input required type="number" id="year" value="<?= $year ?>" class="form-control" name="year" maxlength="255">
            </div>
            <div class="form-group">
                <label class="control-label" for="continuance">Продолжительность (мин)</label>
                <input required type="number" id="continuance" value="<?= $continuance ?>" class="form-control" name="continuance" maxlength="255">
            </div>
            <div class="form-group">
                <label class="control-label" for="short_description">Описание</label>
                <textarea required id="short_description" class="form-control" name="short_description"  maxlength="255"><?= $short_description ?></textarea>
            </div>
            <div class="form-group">
                <?php 
                if (file_exists(__DIR__ . "/images/main_{$id}.jpg")) { ?>
                    <img width="200px" src="/images/main_<?php echo $id ?>.jpg" alt="">
                <?php } ?>
                <label class="control-label">Главная</label>
                <input type="file" name="main">
            </div>
            <div class="form-group">
                <?php 
                if (file_exists(__DIR__ . "/images/img1_{$id}.jpg")) { ?>
                    <img width="200px" src="/images/img1_<?php echo $id ?>.jpg" alt="">
                <?php } ?>
                <label class="control-label">Изображение 1</label>
                <input type="file" name="img1">
            </div>
            <div class="form-group">
                <?php 
                if (file_exists(__DIR__ . "/images/img2_{$id}.jpg")) { ?>
                    <img width="200px" src="/images/img2_<?php echo $id ?>.jpg" alt="">
                <?php } ?>
                <label class="control-label">Изображение 2</label>
                <input type="file" name="img2">
            </div>
            <div class="form-group">
                <?php 
                if (file_exists(__DIR__ . "/images/img3_{$id}.jpg")) { ?>
                    <img width="200px" src="/images/img3_<?php echo $id ?>.jpg" alt="">
                <?php } ?>
                <label class="control-label">Изображение 3</label>
                <input type="file" name="img3">
            </div>
            <div class="form-group field-deposittypes-currency_id">
                <label class="control-label" for="id_sentiment">Настроение</label>
                <select required id="id_sentiment" class="form-control" name="id_sentiment">
                    <?php foreach ($sentiments as $sentiment) { ?>
                        <option 
                            <?= $sentiment['id_sentiment'] == $id_sentiment ? 'selected' : '' ?>  
                                value="<?= $sentiment['id_sentiment'] ?>">
                            <?= $sentiment['name_sentiment'] ?>
                        </option>
                    <?php } ?>
                </select>
                <div class="help-block"></div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-success"><?= $id ? 'Обновить' : 'Создать' ?></button>   <!-- Совсем не могу понять код PHP в этом моменте :( -->
            </div>
        </form>
    <?php } else { ?>
        <?php
        $films = $db->query('
	        SELECT * FROM `films` 
			ORDER BY id_film 
          ')->fetchAll();
        ?>
        <nav class="navbar-light bg-light">
            <div class="row">
                <div class="col-sm"></div>
                <div class="col-lg-auto">
                    <h1>Администрирование</h1>
                </div>
                <div class="col-sm"></div>
            </div>
        </nav>
        <div class="applications-index">
            <h1>Фильмы</h1>
			<a href="admin.php?edit=edit" class="btn btn-success">Создать</a>
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>№</th>
                    <th>Код</th>
                    <th>Название</th>
                    <th>Год</th>
                    <th>Продолжительность(мин)</th>
                    <th>Описание</th>
                    <th>Настроение</th>
                    <th>Функция</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($films as $key => $film) { ?>
                    <tr data-key="1">
                        <td><?= ($key + 1) ?></td>
                        <td><?= $film['id_film'] ?></td>
                        <td><?= $film['name_film'] ?></td>
                        <td><?= $film['year'] ?></td>
                        <td><?= $film['continuance'] ?></td>
                        <td><?= $film['short_description'] ?></td>
                        <td><?= getSentiment($film['id_sentiment'], $sentiments) ?></td>
                        <td><a onclick="return confirm('Вы хотите удалить этот фильм?')" href="/admin.php?delete=<?= $film['id_film'] ?>">Удалить</a>
                        <br><a href="/admin.php?edit=<?= $film['id_film'] ?>">Редактировать</a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
</body>
</html>
