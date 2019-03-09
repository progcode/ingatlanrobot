<?php
/**
 * Created by PhpStorm.
 * User: kovac
 * Date: 2019. 03. 01.
 * Time: 20:33
 */

require('controllers/Property.php');
$property = new Property();
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

        @media(max-width: 992px) {
            .btn {
                display: block;
                margin-bottom: 10px;
                width: 100%;
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

        <a class="btn btn btn-danger" href="javascript: void(0)" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Ingatlan.com találatok</a>
        <a class="btn btn btn-warning" href="javascript: void(0)" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">Jófogás találatok</a>

        <hr />

        <?php if($property->listProperties('all')): ?>
        <div class="accordion" id="accordionExample">
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h2 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <span class="badge badge-danger">icom</span> - Ingatlan.com találatok
                        </button>
                    </h2>
                </div>

                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
                        <?php foreach($property->listProperties('icom') as $_property): ?>
                            <a href="<?php echo $_property['url']; ?>" class="list-group-item list-group-item-action" target="_blank" rel="noopener">
                                <?php echo $_property['title']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h2 class="mb-0">
                        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <span class="badge badge-warning">icom</span> - Jófogás találatok
                        </button>
                    </h2>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                    <div class="card-body">
                        <?php foreach($property->listProperties('jf') as $_property): ?>
                            <a href="<?php echo $_property['url']; ?>" class="list-group-item list-group-item-action" target="_blank" rel="noopener">
                                <?php echo $_property['title']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
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

