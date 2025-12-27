<?php

/** @var yii\web\View $this */
/** @var common\models\Sala $model */
<<<<<<< HEAD
=======
/** @var array $blocos */
>>>>>>> origin/filipe

$this->title = 'Editar Sala: ' . $model->nome;
$this->params['breadcrumbs'][] = ['label' => 'Salas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nome, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>
<div class="sala-update">
    <?= $this->render('_form', [
        'model' => $model,
        'blocos' => $blocos,
    ]) ?>
</div>