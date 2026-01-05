<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Requisicao;

/**
 * Modelo de pesquisa para Requisições (Reservas)
 * Extende o modelo Requisicao para adicionar funcionalidades de pesquisa e filtragem
 * Inclui capacidade de pesquisar por sala, utilizador e bloco através de JOINs
 */
class RequisicaoSearch extends Requisicao
{
    /**
     * @var string Nome da sala para pesquisa
     * Atributo virtual que permite filtrar pelo nome da sala relacionada
     */
    public $sala_nome;

    /**
     * @var string Nome do utilizador para pesquisa
     * Atributo virtual que permite filtrar pelo nome do utilizador relacionado
     */
    public $user_name;

    /**
     * @var string Nome do bloco para pesquisa
     * Atributo virtual que permite filtrar pelo nome do bloco relacionado (através da sala)
     */
    public $bloco_nome;

    /**
     * Define regras de validação para os parâmetros de pesquisa
     * @return array Regras de validação
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'sala_id'], 'integer'],        // IDs devem ser inteiros
            [['status'], 'string'],                            // Status deve ser string
            [['dataInicio', 'dataFim', 'sala_nome', 'user_name', 'bloco_nome'], 'safe'], // Campos seguros para pesquisa
        ];
    }

    /**
     * Define os cenários disponíveis para este modelo de pesquisa
     * Herda os cenários da classe base Model
     * @return array Cenários disponíveis
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Cria o provedor de dados com os filtros aplicados
     * Inclui JOINs múltiplos para permitir filtragem por entidades relacionadas
     * Inclui conversão automática de datas de formato português para MySQL
     * @param array $params Parâmetros de pesquisa do formulário
     * @return ActiveDataProvider Provedor de dados com os resultados filtrados
     */
    public function search($params)
    {
        // Consulta base com JOINs múltiplos:
        // - INNER JOIN com sala (requer que exista sala)
        // - INNER JOIN com user (requer que exista utilizador)
        // - LEFT JOIN com bloco (pode não existir bloco associado)
        $query = Requisicao::find()
            ->joinWith(['sala', 'user'])                    // JOINs obrigatórios
            ->leftJoin('bloco', 'sala.bloco_id = bloco.id'); // JOIN opcional com bloco

        // Configuração do provedor de dados com ordenação personalizada
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['dataInicio' => SORT_DESC], // Ordenação padrão: data mais recente primeiro
                'attributes' => [
                    'id',          // Ordenação por ID
                    'dataInicio',  // Ordenação por data de início
                    'dataFim',     // Ordenação por data de fim
                    'status',      // Ordenação por status

                    // Mapeamento para ordenação por nome da sala (atributo virtual)
                    'sala_nome' => [
                        'asc' => ['sala.nome' => SORT_ASC],    // Ordenação ascendente
                        'desc' => ['sala.nome' => SORT_DESC],  // Ordenação descendente
                    ],

                    // Mapeamento para ordenação por nome do utilizador (atributo virtual)
                    'user_name' => [
                        'asc' => ['user.username' => SORT_ASC],   // Ordenação ascendente
                        'desc' => ['user.username' => SORT_DESC], // Ordenação descendente
                    ],

                    // Mapeamento para ordenação por nome do bloco (atributo virtual)
                    'bloco_nome' => [
                        'asc' => ['bloco.nome' => SORT_ASC],    // Ordenação ascendente
                        'desc' => ['bloco.nome' => SORT_DESC],  // Ordenação descendente
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => 20, // 20 registos por página
            ],
        ]);

        // Carrega os parâmetros de pesquisa no modelo
        $this->load($params);

        // Se a validação falhar, retorna o provedor de dados sem filtros
        if (!$this->validate()) {
            return $dataProvider;
        }

        // Aplica filtros exatos (WHERE com igualdade)
        $query->andFilterWhere([
            'requisicao.id' => $this->id,         // Filtro por ID exato
            'requisicao.user_id' => $this->user_id,    // Filtro por utilizador
            'requisicao.sala_id' => $this->sala_id,    // Filtro por sala
            'requisicao.status' => $this->status,      // Filtro por status
        ]);

        // Filtro por data de início específica - converte formato dd/mm/aaaa para aaaa-mm-dd
        if ($this->dataInicio) {
            $dataFormatada = $this->converterDataParaMySQL($this->dataInicio);
            if ($dataFormatada) {
                // Usa DATE() para comparar apenas a parte da data (ignorando hora)
                $query->andFilterWhere(['DATE(requisicao.dataInicio)' => $dataFormatada]);
            }
        }

        // Filtro por data de fim específica - converte formato dd/mm/aaaa para aaaa-mm-dd
        if ($this->dataFim) {
            $dataFormatada = $this->converterDataParaMySQL($this->dataFim);
            if ($dataFormatada) {
                // Usa DATE() para comparar apenas a parte da data (ignorando hora)
                $query->andFilterWhere(['DATE(requisicao.dataFim)' => $dataFormatada]);
            }
        }

        // Filtros por correspondência parcial (LIKE) em campos de texto
        $query->andFilterWhere(['like', 'sala.nome', $this->sala_nome])    // Filtro por nome da sala
        ->andFilterWhere(['like', 'user.username', $this->user_name])  // Filtro por nome do utilizador
        ->andFilterWhere(['like', 'bloco.nome', $this->bloco_nome]);   // Filtro por nome do bloco

        return $dataProvider;
    }

    /**
     * Converte data de formato português (dd/mm/aaaa) para formato MySQL (aaaa-mm-dd)
     * Suporta ambos os formatos como entrada
     * @param string $data Data no formato dd/mm/aaaa ou aaaa-mm-dd
     * @return string|null Data no formato aaaa-mm-dd ou null se inválida
     */
    private function converterDataParaMySQL($data)
    {
        if (empty($data)) {
            return null;
        }

        // Verificar se já está no formato MySQL (aaaa-mm-dd)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return $data; // Já está no formato correto
        }

        // Tentar converter de formato português (dd/mm/aaaa) para MySQL (aaaa-mm-dd)
        $partes = explode('/', $data);
        if (count($partes) === 3) {
            $dia = $partes[0];
            $mes = $partes[1];
            $ano = $partes[2];

            // Validar se são números válidos
            if (is_numeric($dia) && is_numeric($mes) && is_numeric($ano)) {
                // Garantir 2 dígitos para dia e mês (ex: 1 → 01)
                $dia = str_pad($dia, 2, '0', STR_PAD_LEFT);
                $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

                // Verificar se é uma data válida usando checkdate()
                if (checkdate((int)$mes, (int)$dia, (int)$ano)) {
                    return $ano . '-' . $mes . '-' . $dia; // Formato MySQL
                }
            }
        }

        // Se não foi possível converter, retorna null
        return null;
    }
}