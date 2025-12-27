<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
<<<<<<< HEAD
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
=======
>>>>>>> origin/filipe

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
<<<<<<< HEAD
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
=======
>>>>>>> origin/filipe
 *
 * @property User $user
 * @property Equipamento $equipamento
 * @property Sala $sala
<<<<<<< HEAD
 * @property User $createdBy
 * @property User $updatedBy
=======
>>>>>>> origin/filipe
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
<<<<<<< HEAD
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
=======
    public function rules()
    {
        return [
            [['dataInicio'], 'required'],
>>>>>>> origin/filipe
            [['equipamento_id', 'user_id', 'sala_id'], 'integer'],
            [['dataInicio', 'dataFim'], 'safe'],
            [['descricao'], 'string'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'default', 'value' => self::STATUS_PENDENTE],
<<<<<<< HEAD
=======
            // Validar que pelo menos um (equipamento OU sala) está preenchido
            [['equipamento_id', 'sala_id'], 'validateEquipamentoOrSala', 'skipOnEmpty' => false],
>>>>>>> origin/filipe
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['equipamento_id'], 'exist', 'skipOnError' => true, 'targetClass' => Equipamento::class, 'targetAttribute' => ['equipamento_id' => 'id']],
            [['sala_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sala::class, 'targetAttribute' => ['sala_id' => 'id']],
        ];
    }

    /**
<<<<<<< HEAD
=======
     * Validação personalizada: Pelo menos equipamento OU sala deve ser preenchido
     */
    public function validateEquipamentoOrSala($attribute, $params, $validator)
    {
        if (empty($this->equipamento_id) && empty($this->sala_id)) {
            $this->addError('equipamento_id', 'Deve selecionar pelo menos um equipamento OU uma sala.');
            $this->addError('sala_id', 'Deve selecionar pelo menos um equipamento OU uma sala.');
        }
    }

    /**
>>>>>>> origin/filipe
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
<<<<<<< HEAD
            'created_by' => 'Criado Por',
            'updated_by' => 'Atualizado Por',
            'created_at' => 'Data Criação',
            'updated_at' => 'Data Atualização',
=======
>>>>>>> origin/filipe
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
<<<<<<< HEAD
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
=======
>>>>>>> origin/filipe
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
<<<<<<< HEAD
=======

    /**
     * Get the title for display
     */
    public function getTitle()
    {
        if ($this->equipamento) {
            return 'Manutenção do Equipamento: ' . $this->equipamento->equipamento;
        } elseif ($this->sala) {
            return 'Manutenção da Sala: ' . $this->sala->nome;
        }
        return 'Manutenção #' . $this->id;
    }

    /**
     * Get current location (sala) for equipment maintenance
     */
    public function getLocalizacao()
    {
        if ($this->sala) {
            return $this->sala->nome . ($this->sala->bloco ? ' (' . $this->sala->bloco->nome . ')' : '');
        } elseif ($this->equipamento) {
            $sala = $this->equipamento->getCurrentSala();
            if ($sala) {
                return $sala->nome . ($sala->bloco ? ' (' . $sala->bloco->nome . ')' : '');
            }
        }
        return 'Não localizado';
    }

    /**
     * Before save logic
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Se estiver concluindo a manutenção
            if (!$insert && $this->status === self::STATUS_CONCLUIDA && !$this->dataFim) {
                $this->dataFim = date('Y-m-d H:i:s');
            }

            return true;
        }
        return false;
    }

    /**
     * Get equipamentos que NÃO estão em manutenção ativa
     */
    public static function getEquipamentosDisponiveis()
    {
        // Buscar IDs de equipamentos que estão em manutenção ativa (Pendente ou Em Curso)
        $equipamentosEmManutencao = self::find()
            ->select('equipamento_id')
            ->where(['status' => [self::STATUS_PENDENTE, self::STATUS_EM_CURSO]])
            ->andWhere(['not', ['equipamento_id' => null]])
            ->column();

        // Buscar todos os equipamentos que NÃO estão na lista acima
        return Equipamento::find()
            ->where(['not in', 'id', $equipamentosEmManutencao])
            ->all();
    }

    /**
     * Get salas que NÃO estão em manutenção ativa
     */
    public static function getSalasDisponiveis()
    {
        // Buscar IDs de salas que estão em manutenção ativa (Pendente ou Em Curso)
        $salasEmManutencao = self::find()
            ->select('sala_id')
            ->where(['status' => [self::STATUS_PENDENTE, self::STATUS_EM_CURSO]])
            ->andWhere(['not', ['sala_id' => null]])
            ->column();

        // Buscar todas as salas que NÃO estão na lista acima
        return Sala::find()
            ->where(['not in', 'id', $salasEmManutencao])
            ->all();
    }
>>>>>>> origin/filipe
}