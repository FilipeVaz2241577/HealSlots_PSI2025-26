<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sala_equipamento".
 *
 * @property int $idSala
 * @property int $idEquipamento
 *
 * @property Sala $sala
 * @property Equipamento $equipamento
 */
class SalaEquipamento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sala_equipamento}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idSala', 'idEquipamento'], 'required'],
            [['idSala', 'idEquipamento'], 'integer'],
            [['idSala', 'idEquipamento'], 'unique', 'targetAttribute' => ['idSala', 'idEquipamento']],
            [['idSala'], 'exist', 'skipOnError' => true, 'targetClass' => Sala::class, 'targetAttribute' => ['idSala' => 'id']],
            [['idEquipamento'], 'exist', 'skipOnError' => true, 'targetClass' => Equipamento::class, 'targetAttribute' => ['idEquipamento' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idSala' => 'Sala',
            'idEquipamento' => 'Equipamento',
        ];
    }

    /**
     * Gets query for [[Sala]].
     */
    public function getSala()
    {
        return $this->hasOne(Sala::class, ['id' => 'idSala']);
    }

    /**
     * Gets query for [[Equipamento]].
     */
    public function getEquipamento()
    {
        return $this->hasOne(Equipamento::class, ['id' => 'idEquipamento']);
    }
<<<<<<< HEAD
=======

    /**
     * Remove all associations for a sala
     * @param int $salaId
     * @return int Number of deleted rows
     */
    public static function removeAllBySala($salaId)
    {
        return self::deleteAll(['idSala' => $salaId]);
    }

    /**
     * Remove all associations for an equipamento
     * @param int $equipamentoId
     * @return int Number of deleted rows
     */
    public static function removeAllByEquipamento($equipamentoId)
    {
        return self::deleteAll(['idEquipamento' => $equipamentoId]);
    }

    /**
     * Check if association exists
     * @param int $salaId
     * @param int $equipamentoId
     * @return bool
     */
    public static function associationExists($salaId, $equipamentoId)
    {
        return self::find()
            ->where(['idSala' => $salaId, 'idEquipamento' => $equipamentoId])
            ->exists();
    }

    /**
     * Get all equipamentos for a sala
     * @param int $salaId
     * @return Equipamento[]
     */
    public static function getEquipamentosBySala($salaId)
    {
        return Equipamento::find()
            ->innerJoin('sala_equipamento', 'equipamento.id = sala_equipamento.idEquipamento')
            ->where(['sala_equipamento.idSala' => $salaId])
            ->all();
    }

    /**
     * Get all salas for an equipamento
     * @param int $equipamentoId
     * @return Sala[]
     */
    public static function getSalasByEquipamento($equipamentoId)
    {
        return Sala::find()
            ->innerJoin('sala_equipamento', 'sala.id = sala_equipamento.idSala')
            ->where(['sala_equipamento.idEquipamento' => $equipamentoId])
            ->all();
    }
>>>>>>> origin/filipe
}