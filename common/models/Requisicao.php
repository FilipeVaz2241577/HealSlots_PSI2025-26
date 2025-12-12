<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "requisicao".
 *
 * @property int $id
 * @property int $user_id
 * @property int $sala_id
 * @property string $dataInicio
 * @property string|null $dataFim
 * @property string|null $status
 *
 * @property Equipamento[] $idEquipamentos
 * @property RequisicaoEquipamento[] $requisicaoEquipamentos
 * @property Sala $sala
 * @property User $user
 */
class Requisicao extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_ATIVA = 'Ativa';
    const STATUS_CONCLUIDA = 'Concluída';
    const STATUS_CANCELADA = 'Cancelada';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'requisicao';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dataFim'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'Ativa'],
            [['user_id', 'sala_id', 'dataInicio'], 'required'],
            [['user_id', 'sala_id'], 'integer'],
            [['dataInicio', 'dataFim'], 'safe'],
            [['status'], 'string'],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['sala_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sala::class, 'targetAttribute' => ['sala_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'sala_id' => 'Sala ID',
            'dataInicio' => 'Data Inicio',
            'dataFim' => 'Data Fim',
            'status' => 'Status',
        ];
    }

    /**
     * Gets query for [[IdEquipamentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdEquipamentos()
    {
        return $this->hasMany(Equipamento::class, ['id' => 'idEquipamento'])->viaTable('requisicao_equipamento', ['idRequisicao' => 'id']);
    }

    /**
     * Gets query for [[RequisicaoEquipamentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequisicaoEquipamentos()
    {
        return $this->hasMany(RequisicaoEquipamento::class, ['idRequisicao' => 'id']);
    }

    /**
     * Gets query for [[Sala]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSala()
    {
        return $this->hasOne(Sala::class, ['id' => 'sala_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }


    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_ATIVA => 'Ativa',
            self::STATUS_CONCLUIDA => 'Concluída',
            self::STATUS_CANCELADA => 'Cancelada',
        ];
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusAtiva()
    {
        return $this->status === self::STATUS_ATIVA;
    }

    public function setStatusToAtiva()
    {
        $this->status = self::STATUS_ATIVA;
    }

    /**
     * @return bool
     */
    public function isStatusConcluida()
    {
        return $this->status === self::STATUS_CONCLUIDA;
    }

    public function setStatusToConcluida()
    {
        $this->status = self::STATUS_CONCLUIDA;
    }

    /**
     * @return bool
     */
    public function isStatusCancelada()
    {
        return $this->status === self::STATUS_CANCELADA;
    }

    public function setStatusToCancelada()
    {
        $this->status = self::STATUS_CANCELADA;
    }
}
