<?php
$dbParams = require('db.php');
$db = new PDO (
    "mysql:host=localhost;dbname=".$dbParams['database'].";charset=utf8",
    $dbParams['username'],
    $dbParams['password']
);

if (isset($_GET['edit'])) {
	
	$id = '';
    $name = '';
	
	$sql = '
	SELECT * FROM `sentiments` 
	WHERE id_sentiment = :sentimentId';
    $query = $db->prepare($sql);
    $query->execute([
        ':sentimentId' => $_GET['edit']
    ]);
    $sentimentEdit = $query->fetch();
	
    if (!$sentimentEdit) {
        if (isset($_POST['id'], $_POST['name'])) {
            $sql = "
			INSERT INTO `sentiments` (name_sentiment) 
			VALUE (:name_sentiment)
			";
            $query = $db->prepare($sql);
            $sentimentSaved = $query->execute([
                ':name_sentiment' => $_POST['name']
            ]);
            $id = $db->lastInsertId();
			header('Location: /sentiments.php'); // Перенаправление браузера 
            exit();
        }
    } else {
        if (isset($_POST['id'], $_POST['name'])) {
            $sql = "
			UPDATE `sentiments` SET 
			name_sentiment = :name_sentiment
			WHERE  id_sentiment = :id_sentiment
			";
			$query = $db->prepare($sql);
            $query->execute([
				':id_sentiment' => $_POST['id'],
                ':name_sentiment' => $_POST['name']
            ]);
            header('Location: /sentiments.php');
            exit();
        }
        $id = $sentimentEdit['id_sentiment'];
        $name = $sentimentEdit['name_sentiment'];
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
        <a href="sentiments.php" class="btn btn-secondary">Назад</a>
        <form enctype="multipart/form-data" action="/sentiments.php?edit=<?= $id ?>" method="POST">
            <input type="hidden" id="id" value="<?= $id ?>" class="form-control" name="id" maxlength="255">
            <div class="form-group">
                <label class="control-label" for="name">Название настроения</label>
				<input required type="text" id="name" value="<?php if(isset($name)){ echo $name;} ?>" class="form-control" name="name" maxlength="255">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success"><?= $id ? 'Обновить' : 'Создать' ?></button>
            </div>
        </form>
    <?php } else { ?>
        <?php
        $sentiments = $db->query('
	        SELECT * FROM `sentiments`
			ORDER BY id_sentiment DESC
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
            <h1>Настроения</h1>
			<a href="RWxJC6OHIn.php" class="btn btn-primary">На главную</a> 
            <a href="sentiments.php?edit=" class="btn btn-success">Добавить настроение</a> <br><br>
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
					<th>Действие</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($sentiments AS $key => $sentiment) { ?>
                    <tr>
                        <td><?= $sentiment['id_sentiment'] ?></td>
                        <td><?= $sentiment['name_sentiment'] ?></td>
                        <td><a href="/sentiments.php?edit=<?= $sentiment['id_sentiment'] ?>">Редактировать</a></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
</body>
</html>
