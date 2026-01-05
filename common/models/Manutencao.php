<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use backend\mosquitto\phpMQTT;

/**
 * Esta é a classe de modelo para a tabela "manutencao".
 * Representa registos de manutenção no sistema.
 *
 * @property int $id - Identificador único da manutenção
 * @property int $equipamento_id - ID do equipamento (chave estrangeira, opcional)
 * @property int $user_id - ID do técnico responsável (chave estrangeira)
 * @property int $sala_id - ID da sala (chave estrangeira, opcional)
 * @property string $dataInicio - Data e hora de início da manutenção
 * @property string $dataFim - Data e hora de conclusão da manutenção
 * @property string $descricao - Descrição da manutenção/avaria
 * @property string $status - Estado atual da manutenção
 *
 * @property User $user - Relação com o utilizador/técnico responsável
 * @property Equipamento $equipamento - Relação com o equipamento (se aplicável)
 * @property Sala $sala - Relação com a sala (se aplicável)
 */
class Manutencao extends ActiveRecord
{
    // Constantes para os estados possíveis da manutenção
    const STATUS_PENDENTE = 'Pendente';
    const STATUS_EM_CURSO = 'Em Curso';
    const STATUS_CONCLUIDA = 'Concluída';

    /**
     * {@inheritdoc}
     * Retorna o nome da tabela associada a este modelo
     */
    public static function tableName()
    {
        return '{{%manutencao}}'; // Nome da tabela na base de dados (com prefixo se aplicável)
    }

