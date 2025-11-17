<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "manutencao".
 *
 * @property int $id
 * @property int $equipamento_id
 * @property int $user_id
 * @property int $sala_id
 * @property string $dataInicio
 * @property string $dataFim
 * @property string $descricao
 * @property string $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 * @property Equipamento $equipamento
 * @property Sala $sala
 * @property User $createdBy
 * @property User $updatedBy
 */
class Manutencao extends ActiveRecord
{
    const STATUS_PENDENTE = 'Pendente';
    const STATUS_EM_CURSO = 'Em Curso';
    const STATUS_CONCLUIDA = 'Concluída';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%manutencao}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['equipamento_id', 'dataInicio'], 'required'],
            [['equipamento_id', 'user_id', 'sala_id'], 'integer'],
            [['dataInicio', 'dataFim'], 'safe'],
            [['descricao'], 'string'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'default', 'value' => self::STATUS_PENDENTE],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['equipamento_id'], 'exist', 'skipOnError' => true, 'targetClass' => Equipamento::class, 'targetAttribute' => ['equipamento_id' => 'id']],
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
            'equipamento_id' => 'Equipamento',
            'user_id' => 'Técnico',
            'sala_id' => 'Sala',
            'dataInicio' => 'Data Início',
            'dataFim' => 'Data Fim',
            'descricao' => 'Descrição',
            'status' => 'Estado',
            'created_by' => 'Criado Por',
            'updated_by' => 'Atualizado Por',
            'created_at' => 'Data Criação',
            'updated_at' => 'Data Atualização',
        ];
    }

    /**
     * Gets query for [[User]].
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Equipamento]].
     */
    public function getEquipamento()
    {
        return $this->hasOne(Equipamento::class, ['id' => 'equipamento_id']);
    }

    /**
     * Gets query for [[Sala]].
     */
    public function getSala()
    {
        return $this->hasOne(Sala::class, ['id' => 'sala_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDENTE => 'Pendente',
            self::STATUS_EM_CURSO => 'Em Curso',
            self::STATUS_CONCLUIDA => 'Concluída',
        ];
    }

    /**
     * Get badge color for status
     */
    public function getStatusBadge()
    {
        $colors = [
            self::STATUS_PENDENTE => 'warning',
            self::STATUS_EM_CURSO => 'primary',
            self::STATUS_CONCLUIDA => 'success',
        ];

        return '<span class="badge bg-' . ($colors[$this->status] ?? 'secondary') . '">' . $this->status . '</span>';
    }

    /**
     * Check if maintenance is in progress
     */
    public function isInProgress()
    {
        return $this->status === self::STATUS_EM_CURSO;
    }

    /**
     * Check if maintenance is completed
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_CONCLUIDA;
    }

    /**
     * Get duration in hours
     */
    public function getDuracao()
    {
        if ($this->dataInicio && $this->dataFim) {
            $inicio = new \DateTime($this->dataInicio);
            $fim = new \DateTime($this->dataFim);
            $diff = $inicio->diff($fim);
            return $diff->h + ($diff->days * 24);
        }
        return null;
    }
}