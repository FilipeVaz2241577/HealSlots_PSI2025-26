<?php
use yii\bootstrap5\Html;
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= Yii::$app->homeUrl ?>" class="brand-link">
        <img src="<?= Yii::getAlias('@web') ?>/img/logo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"><?= Html::encode(Yii::$app->name) ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?=$assetDir?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= Yii::$app->user->isGuest ? 'Guest' : Yii::$app->user->identity->username ?></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => [
                    [
                        'label' => 'DASHBOARD',
                        'icon' => 'tachometer-alt',
                        'url' => ['/site/index']
                    ],
                    [
                        'label' => 'UTILIZADORES',
                        'icon' => 'users',
                        'url' => ['/utilizador/index'],
                        'badge' => '<span class="right badge badge-primary">Gestão</span>'
                    ],
                    [
                        'label' => 'REQUISIÇÕES',  // NOVO MENU SIMPLES
                        'icon' => 'calendar-alt',
                        'url' => ['/requisicao/index'],
                        'badge' => '<span class="right badge badge-success">Gestão</span>'
                    ],
                    [
                        'label' => 'MANUTENÇÕES',
                        'icon' => 'tools',
                        'url' => ['/manutencao/index'],
                        'badge' => '<span class="right badge badge-warning">Manutenção</span>'
                    ],
                    [
                        'label' => 'BLOCOS',
                        'icon' => 'cube',
                        'url' => ['/bloco/index'],
                        'badge' => '<span class="right badge badge-primary">Gestão</span>'
                    ],
                    [
                        'label' => 'SALAS',
                        'icon' => 'door-open',
                        'url' => ['/sala/index'],
                        'badge' => '<span class="right badge badge-primary">Gestão</span>'
                    ],
                    [
                        'label' => 'EQUIPAMENTOS',
                        'icon' => 'microchip',
                        'url' => ['/equipamento/index'],
                        'badge' => '<span class="right badge badge-primary">Gestão</span>'
                    ],
                    [
                        'label' => 'FERRAMENTAS',
                        'icon' => 'wrench',
                        'items' => [
                            ['label' => 'Gii', 'icon' => 'file-code', 'url' => ['/gii'], 'target' => '_blank'],
                            ['label' => 'Debug', 'icon' => 'bug', 'url' => ['/debug'], 'target' => '_blank'],
                        ]
                    ],
                ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>