    /**
     * {@inheritdoc}
     * Define as regras de validação para os atributos do modelo
     */
    public function rules()
    {
        return [
            // dataInicio é obrigatória
            [['dataInicio'], 'required'],
            // equipamento_id, user_id e sala_id devem ser inteiros
            [['equipamento_id', 'user_id', 'sala_id'], 'integer'],
            // dataInicio e dataFim são datas (safe significa que serão validadas como datas)
            [['dataInicio', 'dataFim'], 'safe'],
            // descricao é uma string (texto)
            [['descricao'], 'string'],
            // status é uma string com máximo de 20 caracteres
            [['status'], 'string', 'max' => 20],
            // Valor padrão para status é 'Pendente'
            [['status'], 'default', 'value' => self::STATUS_PENDENTE],
            // Validação personalizada: pelo menos um (equipamento OU sala) deve estar preenchido
            [['equipamento_id', 'sala_id'], 'validateEquipamentoOrSala', 'skipOnEmpty' => false],
            // Validação de existência das chaves estrangeiras
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['equipamento_id'], 'exist', 'skipOnError' => true, 'targetClass' => Equipamento::class, 'targetAttribute' => ['equipamento_id' => 'id']],
            [['sala_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sala::class, 'targetAttribute' => ['sala_id' => 'id']],
        ];
    }

    /**
     * Validação personalizada: Pelo menos equipamento OU sala deve ser preenchido
     * Garante que cada manutenção está associada a um equipamento OU uma sala
     *
     * @param string $attribute o atributo atualmente sendo validado
     * @param array $params parâmetros adicionais
     * @param \yii\validators\InlineValidator $validator o validador
     */
    public function validateEquipamentoOrSala($attribute, $params, $validator)
    {
        // Verifica se ambos os campos estão vazios
        if (empty($this->equipamento_id) && empty($this->sala_id)) {
            // Adiciona erro a ambos os campos
            $this->addError('equipamento_id', 'Deve selecionar pelo menos um equipamento OU uma sala.');
            $this->addError('sala_id', 'Deve selecionar pelo menos um equipamento OU uma sala.');
        }
    }

    /**
     * {@inheritdoc}
     * Define os rótulos para os atributos (usados em formulários)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID', // Rótulo para o ID
            'equipamento_id' => 'Equipamento', // Rótulo para o equipamento
            'user_id' => 'Técnico', // Rótulo para o técnico responsável
            'sala_id' => 'Sala', // Rótulo para a sala
            'dataInicio' => 'Data Início', // Rótulo para data de início
            'dataFim' => 'Data Fim', // Rótulo para data de conclusão
            'descricao' => 'Descrição', // Rótulo para descrição
            'status' => 'Estado', // Rótulo para estado
        ];
    }

    /**
     * Obtém a relação com o utilizador/técnico responsável
     * @return \yii\db\ActiveQuery - Query para obter o utilizador
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Obtém a relação com o equipamento (se aplicável)
     * @return \yii\db\ActiveQuery - Query para obter o equipamento
     */
    public function getEquipamento()
    {
        return $this->hasOne(Equipamento::class, ['id' => 'equipamento_id']);
    }

    /**
     * Obtém a relação com a sala (se aplicável)
     * @return \yii\db\ActiveQuery - Query para obter a sala
     */
    public function getSala()
    {
        return $this->hasOne(Sala::class, ['id' => 'sala_id']);
    }

    /**
     * Obtém as opções para o campo status
     * @return string[] - Array com os pares chave-valor dos estados
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
     * Obtém um badge colorido para o status da manutenção
     * @return string - HTML do badge com a cor apropriada
     */
    public function getStatusBadge()
    {
        // Mapeamento de cores para cada status
        $colors = [
            self::STATUS_PENDENTE => 'warning', // Amarelo para pendente
            self::STATUS_EM_CURSO => 'primary', // Azul para em curso
            self::STATUS_CONCLUIDA => 'success', // Verde para concluída
        ];

        // Retorna o badge HTML com a classe de cor apropriada
        return '<span class="badge bg-' . ($colors[$this->status] ?? 'secondary') . '">' . $this->status . '</span>';
    }

    /**
     * Verifica se a manutenção está em curso
     * @return bool - True se o status for 'Em Curso'
     */
    public function isInProgress()
    {
        return $this->status === self::STATUS_EM_CURSO;
    }

    /**
     * Verifica se a manutenção está concluída
     * @return bool - True se o status for 'Concluída'
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_CONCLUIDA;
    }

    /**
     * Calcula a duração da manutenção em horas
     * @return int|null - Duração em horas ou null se não houver dataFim
     */
    public function getDuracao()
    {
        if ($this->dataInicio && $this->dataFim) {
            // Cria objetos DateTime para calcular a diferença
            $inicio = new \DateTime($this->dataInicio);
            $fim = new \DateTime($this->dataFim);
            $diff = $inicio->diff($fim);

            // Calcula horas totais (horas + dias convertidos para horas)
            return $diff->h + ($diff->days * 24);
        }
        return null; // Retorna null se não houver data de conclusão
    }

    /**
     * Obtém um título descritivo para a manutenção
     * @return string - Título formatado para exibição
     */
    public function getTitle()
    {
        if ($this->equipamento) {
            return 'Manutenção do Equipamento: ' . $this->equipamento->equipamento;
        } elseif ($this->sala) {
            return 'Manutenção da Sala: ' . $this->sala->nome;
        }
        return 'Manutenção #' . $this->id; // Fallback caso não tenha equipamento nem sala
    }

    /**
     * Obtém a localização da manutenção
     * @return string - Descrição da localização (sala e bloco)
     */
    public function getLocalizacao()
    {
        if ($this->sala) {
            // Se a manutenção é diretamente numa sala
            return $this->sala->nome . ($this->sala->bloco ? ' (' . $this->sala->bloco->nome . ')' : '');
        } elseif ($this->equipamento) {
            // Se a manutenção é num equipamento, verifica onde o equipamento está
            $sala = $this->equipamento->getCurrentSala();
            if ($sala) {
                return $sala->nome . ($sala->bloco ? ' (' . $sala->bloco->nome . ')' : '');
            }
        }
        return 'Não localizado'; // Fallback caso não seja possível determinar a localização
    }

    /**
     * Lógica executada antes de salvar o registo
     * @return bool - True se pode prosseguir com o save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Se estiver a concluir a manutenção (não é inserção e status mudou para Concluída)
            if (!$insert && $this->status === self::STATUS_CONCLUIDA && !$this->dataFim) {
                // Define automaticamente a data de conclusão como o momento atual
                $this->dataFim = date('Y-m-d H:i:s');
            }

            return true; // Permite a operação de save
        }
        return false; // Impede a operação de save
    }

    /**
     * Obtém equipamentos que NÃO estão em manutenção ativa
     * @return array - Lista de equipamentos disponíveis para manutenção
     */
    public static function getEquipamentosDisponiveis()
    {
        // Buscar IDs de equipamentos que estão em manutenção ativa (Pendente ou Em Curso)
        $equipamentosEmManutencao = self::find()
            ->select('equipamento_id')
            ->where(['status' => [self::STATUS_PENDENTE, self::STATUS_EM_CURSO]])
            ->andWhere(['not', ['equipamento_id' => null]]) // Apenas equipamentos (não salas)
            ->column(); // Obtém um array de IDs

        // Buscar todos os equipamentos que NÃO estão na lista acima
        return Equipamento::find()
            ->where(['not in', 'id', $equipamentosEmManutencao])
            ->all(); // Retorna todos os objetos Equipamento
    }

    /**
     * Obtém salas que NÃO estão em manutenção ativa
     * @return array - Lista de salas disponíveis para manutenção
     */
    public static function getSalasDisponiveis()
    {
        // Buscar IDs de salas que estão em manutenção ativa (Pendente ou Em Curso)
        $salasEmManutencao = self::find()
            ->select('sala_id')
            ->where(['status' => [self::STATUS_PENDENTE, self::STATUS_EM_CURSO]])
            ->andWhere(['not', ['sala_id' => null]]) // Apenas salas (não equipamentos)
            ->column(); // Obtém um array de IDs

        // Buscar todas as salas que NÃO estão na lista acima
        return Sala::find()
            ->where(['not in', 'id', $salasEmManutencao])
            ->all(); // Retorna todos os objetos Sala
    }

    // ==============================================
    // MÉTODOS PARA COMUNICAÇÃO MQTT
    // ==============================================

    /**
     * {@inheritdoc}
     * Executa após salvar o registo (tanto inserção como atualização)
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Preparar dados da manutenção para envio MQTT
        $data = [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'status' => $this->status,
            'dataInicio' => $this->dataInicio,
            'dataFim' => $this->dataFim,
            'equipamento_id' => $this->equipamento_id,
            'user_id' => $this->user_id,
            'sala_id' => $this->sala_id,
            'timestamp' => date('Y-m-d H:i:s') // Timestamp atual para referência
        ];

        // Adicionar informações dos relacionamentos (se disponíveis)
        if ($this->equipamento) {
            $data['equipamento_nome'] = $this->equipamento->equipamento;
            $data['equipamento_numeroSerie'] = $this->equipamento->numeroSerie;
        }

        if ($this->user) {
            $data['user_nome'] = $this->user->username;
        }

        if ($this->sala) {
            $data['sala_nome'] = $this->sala->nome;
            if ($this->sala->bloco) {
                $data['bloco_nome'] = $this->sala->bloco->nome;
            }
        }

        $myJSON = json_encode($data); // Converter para JSON

        // Publicar no Mosquitto MQTT
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_MANUTENCAO", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_MANUTENCAO", $myJSON);
        }
    }

    /**
     * {@inheritdoc}
     * Executa após eliminar o registo
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // Preparar dados mínimos para notificar a eliminação
        $data = ['id' => $this->id];
        $myJSON = json_encode($data);

        // Publicar no tópico de eliminação
        $this->FazPublishNoMosquitto("DELETE_MANUTENCAO", $myJSON);
    }

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
            $client_id = "yii_manutencao_" . uniqid(); // ID único do cliente

            // Criar instância do cliente MQTT
            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            // Tentar conectar ao servidor MQTT (timeout de 5 segundos)
            if ($mqtt->connect(true, null, null, null, 5)) {
                // Publicar a mensagem no tópico especificado (QoS 0 = sem confirmação)
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close(); // Fechar a conexão

                // Log de sucesso
                error_log("✅ MQTT Manutenção: Publicado em $canal - ID: " . json_decode($msg)->id);

                // Log adicional em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_manutencao.log',
                    date('Y-m-d H:i:s') . " | $canal | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true; // Sucesso
            }

            // Se falhar a conexão
            error_log("❌ MQTT Manutenção: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            // Em caso de exceção
            error_log("❌ MQTT Manutenção Exception: " . $e->getMessage());
            return false;
        }
    }
}