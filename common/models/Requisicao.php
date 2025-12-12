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
            [['dataInicio', 'dataFim'], 'validateDatas'],
            [['sala_id', 'dataInicio', 'dataFim'], 'validateDisponibilidade', 'on' => ['create', 'update']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Utilizador',
            'sala_id' => 'Sala',
            'dataInicio' => 'Data de Início',
            'dataFim' => 'Data de Fim',
            'status' => 'Estado',
        ];
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
     * Gets query for [[Equipamentos]] via tabela de ligação.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEquipamentos()
    {
        // CORREÇÃO: Usar os nomes corretos das colunas da tabela
        return $this->hasMany(Equipamento::class, ['id' => 'idEquipamento'])
            ->viaTable('requisicao_equipamento', ['idRequisicao' => 'id']);
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
    public function getEstadoLabel()
    {
        return self::optsStatus()[$this->status] ?? 'Desconhecido';
    }

    /**
     * @return bool
     */
    public function isAtiva()
    {
        return $this->status === self::STATUS_ATIVA;
    }

    /**
     * @return bool
     */
    public function isConcluida()
    {
        return $this->status === self::STATUS_CONCLUIDA;
    }

    /**
     * @return bool
     */
    public function isCancelada()
    {
        return $this->status === self::STATUS_CANCELADA;
    }

    /**
     * Verifica se a requisição está ativa no momento atual
     * @return bool
     */
    public function isAtivaAgora()
    {
        $now = date('Y-m-d H:i:s');
        return $this->isAtiva() &&
            $this->dataInicio <= $now &&
            (!$this->dataFim || $this->dataFim >= $now);
    }

    /**
     * Verifica se há conflito com outra requisição
     * @param Requisicao $other
     * @return bool
     */
    public function conflitoCom($other)
    {
        if ($this->sala_id !== $other->sala_id) {
            return false;
        }

        // Verifica se os intervalos se sobrepõem
        $inicio1 = strtotime($this->dataInicio);
        $fim1 = $this->dataFim ? strtotime($this->dataFim) : null;
        $inicio2 = strtotime($other->dataInicio);
        $fim2 = $other->dataFim ? strtotime($other->dataFim) : null;

        // Se não tem data de fim, considera como contínua
        if ($fim1 === null) $fim1 = PHP_INT_MAX;
        if ($fim2 === null) $fim2 = PHP_INT_MAX;

        return !($fim1 <= $inicio2 || $fim2 <= $inicio1);
    }

    /**
     * Marca a requisição como concluída
     * @return bool
     */
    public function marcarComoConcluida()
    {
        $this->status = self::STATUS_CONCLUIDA;
        if (!$this->dataFim) {
            $this->dataFim = date('Y-m-d H:i:s');
        }

        if ($this->save(false, ['status', 'dataFim'])) {
            // Atualiza o estado da sala para Livre
            $this->atualizarEstadoSala(true);
            return true;
        }

        return false;
    }

    /**
     * Marca a requisição como cancelada
     * @return bool
     */
    public function marcarComoCancelada()
    {
        $this->status = self::STATUS_CANCELADA;

        if ($this->save(false, ['status'])) {
            // Atualiza o estado da sala para Livre
            $this->atualizarEstadoSala(true);
            return true;
        }

        return false;
    }

    /**
     * Atualiza o estado da sala quando a requisição é criada ou alterada
     * @param bool $forceUpdate Forçar atualização mesmo que o estado seja o mesmo
     * @return bool
     */
    public function atualizarEstadoSala($forceUpdate = false)
    {
        if (!$this->sala) {
            return false;
        }

        $sala = $this->sala;
        $novoEstadoSala = $this->determinarEstadoSala();

        // Só atualiza se for diferente ou se for forçado
        if ($forceUpdate || $sala->estado !== $novoEstadoSala) {
            $sala->estado = $novoEstadoSala;
            if (!$sala->save(false, ['estado'])) {
                Yii::error("Erro ao atualizar estado da sala {$sala->id} para {$novoEstadoSala}");
                return false;
            }
        }

        return true;
    }

    /**
     * Determina o estado da sala com base no status da requisição
     * @return string Estado da sala
     */
    public function determinarEstadoSala()
    {
        if ($this->isAtiva()) {
            return Sala::ESTADO_EM_USO;
        } elseif ($this->isConcluida()) {
            return Sala::ESTADO_LIVRE;
        } elseif ($this->isCancelada()) {
            return Sala::ESTADO_LIVRE;
        }

        return Sala::ESTADO_LIVRE; // Estado padrão
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Converter datetime-local para formato MySQL antes de salvar
            if ($this->dataInicio && strpos($this->dataInicio, 'T') !== false) {
                $this->dataInicio = date('Y-m-d H:i:s', strtotime($this->dataInicio));
            }

            if ($this->dataFim && strpos($this->dataFim, 'T') !== false) {
                $this->dataFim = date('Y-m-d H:i:s', strtotime($this->dataFim));
            }

            // Definir user_id se não estiver definido (para novas requisições)
            if ($insert && empty($this->user_id)) {
                $this->user_id = Yii::$app->user->id;
            }

            // Validar que a sala está Livre antes de criar uma nova requisição
            if ($insert) {
                $sala = Sala::findOne($this->sala_id);
                if (!$sala || $sala->estado !== Sala::ESTADO_LIVRE) {
                    $this->addError('sala_id', 'A sala não está disponível para requisição (estado: ' . ($sala->getEstadoLabel() ?? 'desconhecido') . ')');
                    return false;
                }
            }

            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Atualiza o estado da sala sempre que salvar uma requisição
        $this->atualizarEstadoSala();
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // Quando uma requisição é eliminada, a sala volta a estar livre
        if ($this->sala) {
            $sala = $this->sala;
            $sala->estado = Sala::ESTADO_LIVRE;
            $sala->save(false, ['estado']);
        }
    }

    /**
     * Converter formato MySQL para datetime-local após buscar do BD
     */
    public function afterFind()
    {
        parent::afterFind();

        // Converter para formato datetime-local para exibição no formulário
        if ($this->dataInicio) {
            $this->dataInicio = date('Y-m-d\TH:i', strtotime($this->dataInicio));
        }

        if ($this->dataFim) {
            $this->dataFim = date('Y-m-d\TH:i', strtotime($this->dataFim));
        }
    }

    /**
     * Valida se a data de fim é posterior à data de início
     * @return bool
     */
    public function validarDatas()
    {
        if (!$this->dataInicio) {
            return true;
        }

        if ($this->dataFim) {
            $inicio = strtotime($this->dataInicio);
            $fim = strtotime($this->dataFim);

            return $fim > $inicio;
        }

        return true;
    }

    /**
     * Valida se a sala está disponível no período solicitado
     * @return bool
     */
    public function validarDisponibilidade()
    {
        if (!$this->sala_id || !$this->dataInicio) {
            return false;
        }

        // Verificar se a sala existe e está livre
        $sala = Sala::findOne($this->sala_id);
        if (!$sala || $sala->estado !== Sala::ESTADO_LIVRE) {
            return false;
        }

        // Verificar se o bloco está ativo
        if (!$sala->bloco || $sala->bloco->estado !== 'ativo') {
            return false;
        }

        // Converter dataInicio para formato MySQL para a consulta
        $dataInicioMySQL = date('Y-m-d H:i:s', strtotime($this->dataInicio));
        $dataFimMySQL = $this->dataFim ? date('Y-m-d H:i:s', strtotime($this->dataFim)) : null;

        $query = Requisicao::find()
            ->where(['sala_id' => $this->sala_id])
            ->andWhere(['status' => 'Ativa'])
            ->andWhere(['or',
                ['between', 'dataInicio', $dataInicioMySQL, $dataFimMySQL],
                ['between', 'dataFim', $dataInicioMySQL, $dataFimMySQL],
                ['and',
                    ['<=', 'dataInicio', $dataInicioMySQL],
                    ['>=', 'dataFim', $dataFimMySQL]
                ],
                ['and',
                    ['>=', 'dataInicio', $dataInicioMySQL],
                    ['<=', 'dataFim', $dataFimMySQL]
                ]
            ]);

        // Excluir a própria requisição em caso de atualização
        if (!$this->isNewRecord) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        return $query->count() === 0;
    }

    /**
     * Adiciona regras de validação de datas
     */
    public function validateDatas($attribute, $params)
    {
        if (!$this->validarDatas()) {
            $this->addError('dataFim', 'A data de fim deve ser posterior à data de início.');
        }
    }

    /**
     * Adiciona regras de validação de disponibilidade
     */
    public function validateDisponibilidade($attribute, $params)
    {
        if (!$this->validarDisponibilidade()) {
            $this->addError('sala_id', 'A sala não está disponível no período solicitado.');
        }
    }
}