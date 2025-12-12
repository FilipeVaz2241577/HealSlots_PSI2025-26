<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "requisicao_equipamento".
 *
 * @property int $idRequisicao
 * @property int $idEquipamento
 *
 * @property Equipamento $idEquipamento0
 * @property Requisicao $idRequisicao0
 */
class RequisicaoEquipamento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'requisicao_equipamento';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idRequisicao', 'idEquipamento'], 'required'],
            [['idRequisicao', 'idEquipamento'], 'integer'],
            [['idRequisicao', 'idEquipamento'], 'unique', 'targetAttribute' => ['idRequisicao', 'idEquipamento']],
            [['idRequisicao'], 'exist', 'skipOnError' => true, 'targetClass' => Requisicao::class, 'targetAttribute' => ['idRequisicao' => 'id']],
            [['idEquipamento'], 'exist', 'skipOnError' => true, 'targetClass' => Equipamento::class, 'targetAttribute' => ['idEquipamento' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idRequisicao' => 'Id Requisicao',
            'idEquipamento' => 'Id Equipamento',
        ];
    }

    /**
     * Gets query for [[IdEquipamento0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdEquipamento0()
    {
        return $this->hasOne(Equipamento::class, ['id' => 'idEquipamento']);
    }

    /**
     * Gets query for [[IdRequisicao0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdRequisicao0()
    {
        return $this->hasOne(Requisicao::class, ['id' => 'idRequisicao']);
    }
}