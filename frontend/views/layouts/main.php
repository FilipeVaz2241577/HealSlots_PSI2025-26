<?php

/** @var \yii\web\View $this - Objeto da view do Yii */
/** @var string $content - Conteúdo principal da página a ser renderizado */

use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

// Registra o asset bundle do frontend (CSS, JS, etc.)
AppAsset::register($this);

// CSS customizado para a navbar
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
        <!-- Meta tags essenciais -->
        <meta charset="<?= Yii::$app->charset ?>"> <!-- Define o charset da aplicação -->
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> <!-- Responsive design -->

        <?php $this->registerCsrfMetaTags() ?> <!-- Gera meta tags CSRF para segurança -->

        <title><?= Html::encode($this->title) ?></title> <!-- Título da página (codificado para segurança) -->

        <!-- Google Web Fonts -->
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">

        <!-- Icon Font Stylesheet -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

        <?php $this->head() ?> <!-- Inclui CSS, JS e outras tags head do Yii -->
    </head>
    <body class="d-flex flex-column h-100"> <!-- Layout flexbox para altura total -->
    <?php $this->beginBody() ?>

    <!-- Navbar Start -->
    <div class="container-fluid sticky-top bg-white shadow-sm">
        <div class="container">
            <nav class="navbar navbar-expand-lg bg-white navbar-light py-3 py-lg-1">
                <!-- Logo e nome da aplicação -->
                <a href="<?= Yii::$app->homeUrl ?>" class="navbar-brand d-flex align-items-center">
                    <img src="<?= Yii::getAlias('@web/img/icon_semtexto.png') ?>" alt="HealSlots" style="height: 80px;" class="me-3">
                    <h1 class="m-0 text-uppercase text-primary"><?= Html::encode(Yii::$app->name) ?></h1>
                </a>

                <!-- Botão para menu (mobile) -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Itens do menu -->
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav ms-auto py-0">
                        <!-- Link para Equipamentos -->
                        <a href="<?= \yii\helpers\Url::to(['/site/tiposequipamento']) ?>"
                           class="nav-item nav-link <?= $this->title == 'Equipamentos' ? 'active' : '' ?>">Equipamentos</a>

                        <!-- Link para Blocos/Salas -->
                        <a href="<?= \yii\helpers\Url::to(['/site/blocos']) ?>"
                           class="nav-item nav-link <?= $this->title == 'blocos' ? 'active' : '' ?>">Blocos/Salas</a>

                        <!-- Link para Suporte -->
                        <a href="<?= \yii\helpers\Url::to(['/site/suporte']) ?>"
                           class="nav-item nav-link <?= $this->title == 'Suporte' ? 'active' : '' ?>">Suporte</a>

                        <!-- Menu de utilizador -->
                        <?php if (Yii::$app->user->isGuest): ?>
                            <!-- Se não estiver autenticado, mostra link para login -->
                            <a href="<?= \yii\helpers\Url::to(['/site/login']) ?>" class="nav-item nav-link">
                                <i class="fa fa-user me-1"></i>Login
                            </a>
                        <?php else: ?>
                            <!-- Se estiver autenticado, mostra dropdown com nome do utilizador -->
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button">
                                    <i class="fa fa-user me-1"></i><?= Yii::$app->user->identity->username ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                                    <!-- Formulário para logout -->
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

    <!-- Conteúdo principal da página -->
    <main role="main" class="flex-shrink-0">
        <div class="container-fluid">
            <!-- Migalhas de pão (breadcrumbs) para navegação hierárquica -->
            <?= Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>

            <!-- Widget para exibir mensagens flash -->
            <?= Alert::widget() ?>

            <!-- Renderiza o conteúdo específico da view -->
            <?= $content ?>
        </div>
    </main>

    <!-- Rodapé da página -->
    <footer class="footer mt-auto py-3 text-muted bg-light">
        <div class="container">
            <!-- Direitos autorais e nome da aplicação -->
            <p class="float-start">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>

            <!-- Powered by Yii Framework -->
            <p class="float-end"><?= Yii::powered() ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage(); ?>