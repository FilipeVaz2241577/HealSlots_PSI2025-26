<?php
/** @var yii\web\View $this */

use hail812\adminlte\widgets\Alert;
use hail812\adminlte\widgets\SmallBox;

$this->title = 'Dashboard - HealSlots';
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?= Alert::widget([
                'type' => 'info',
                'body' => '<h3><i class="fas fa-heartbeat"></i> Bem-vindo ao HealSlots!</h3>Sistema de gestão de utilizadores e funcionalidades',
            ]) ?>
        </div>
    </div>

    <!-- Estatísticas do HealSlots -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $userCount ?? '0',
                'text' => 'Total Utilizadores',
                'icon' => 'fas fa-users',
                'theme' => 'info'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $activeUsers ?? '0',
                'text' => 'Utilizadores Ativos',
                'icon' => 'fas fa-user-check',
                'theme' => 'success'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $adminCount ?? '0',
                'text' => 'Administradores',
                'icon' => 'fas fa-user-shield',
                'theme' => 'warning'
            ]) ?>
        </div>
        <div class="col-lg-3 col-6">
            <?= SmallBox::widget([
                'title' => $todayLogins ?? '0',
                'text' => 'Logins Hoje',
                'icon' => 'fas fa-sign-in-alt',
                'theme' => 'primary'
            ]) ?>
        </div>
    </div>

    <!-- Conteúdo específico -->
    <div class="row">
        <div class="col-md-6">
            <?= \hail812\adminlte\widgets\Callout::widget([
                'type' => 'success',
                'head' => 'Gestão de Utilizadores',
                'body' => 'Gerir todos os utilizadores do sistema e suas permissões'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= \hail812\adminlte\widgets\Callout::widget([
                'type' => 'info',
                'head' => 'Configurações do Sistema',
                'body' => 'Configurar parâmetros e funcionalidades da aplicação'
            ]) ?>
        </div>
    </div>
</div>