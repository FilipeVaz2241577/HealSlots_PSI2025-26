<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Manutencao;

/**
 * Modelo de pesquisa para Manutenções
 * Extende o modelo Manutencao para adicionar funcionalidades de pesquisa e filtragem
 * Inclui capacidade de pesquisar por equipamento, utilizador e sala através de JOINs
 */
class ManutencaoSearch extends Manutencao
{
    /**
     * @var string Nome do equipamento para pesquisa
     * Atributo virtual que permite filtrar pelo nome do equipamento relacionado
     */
    public $equipamentoNome;

    /**
     * @var string Nome do utilizador para pesquisa
     * Atributo virtual que permite filtrar pelo nome do utilizador relacionado
     */
    public $userNome;

    /**
     * @var string Nome da sala para pesquisa
     * Atributo virtual que permite filtrar pelo nome da sala relacionada
     */
    public $salaNome;

    /**
     * Define regras de validação para os parâmetros de pesquisa
     * @return array Regras de validação
     */
    public function rules()
    {
        return [
            [['id', 'equipamento_id', 'user_id', 'sala_id'], 'integer'], // IDs devem ser inteiros
            [['descricao', 'status', 'equipamentoNome', 'userNome', 'salaNome'], 'safe'], // Campos seguros para pesquisa
            [['dataInicio', 'dataFim'], 'safe'], // Campos de data seguros para pesquisa
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
     * @param array $params Parâmetros de pesquisa do formulário
     * @return ActiveDataProvider Provedor de dados com os resultados filtrados
     */
    public function search($params)
    {
        // Consulta base com JOINs múltiplos para incluir dados relacionados
        $query = Manutencao::find();
        $query->joinWith(['equipamento', 'user', 'sala']); // JOIN com três tabelas relacionadas

        // Configuração do provedor de dados
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC], // Ordenação padrão: ID mais recente primeiro
            ],
            'pagination' => [
                'pageSize' => 20, // 20 registos por página
            ],
        ]);

        // Configurar ordenação para campos virtuais (atributos de relacionamento)

        // Mapeamento para ordenação por nome do equipamento
        $dataProvider->sort->attributes['equipamentoNome'] = [
            'asc' => ['equipamento.equipamento' => SORT_ASC],   // Ordenação ascendente
            'desc' => ['equipamento.equipamento' => SORT_DESC], // Ordenação descendente
        ];

        // Mapeamento para ordenação por nome do utilizador
        $dataProvider->sort->attributes['userNome'] = [
            'asc' => ['user.username' => SORT_ASC],   // Ordenação ascendente
            'desc' => ['user.username' => SORT_DESC], // Ordenação descendente
        ];

        // Mapeamento para ordenação por nome da sala
        $dataProvider->sort->attributes['salaNome'] = [
            'asc' => ['sala.nome' => SORT_ASC],   // Ordenação ascendente
            'desc' => ['sala.nome' => SORT_DESC], // Ordenação descendente
        ];

        // Carrega os parâmetros de pesquisa no modelo
        $this->load($params);

        // Se a validação falhar, retorna o provedor de dados sem filtros
        if (!$this->validate()) {
            return $dataProvider;
        }

        // Aplica filtros à consulta, especificando tabelas para evitar ambiguidade
        // IMPORTANTE: Especificar 'manutencao.' prefixo para evitar conflitos de colunas
        $query->andFilterWhere([
            'manutencao.id' => $this->id,                 // Filtro por ID exato
            'manutencao.equipamento_id' => $this->equipamento_id, // Filtro por equipamento
            'manutencao.user_id' => $this->user_id,       // Filtro por utilizador
            'manutencao.sala_id' => $this->sala_id,       // Filtro por sala
            'manutencao.status' => $this->status,         // CORREÇÃO: Filtro por status usando prefixo
        ]);

        // Filtros por correspondência parcial (LIKE) em campos de texto
        $query->andFilterWhere(['like', 'manutencao.descricao', $this->descricao])      // Filtro por descrição
        ->andFilterWhere(['like', 'equipamento.equipamento', $this->equipamentoNome]) // Filtro por nome do equipamento
        ->andFilterWhere(['like', 'user.username', $this->userNome])                 // Filtro por nome do utilizador
        ->andFilterWhere(['like', 'sala.nome', $this->salaNome]);                    // Filtro por nome da sala

        // Filtros por intervalo de datas (data de início)
        if ($this->dataInicio) {
            $query->andFilterWhere(['>=', 'manutencao.dataInicio', $this->dataInicio]); // Data igual ou posterior
        }

        // Filtros por intervalo de datas (data de fim)
        if ($this->dataFim) {
            $query->andFilterWhere(['>=', 'manutencao.dataFim', $this->dataFim]); // Data igual ou posterior
        }

        return $dataProvider;
    }
}