<?php

/** @var \yii\web\View $this */
/** @var string $content */

use backend\assets\AppAsset;
use common\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

// TODO: Lógica para determinar item ativo no menu
$currentController = Yii::$app->controller->id;
$currentAction = Yii::$app->controller->action->id;
$currentRoute = $currentController . '/' . $currentAction;

// TODO: Definir quais rotas pertencem a cada item do menu
$isDashboard = $currentRoute === 'site/index' || $currentController === 'site';
$isUtilizadores = $currentController === 'utilizador';
$isElements = in_array($currentController, ['element', 'button', 'typography']) ||
    in_array($currentRoute, ['site/button', 'site/typography', 'site/element']);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>

        <!-- Favicon -->
        <link href="<?= Yii::getAlias('@web') ?>/img/favicon.ico" rel="icon">

        <!-- Google Web Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Icon Font Stylesheet -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

        <!-- Libraries Stylesheet -->
        <link href="<?= Yii::getAlias('@web') ?>/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
        <link href="<?= Yii::getAlias('@web') ?>/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

        <!-- Customized Bootstrap Stylesheet -->
        <link href="<?= Yii::getAlias('@web') ?>/css/bootstrap.min.css" rel="stylesheet">

        <!-- Template Stylesheet -->
        <link href="<?= Yii::getAlias('@web') ?>/css/style.css" rel="stylesheet">

        <?php $this->head() ?>
    </head>

    <body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <div class="container-fluid position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="<?= Yii::$app->homeUrl ?>" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary"><i class="fa fa-hashtag me-2"></i><?= Html::encode(Yii::$app->name) ?></h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="<?= Yii::getAlias('@web') ?>/img/user.jpg" alt="" style="width: 40px; height: 40px;">
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0"><?= Yii::$app->user->isGuest ? 'Guest' : Yii::$app->user->identity->username ?></h6>
                        <span><?= Yii::$app->user->isGuest ? 'Visitor' : 'User' ?></span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <!-- TODO: Menu Dashboard com classe ativa dinâmica -->
                    <a href="<?= Yii::$app->homeUrl ?>" class="nav-item nav-link <?= $isDashboard ? 'active' : '' ?>">
                        <i class="fa fa-tachometer-alt me-2"></i>Dashboard
                    </a>

                    <!-- TODO: Menu Elements com classe ativa dinâmica -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?= $isElements ? 'active' : '' ?>" data-bs-toggle="dropdown">
                            <i class="fa fa-laptop me-2"></i>Elements
                        </a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="button.html" class="dropdown-item">Buttons</a>
                            <a href="typography.html" class="dropdown-item">Typography</a>
                            <a href="element.html" class="dropdown-item">Other Elements</a>
                        </div>
                    </div>

                    <!-- TODO: Menu Utilizadores com classe ativa dinâmica -->
                    <a href="<?= Yii::$app->urlManager->createUrl(['utilizador/index']) ?>"
                       class="nav-item nav-link <?= $isUtilizadores ? 'active' : '' ?>">
                        <i class="fa fa-users me-2"></i>Utilizadores
                    </a>

                    <!-- TODO: Adicionar lógica ativa para Forms -->
                    <a href="form.html" class="nav-item nav-link">
                        <i class="fa fa-keyboard me-2"></i>Forms
                    </a>

                    <!-- TODO: Adicionar lógica ativa para Tables -->
                    <a href="table.html" class="nav-item nav-link">
                        <i class="fa fa-table me-2"></i>Tables
                    </a>

                    <!-- TODO: Adicionar lógica ativa para Charts -->
                    <a href="chart.html" class="nav-item nav-link">
                        <i class="fa fa-chart-bar me-2"></i>Charts
                    </a>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="far fa-file-alt me-2"></i>Pages
                        </a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="signin.html" class="dropdown-item">Sign In</a>
                            <a href="signup.html" class="dropdown-item">Sign Up</a>
                            <a href="404.html" class="dropdown-item">404 Error</a>
                            <a href="blank.html" class="dropdown-item">Blank Page</a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->


        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="<?= Yii::$app->homeUrl ?>" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <form class="d-none d-md-flex ms-4">
                    <input class="form-control border-0" type="search" placeholder="Search">
                </form>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-envelope me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Message</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="<?= Yii::getAlias('@web') ?>/img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="<?= Yii::getAlias('@web') ?>/img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="<?= Yii::getAlias('@web') ?>/img/user.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item text-center">See all message</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-bell me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Notificatin</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">
                                <h6 class="fw-normal mb-0">Profile updated</h6>
                                <small>15 minutes ago</small>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <h6 class="fw-normal mb-0">New user added</h6>
                                <small>15 minutes ago</small>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <h6 class="fw-normal mb-0">Password changed</h6>
                                <small>15 minutes ago</small>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item text-center">See all notifications</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="<?= Yii::getAlias('@web') ?>/img/user.jpg" alt="" style="width: 40px; height: 40px;">
                            <span class="d-none d-lg-inline-flex"><?= Yii::$app->user->isGuest ? 'Guest' : Yii::$app->user->identity->username ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <?php if (!Yii::$app->user->isGuest): ?>
                                <a href="#" class="dropdown-item">My Profile</a>
                                <a href="#" class="dropdown-item">Settings</a>
                                <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline'])
                                . Html::submitButton(
                                    'Log Out',
                                    ['class' => 'dropdown-item border-0 bg-transparent']
                                )
                                . Html::endForm() ?>
                            <?php else: ?>
                                <a href="<?= Yii::$app->urlManager->createUrl(['site/login']) ?>" class="dropdown-item">Login</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->


            <!-- Main Content -->
            <main role="main" class="flex-shrink-0">
                <div class="container-fluid pt-4 px-4">
                    <!-- Breadcrumbs -->
                    <?= Breadcrumbs::widget([
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                    ]) ?>

                    <!-- Alert Widget -->
                    <?= Alert::widget() ?>

                    <!-- Yii Content -->
                    <?= $content ?>
                </div>
            </main>


            <!-- Footer Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="#"><?= Html::encode(Yii::$app->name) ?></a>, All Right Reserved.
                        </div>
                        <div class="col-12 col-sm-6 text-center text-sm-end">
                            <!--/*** This template is free as long as you keep the footer author's credit link/attribution link/backlink. If you'd like to use the template without the footer author's credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
                            Designed By <a href="https://htmlcodex.com">HTML Codex</a>
                            <br>
                            <small>Powered by <?= Yii::powered() ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer End -->
        </div>
        <!-- Content End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= Yii::getAlias('@web') ?>/lib/chart/chart.min.js"></script>
    <script src="<?= Yii::getAlias('@web') ?>/lib/easing/easing.min.js"></script>
    <script src="<?= Yii::getAlias('@web') ?>/lib/waypoints/waypoints.min.js"></script>
    <script src="<?= Yii::getAlias('@web') ?>/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="<?= Yii::getAlias('@web') ?>/lib/tempusdominus/js/moment.min.js"></script>
    <script src="<?= Yii::getAlias('@web') ?>/lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="<?= Yii::getAlias('@web') ?>/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="<?= Yii::getAlias('@web') ?>/js/main.js"></script>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>