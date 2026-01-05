<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\User;
use yii\helpers\ArrayHelper;

/**
 * Modelo de pesquisa para Utilizadores
 * Representa o modelo por trás do formulário de pesquisa de `common\models\User`
 * Inclui filtros avançados por role (função) e intervalo de datas
 */
class UserSearch extends User
{
    /**
     * @var string Role (função) do utilizador para pesquisa
     * Atributo virtual que permite filtrar utilizadores pela sua função no sistema
     */
    public $role;

    /**
     * @var string Data de início do intervalo de criação para pesquisa
     * Permite filtrar utilizadores criados a partir de uma data específica
     */
    public $created_at_start;

    /**
     * @var string Data de fim do intervalo de criação para pesquisa
     * Permite filtrar utilizadores criados até uma data específica
     */
    public $created_at_end;

    /**
     * Define regras de validação para os parâmetros de pesquisa
     * @return array Regras de validação
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'], // ID e status devem ser inteiros
            [['username', 'email', 'role', 'created_at_start', 'created_at_end'], 'safe'], // Campos seguros para pesquisa
            [['created_at_start', 'created_at_end'], 'date', 'format' => 'php:Y-m-d'], // Datas no formato MySQL
        ];
    }

    /**
     * Define os labels (etiquetas) dos atributos para o formulário
     * Estende os labels do modelo pai com labels para atributos virtuais
     * @return array Labels dos atributos
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'role' => 'Função (Role)',                 // Label para campo de role
            'created_at_start' => 'Data de Criação (Início)', // Label para data inicial
            'created_at_end' => 'Data de Criação (Fim)',      // Label para data final
        ]);
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
     * Inclui filtros avançados por role e intervalo de datas
     * @param array $params Parâmetros de pesquisa do formulário
     * @return ActiveDataProvider Provedor de dados com os resultados filtrados
     */
    public function search($params)
    {
        // Consulta base - busca TODOS os utilizadores (ativos e inativos)
        $query = User::find();

        // Configuração do provedor de dados
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10, // 10 registos por página
            ],
            'sort' => [
                'defaultOrder' => [
                    'status' => SORT_DESC, // Utilizadores ativos primeiro (status = 10)
                    'id' => SORT_DESC      // IDs mais recentes primeiro
                ],
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
            'id' => $this->id,         // Filtro por ID exato
            'status' => $this->status, // Filtro por status exato
        ]);

        // Filtros por correspondência parcial (LIKE) em campos de texto
        $query->andFilterWhere(['like', 'username', $this->username]) // Filtro por username
        ->andFilterWhere(['like', 'email', $this->email]);        // Filtro por email

        // Filtro avançado por role (função) usando tabela auth_assignment
        if (!empty($this->role)) {
            // Subconsulta para obter IDs de utilizadores com a role específica
            $userIds = (new \yii\db\Query())
                ->select('user_id')
                ->from('{{%auth_assignment}}') // Tabela de atribuição de roles
                ->where(['item_name' => $this->role]) // Role específica
                ->column(); // Extrai array de IDs

            if (!empty($userIds)) {
                // Filtra apenas utilizadores com essa role
                $query->andWhere(['id' => $userIds]);
            } else {
                // Se não houver utilizadores com essa role, não mostra nenhum resultado
                $query->andWhere('1=0');
            }
        }

        // Filtro por intervalo de datas de criação (data de início)
        if ($this->created_at_start) {
            // Converte data para timestamp UNIX para comparação com campo created_at
            $query->andFilterWhere(['>=', 'created_at', strtotime($this->created_at_start . ' 00:00:00')]);
        }

        // Filtro por intervalo de datas de criação (data de fim)
        if ($this->created_at_end) {
            // Converte data para timestamp UNIX para comparação com campo created_at
            $query->andFilterWhere(['<=', 'created_at', strtotime($this->created_at_end . ' 23:59:59')]);
        }

        return $dataProvider;
    }
}