<?php

namespace common\models;

use Yii;
use backend\mosquitto\phpMQTT;

/**
 * Esta é a classe de modelo para a tabela "requisicao".
 * Representa as requisições de utilização de salas no sistema.
 *
 * @property int $id - Identificador único da requisição
 * @property int $user_id - ID do utilizador que fez a requisição (chave estrangeira)
 * @property int $sala_id - ID da sala requisitada (chave estrangeira)
 * @property string $dataInicio - Data e hora de início da requisição
 * @property string|null $dataFim - Data e hora de término da requisição (opcional)
 * @property string|null $status - Estado atual da requisição
 *
 * @property Sala $sala - Relação com a sala requisitada
 * @property User $user - Relação com o utilizador que fez a requisição
 */
class Requisicao extends \yii\db\ActiveRecord
{
    /**
     * Valores do campo ENUM para o status
     */
    const STATUS_ATIVA = 'Ativa';
    const STATUS_CONCLUIDA = 'Concluída';
    const STATUS_CANCELADA = 'Cancelada';

    /**
     * {@inheritdoc}
     * Retorna o nome da tabela associada a este modelo
     */
    public static function tableName()
    {
        return 'requisicao'; // Nome da tabela na base de dados
    }

    /**
     * {@inheritdoc}
     * Define as regras de validação para os atributos do modelo
     */
    public function rules()
    {
        return [
            // Define o valor padrão para dataFim como null
            [['dataFim'], 'default', 'value' => null],
            // Define o valor padrão para status como 'Ativa'
            [['status'], 'default', 'value' => 'Ativa'],
            // user_id, sala_id e dataInicio são obrigatórios
            [['user_id', 'sala_id', 'dataInicio'], 'required'],
            // user_id e sala_id devem ser inteiros
            [['user_id', 'sala_id'], 'integer'],
            // dataInicio e dataFim são datas (safe significa que serão validadas como datas)
            [['dataInicio', 'dataFim'], 'safe'],
            // status é uma string
            [['status'], 'string'],
            // status deve ser um dos valores permitidos no ENUM
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            // Validação de existência das chaves estrangeiras
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['sala_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sala::class, 'targetAttribute' => ['sala_id' => 'id']],
            // Validações personalizadas
            [['dataInicio', 'dataFim'], 'validateDatas'], // Valida relação entre datas
            [['sala_id', 'dataInicio', 'dataFim'], 'validateDisponibilidade', 'on' => ['create', 'update']], // Valida disponibilidade da sala
        ];
    }

    /**
     * {@inheritdoc}
     * Define os rótulos para os atributos (usados em formulários)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID', // Rótulo para o ID
            'user_id' => 'Utilizador', // Rótulo para o utilizador
            'sala_id' => 'Sala', // Rótulo para a sala
            'dataInicio' => 'Data de Início', // Rótulo para data de início
            'dataFim' => 'Data de Fim', // Rótulo para data de término
            'status' => 'Estado', // Rótulo para estado
        ];
    }

    /**
     * Obtém a relação com a sala requisitada
     * @return \yii\db\ActiveQuery - Query para obter a sala
     */
    public function getSala()
    {
        return $this->hasOne(Sala::class, ['id' => 'sala_id']);
    }

    /**
     * Obtém a relação com o utilizador que fez a requisição
     * @return \yii\db\ActiveQuery - Query para obter o utilizador
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Obtém a relação com os equipamentos associados à requisição (via tabela de ligação)
     * @return \yii\db\ActiveQuery - Query para obter os equipamentos
     */
    public function getEquipamentos()
    {
        // CORREÇÃO: Usar os nomes corretos das colunas da tabela de ligação
        return $this->hasMany(Equipamento::class, ['id' => 'idEquipamento'])
            ->viaTable('requisicao_equipamento', ['idRequisicao' => 'id']);
    }

    /**
     * Obtém a relação com os ID dos equipamentos associados
     * @return \yii\db\ActiveQuery - Query para obter os IDs dos equipamentos
     */
    public function getIdEquipamentos()
    {
        return $this->hasMany(Equipamento::class, ['id' => 'idEquipamento'])->viaTable('requisicao_equipamento', ['idRequisicao' => 'id']);
    }

    /**
     * Obtém a relação com os registos de associação equipamento-requisição
     * @return \yii\db\ActiveQuery - Query para obter as associações
     */
    public function getRequisicaoEquipamentos()
    {
        return $this->hasMany(RequisicaoEquipamento::class, ['idRequisicao' => 'id']);
    }

    /**
     * Obtém os rótulos para os valores do ENUM do status
     * @return string[] - Array com os pares chave-valor do status
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_ATIVA => 'Ativa', // Rótulo para requisição ativa
            self::STATUS_CONCLUIDA => 'Concluída', // Rótulo para requisição concluída
            self::STATUS_CANCELADA => 'Cancelada', // Rótulo para requisição cancelada
        ];
    }

    /**
     * Obtém o rótulo do status atual
     * @return string - Rótulo do status ou 'Desconhecido' se não existir
     */
    public function getEstadoLabel()
    {
        return self::optsStatus()[$this->status] ?? 'Desconhecido';
    }

    /**
     * Verifica se a requisição está ativa
     * @return bool - True se o status for 'Ativa'
     */
    public function isAtiva()
    {
        return $this->status === self::STATUS_ATIVA;
    }

    /**
     * Verifica se a requisição está concluída
     * @return bool - True se o status for 'Concluída'
     */
    public function isConcluida()
    {
        return $this->status === self::STATUS_CONCLUIDA;
    }

    /**
     * Verifica se a requisição está cancelada
     * @return bool - True se o status for 'Cancelada'
     */
    public function isCancelada()
    {
        return $this->status === self::STATUS_CANCELADA;
    }

    /**
     * Verifica se a requisição está ativa no momento atual
     * @return bool - True se estiver dentro do período da requisição
     */
    public function isAtivaAgora()
    {
        $now = date('Y-m-d H:i:s'); // Data e hora atuais
        return $this->isAtiva() &&
            $this->dataInicio <= $now && // Já começou
            (!$this->dataFim || $this->dataFim >= $now); // Não terminou ou ainda não tem data de fim
    }

    /**
     * Verifica se há conflito com outra requisição (sobreposição de horários na mesma sala)
     * @param Requisicao $other - Outra requisição para comparar
     * @return bool - True se houver conflito/sobreposição
     */
    public function conflitoCom($other)
    {
        // Só pode haver conflito se for a mesma sala
        if ($this->sala_id !== $other->sala_id) {
            return false;
        }

        // Converte as datas para timestamps para comparação
        $inicio1 = strtotime($this->dataInicio);
        $fim1 = $this->dataFim ? strtotime($this->dataFim) : null;
        $inicio2 = strtotime($other->dataInicio);
        $fim2 = $other->dataFim ? strtotime($other->dataFim) : null;

        // Se não tem data de fim, considera como contínua (valor muito grande)
        if ($fim1 === null) $fim1 = PHP_INT_MAX;
        if ($fim2 === null) $fim2 = PHP_INT_MAX;

        // Verifica se os intervalos NÃO se sobrepõem (se não se sobrepõem, retorna false)
        // Dois intervalos não se sobrepõem se um termina antes do outro começar
        return !($fim1 <= $inicio2 || $fim2 <= $inicio1);
    }

    /**
     * Marca a requisição como concluída
     * @return bool - True se a operação for bem-sucedida
     */
    public function marcarComoConcluida()
    {
        $this->status = self::STATUS_CONCLUIDA;

        // Se não tiver data de fim, define como o momento atual
        if (!$this->dataFim) {
            $this->dataFim = date('Y-m-d H:i:s');
        }

        // Salva apenas os campos status e dataFim (skip validation)
        if ($this->save(false, ['status', 'dataFim'])) {
            // Atualiza o estado da sala para Livre
            $this->atualizarEstadoSala(true);
            return true;
        }

        return false;
    }

    /**
     * Marca a requisição como cancelada
     * @return bool - True se a operação for bem-sucedida
     */
    public function marcarComoCancelada()
    {
        $this->status = self::STATUS_CANCELADA;

        // Salva apenas o campo status (skip validation)
        if ($this->save(false, ['status'])) {
            // Atualiza o estado da sala para Livre
            $this->atualizarEstadoSala(true);
            return true;
        }

        return false;
    }

    /**
     * Atualiza o estado da sala com base no status da requisição
     * @param bool $forceUpdate - Forçar atualização mesmo que o estado seja o mesmo
     * @return bool - True se a atualização for bem-sucedida
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
     * @return string - Estado da sala (EM_USO ou LIVRE)
     */
    public function determinarEstadoSala()
    {
        if ($this->isAtiva()) {
            return Sala::ESTADO_EM_USO; // Sala em uso durante requisição ativa
        } elseif ($this->isConcluida()) {
            return Sala::ESTADO_LIVRE; // Sala livre após conclusão
        } elseif ($this->isCancelada()) {
            return Sala::ESTADO_LIVRE; // Sala livre após cancelamento
        }

        return Sala::ESTADO_LIVRE; // Estado padrão (fallback)
    }

    /**
     * {@inheritdoc}
     * Executa antes de salvar o registo
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Converter formato datetime-local para formato MySQL antes de salvar
            if ($this->dataInicio && strpos($this->dataInicio, 'T') !== false) {
                $this->dataInicio = date('Y-m-d H:i:s', strtotime($this->dataInicio));
            }

            if ($this->dataFim && strpos($this->dataFim, 'T') !== false) {
                $this->dataFim = date('Y-m-d H:i:s', strtotime($this->dataFim));
            }

            // Definir user_id automaticamente se não estiver definido (para novas requisições)
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
     * Executa após salvar o registo (tanto inserção como atualização)
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Atualiza o estado da sala sempre que salvar uma requisição
        $this->atualizarEstadoSala();

        // ==============================================
        // CÓDIGO MQTT PARA REQUISIÇÃO
        // ==============================================

        // Preparar dados da requisição para envio MQTT
        $data = [
            'id' => $this->id,
            'status' => $this->status,
            'dataInicio' => $this->dataInicio,
            'dataFim' => $this->dataFim,
            'user_id' => $this->user_id,
            'sala_id' => $this->sala_id,
            'timestamp' => date('Y-m-d H:i:s') // Timestamp atual
        ];

        // Adicionar informações dos relacionamentos (se disponíveis)
        if ($this->sala) {
            $data['sala_nome'] = $this->sala->nome;
            if ($this->sala->bloco) {
                $data['bloco_nome'] = $this->sala->bloco->nome;
            }
        }

        if ($this->user) {
            $data['user_nome'] = $this->user->username;
            $data['user_email'] = $this->user->email;
        }

        $myJSON = json_encode($data); // Converter para JSON

        // Publicar no Mosquitto MQTT baseado no tipo de operação
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_REQUISICAO", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_REQUISICAO", $myJSON);

            // Notificação específica para mudança de status
            if (isset($changedAttributes['status'])) {
                $oldStatus = $changedAttributes['status'];
                $newStatus = $this->status;

                $statusData = [
                    'id' => $this->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'dataInicio' => $this->dataInicio,
                    'sala_id' => $this->sala_id,
                    'timestamp' => date('Y-m-d H:i:s')
                ];

                if ($this->sala) {
                    $statusData['sala_nome'] = $this->sala->nome;
                }

                $statusJSON = json_encode($statusData);
                $this->FazPublishNoMosquitto("STATUS_CHANGED_REQUISICAO", $statusJSON);
            }
        }
    }

    /**
     * {@inheritdoc}
     * Executa após eliminar o registo
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

        // ==============================================
        // CÓDIGO MQTT PARA EXCLUSÃO DE REQUISIÇÃO
        // ==============================================

        $data = [
            'id' => $this->id,
            'dataInicio' => $this->dataInicio,
            'sala_id' => $this->sala_id
        ];

        if ($this->sala) {
            $data['sala_nome'] = $this->sala->nome;
        }

        $myJSON = json_encode($data);
        $this->FazPublishNoMosquitto("DELETE_REQUISICAO", $myJSON);
    }

    /**
     * Executa após buscar o registo da base de dados
     * Converte formato MySQL para datetime-local para exibição em formulários
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
     * @return bool - True se a validação passar
     */
    public function validarDatas()
    {
        if (!$this->dataInicio) {
            return true;
        }

        if ($this->dataFim) {
            $inicio = strtotime($this->dataInicio);
            $fim = strtotime($this->dataFim);

            return $fim > $inicio; // Data fim deve ser posterior à data início
        }

        return true; // Se não tem data fim, é válido (requisição contínua)
    }

    /**
     * Valida se a sala está disponível no período solicitado
     * @return bool - True se a sala estiver disponível
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

        // Verificar se o bloco da sala está ativo
        if (!$sala->bloco || $sala->bloco->estado !== 'ativo') {
            return false;
        }

        // Converter dataInicio para formato MySQL para a consulta
        $dataInicioMySQL = date('Y-m-d H:i:s', strtotime($this->dataInicio));
        $dataFimMySQL = $this->dataFim ? date('Y-m-d H:i:s', strtotime($this->dataFim)) : null;

        // Consulta para verificar conflitos de horário
        $query = Requisicao::find()
            ->where(['sala_id' => $this->sala_id])
            ->andWhere(['status' => 'Ativa']) // Apenas requisições ativas causam conflito
            ->andWhere(['or',
                // Intervalo do novo dentro de um existente
                ['between', 'dataInicio', $dataInicioMySQL, $dataFimMySQL],
                // Intervalo do novo contém um existente
                ['between', 'dataFim', $dataInicioMySQL, $dataFimMySQL],
                // Novo começa antes e termina depois de um existente
                ['and',
                    ['<=', 'dataInicio', $dataInicioMySQL],
                    ['>=', 'dataFim', $dataFimMySQL]
                ],
                // Novo está completamente dentro de um existente
                ['and',
                    ['>=', 'dataInicio', $dataInicioMySQL],
                    ['<=', 'dataFim', $dataFimMySQL]
                ]
            ]);

        // Excluir a própria requisição em caso de atualização
        if (!$this->isNewRecord) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        return $query->count() === 0; // Disponível se não houver conflitos
    }

    /**
     * Validação personalizada para as datas
     * @param string $attribute - O atributo sendo validado
     * @param array $params - Parâmetros adicionais
     */
    public function validateDatas($attribute, $params)
    {
        if (!$this->validarDatas()) {
            $this->addError('dataFim', 'A data de fim deve ser posterior à data de início.');
        }
    }

    /**
     * Validação personalizada para disponibilidade da sala
     * @param string $attribute - O atributo sendo validado
     * @param array $params - Parâmetros adicionais
     */
    public function validateDisponibilidade($attribute, $params)
    {
        if (!$this->validarDisponibilidade()) {
            $this->addError('sala_id', 'A sala não está disponível no período solicitado.');
        }
    }

    // ==============================================
    // MÉTODO PARA COMUNICAÇÃO MQTT
    // ==============================================

    /**
     * Publica uma mensagem no servidor Mosquitto MQTT
     * @param string $canal - Nome do tópico/canal MQTT
     * @param string $msg - Mensagem a publicar (em formato JSON)
     * @return bool - True se a publicação for bem-sucedida
     */
    public function FazPublishNoMosquitto($canal, $msg)
    {
        try {
            // Caminho ABSOLUTO para o ficheiro phpMQTT.php
            $phpMQTTPath = Yii::getAlias('@backend') . '/mosquitto/phpMQTT.php';

            // Verificar se o ficheiro existe
            if (!file_exists($phpMQTTPath)) {
                error_log("MQTT ERRO: Arquivo não encontrado: $phpMQTTPath");
                return false;
            }

            // Incluir a biblioteca MQTT
            require_once $phpMQTTPath;

            // Configurações do servidor MQTT
            $server = "127.0.0.1"; // Endereço do servidor Mosquitto (localhost)
            $port = 1883; // Porta padrão do MQTT
            $client_id = "yii_requisicao_" . uniqid(); // ID único do cliente

            // Criar instância do cliente MQTT
            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            // Tentar conectar ao servidor MQTT (timeout de 5 segundos)
            if ($mqtt->connect(true, null, null, null, 5)) {
                // Publicar a mensagem no tópico especificado (QoS 0 = sem confirmação)
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close(); // Fechar a conexão

                // Log de sucesso
                error_log("✅ MQTT Requisição: Publicado em $canal - ID: " . json_decode($msg)->id);

                // Log adicional em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_requisicao.log',
                    date('Y-m-d H:i:s') . " | $canal | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true; // Sucesso
            }

            // Se falhar a conexão
            error_log("❌ MQTT Requisição: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            // Em caso de exceção
            error_log("❌ MQTT Requisição Exception: " . $e->getMessage());
            return false;
        }
    }
}