<?php
/**
 * Created by PhpStorm.
 * User: kovac
 * Date: 2019. 03. 01.
 * Time: 20:33
 */

require('controllers/Property.php');
$property = new Property();

/**
 * Get site type from url
 *
 * @var $getSite
 *
 */
$getSite = 'all';
if($property->get('site')) {
    $getSite = $property->get('site');
}
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <title>IngatlanRobot v0.1</title>

    <!-- Bootstrap core CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css?v=1" rel="stylesheet">

    <style>
        body {
            padding-top: 5rem;
        }

        .starter-template {
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
    </style>

</head>

<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="#">IngatlanRobot</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault"
            aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>

<main role="main" class="container">

    <div class="starter-template">
        <h1>
            <img class="img-responsive" src="irobot.png" style="width: 10%; height: auto" />
            Friss ingatlanok <?php echo date('Y-m-d'); ?>
        </h1>

        <a class="btn btn btn-primary" href="/List.php?site=all">Minden találat</a>
        <a class="btn btn btn-danger" href="/List.php?site=icom">Ingatlan.com találatok</a>
        <a class="btn btn btn-warning" href="/List.php?site=jf">Jófogás találatok</a>

        <hr />

        <?php if($property->listProperties($getSite)): ?>
        <div class="list-group">
            <?php foreach($property->listProperties($getSite) as $property): ?>
                <a href="<?php echo $property['url']; ?>" class="list-group-item list-group-item-action" target="_blank" rel="noopener">
                    <?php if($property['portal'] == 'ingatlan.com'): ?>
                        <span class="badge badge-danger">icom</span>
                    <?php else: ?>
                        <span class="badge badge-warning">jofogas</span>
                    <?php endif; ?>

                    <?php echo $property['title']; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">Nem találtam a mai nap új ingatlanokat :(</div>
        <?php endif; ?>

        <hr />
        <p>IngatlanRobot v0.2.1</p>
    </div>

</main><!-- /.container -->

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js?v=1"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js?v=1"></script>

</body>
</html>

