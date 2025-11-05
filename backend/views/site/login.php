<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login';
?>

    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">

    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="" name="keywords">
        <meta content="" name="description">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?> - <?= Html::encode(Yii::$app->name) ?></title>

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

    <body>
    <?php $this->beginBody() ?>

    <div class="container-fluid position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Sign In Start -->
        <div class="container-fluid">
            <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <div class="bg-light rounded p-4 p-sm-5 my-4 mx-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <a href="<?= Yii::$app->homeUrl ?>" class="">
                                <h3 class="text-primary"><i class="fa fa-hashtag me-2"></i><?= Html::encode(Yii::$app->name) ?></h3>
                            </a>
                            <h3>Sign In</h3>
                        </div>

                        <?php $form = ActiveForm::begin([
                            'id' => 'login-form',
                            'fieldConfig' => [
                                'options' => ['class' => 'form-floating mb-3'],
                                'inputOptions' => ['class' => 'form-control'],
                                'labelOptions' => ['class' => 'form-label'],
                            ],
                        ]); ?>

                        <?= $form->field($model, 'username', [
                            'template' => '{input}{label}{error}'
                        ])->textInput([
                            'autofocus' => true,
                            'placeholder' => 'name@example.com',
                            'id' => 'floatingInput'
                        ])->label('Email address') ?>

                        <?= $form->field($model, 'password', [
                            'template' => '{input}{label}{error}'
                        ])->passwordInput([
                            'placeholder' => 'Password',
                            'id' => 'floatingPassword'
                        ])->label('Password') ?>

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <?= $form->field($model, 'rememberMe', [
                                'options' => ['class' => 'form-check mb-0'],
                                'inputOptions' => ['class' => 'form-check-input'],
                                'labelOptions' => ['class' => 'form-check-label'],
                            ])->checkbox() ?>
                            <a href="">Forgot Password</a>
                        </div>

                        <?= Html::submitButton('Sign In', [
                            'class' => 'btn btn-primary py-3 w-100 mb-4',
                            'name' => 'login-button'
                        ]) ?>

                        <?php ActiveForm::end(); ?>

                        <p class="text-center mb-0">Don't have an Account? <a href="">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Sign In End -->
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