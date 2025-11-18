<?php

/** @var yii\web\View $this */
/** @var common\models\Manutencao $model */

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
            <?= $this->render('_form', [
                'model' => $model,
                'tecnicosList' => $tecnicosList,
                'equipamentosList' => $equipamentosList,
                'salasList' => $salasList,
            ]) ?>
        </div>
    </div>
</div>