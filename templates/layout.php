<?php
use touiteur\render\View;
use touiteur\render\Template;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= View::e($data['title']) ?> - Touiteur</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<div class="page">
    <?= View::render(Template::Header) ?>
    <div class="layout">
        <?= $data['content'] ?>
    </div>

</div>
<script src="public/js/app.js"></script>
</body>
</html>
