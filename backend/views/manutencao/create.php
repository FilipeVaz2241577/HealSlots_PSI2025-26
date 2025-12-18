<?php

/** @var yii\web\View $this */
/** @var common\models\Manutencao $model */
/** @var array $tecnicosList */
/** @var array $equipamentosList */
/** @var array $salasList */
/** @var int|null $equipamento_id */
/** @var int|null $sala_id */

use yii\bootstrap5\Html;

$this->title = 'Nova Manutenção';
$this->params['breadcrumbs'][] = ['label' => 'Gestão de Manutenções', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="manutencao-create">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fa fa-arrow-left me-2"></i>Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <!-- Alerta informativo se veio de equipamento -->
            <?php if ($equipamento_id): ?>
                <div class="alert alert-info mb-4">
                    <h5 class="alert-heading">
                        <i class="fa fa-info-circle me-2"></i>Informação Importante
                    </h5>
                    <p class="mb-0">
                        Você está criando uma manutenção para um <strong>equipamento</strong>.
                        O equipamento já foi pré-selecionado, mas você pode adicionar uma sala se necessário.
                    </p>
                </div>
            <?php elseif ($sala_id): ?>
                <div class="alert alert-info mb-4">
                    <h5 class="alert-heading">
                        <i class="fa fa-info-circle me-2"></i>Informação Importante
                    </h5>
                    <p class="mb-0">
                        Você está criando uma manutenção para uma <strong>sala</strong>.
                        A sala já foi pré-selecionada, mas você pode adicionar um equipamento se necessário.
                    </p>
                </div>
            <?php endif; ?>

            <?= $this->render('_form', [
                'model' => $model,
                'tecnicosList' => $tecnicosList,
                'equipamentosList' => $equipamentosList,
                'salasList' => $salasList,
                'equipamento_id' => $equipamento_id,
                'sala_id' => $sala_id,
            ]) ?>
        </div>
    </div>
</div>