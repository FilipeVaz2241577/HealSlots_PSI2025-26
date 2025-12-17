<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCss("
    .navbar-nav .nav-link {
        font-size: 1.15rem;
        font-weight: 500;
    }
    
    .dropdown-menu .dropdown-item {
        font-size: 1.05rem;
    }
");

?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>

        <!-- Google Web Fonts -->
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">

        <!-- Icon Font Stylesheet -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

        <?php $this->head() ?>
    </head>
    <body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <!-- Navbar Start -->
    <div class="container-fluid sticky-top bg-white shadow-sm">
        <div class="container">
            <nav class="navbar navbar-expand-lg bg-white navbar-light py-3 py-lg-1">
                <a href="<?= Yii::$app->homeUrl ?>" class="navbar-brand d-flex align-items-center">
                    <img src="<?= Yii::getAlias('@web/img/icon_semtexto.png') ?>" alt="HealSlots" style="height: 80px;" class="me-3">
                    <h1 class="m-0 text-uppercase text-primary"><?= Html::encode(Yii::$app->name) ?></h1>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav ms-auto py-0">
                        <a href="<?= \yii\helpers\Url::to(['/site/tiposequipamento']) ?>" class="nav-item nav-link <?= $this->title == 'Equipamentos' ? 'active' : '' ?>">Equipamentos</a>
                        <a href="<?= \yii\helpers\Url::to(['/site/blocos']) ?>" class="nav-item nav-link <?= $this->title == 'Blocos' ? 'active' : '' ?>">Blocos/Salas</a>
                        <a href="<?= \yii\helpers\Url::to(['/site/suporte']) ?>" class="nav-item nav-link <?= $this->title == 'Suporte' ? 'active' : '' ?>">Suporte</a>

                        <?php if (Yii::$app->user->isGuest): ?>
                            <a href="<?= \yii\helpers\Url::to(['/site/login']) ?>" class="nav-item nav-link">
                                <i class="fa fa-user me-1"></i>Login
                            </a>
                        <?php else: ?>
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button">
                                    <i class="fa fa-user me-1"></i><?= Yii::$app->user->identity->username ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                                    <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline'])
                                    . Html::submitButton(
                                            'Log Out',
                                            ['class' => 'dropdown-item border-0 bg-transparent']
                                    )
                                    . Html::endForm() ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Navbar End -->

    <main role="main" class="flex-shrink-0">
        <div class="container-fluid">
            <?= Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>

    <footer class="footer mt-auto py-3 text-muted bg-light">
        <div class="container">
            <p class="float-start">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
            <p class="float-end"><?= Yii::powered() ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage(); ?>