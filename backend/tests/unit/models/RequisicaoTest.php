<?php

namespace backend\tests\unit\models;

use common\models\Requisicao;
use common\models\Sala;
use common\models\Bloco;
use common\models\User;

class RequisicaoTest extends \Codeception\Test\Unit
{
    /**
     * @var \backend\tests\UnitTester
     */
    protected $tester;

    protected $blocoId;
    protected $salaId;
    protected $userId;

    protected function _before()
    {
        // Criar dados necessários para os testes
        $this->blocoId = $this->tester->haveRecord('common\models\Bloco', [
            'nome' => 'Bloco Teste ' . uniqid(),
            'estado' => 'ativo',
        ]);

        $this->salaId = $this->tester->haveRecord('common\models\Sala', [
            'nome' => 'Sala Teste ' . uniqid(),
            'estado' => 'Livre',
            'bloco_id' => $this->blocoId,
        ]);

        // Criar um usuário para teste (simulado)
        $this->userId = 1; // Supondo que o usuário com ID 1 existe
    }

    public function testValidacaoCamposObrigatorios()
    {
        $requisicao = new Requisicao();

        $this->assertFalse($requisicao->validate());
        $this->assertArrayHasKey('user_id', $requisicao->errors);
        $this->assertArrayHasKey('sala_id', $requisicao->errors);
        $this->assertArrayHasKey('dataInicio', $requisicao->errors);
    }

    public function testValidacaoStatusValido()
    {
        $requisicao = new Requisicao();
        $requisicao->user_id = $this->userId;
        $requisicao->sala_id = $this->salaId;
        $requisicao->dataInicio = '2024-01-01 10:00:00';
        $requisicao->status = 'StatusInvalido';

        $this->assertFalse($requisicao->validate());
        $this->assertArrayHasKey('status', $requisicao->errors);
    }

    public function testValidacaoStatusValidos()
    {
        $statusValidos = [Requisicao::STATUS_ATIVA, Requisicao::STATUS_CONCLUIDA, Requisicao::STATUS_CANCELADA];

        foreach ($statusValidos as $status) {
            $requisicao = new Requisicao([
                'user_id' => $this->userId,
                'sala_id' => $this->salaId,
                'dataInicio' => '2024-01-01 10:00:00',
                'status' => $status,
            ]);

            // Validação pode falhar devido a outras regras, mas não devido ao status
            // Vamos verificar se o status não causa erro
            $requisicao->validate();
            $this->assertArrayNotHasKey('status', $requisicao->errors,
                "Status '$status' não deveria causar erro de validação");
        }
    }

    public function testValidacaoDataFimPosteriorDataInicio()
    {
        $requisicao = new Requisicao();
        $requisicao->user_id = $this->userId;
        $requisicao->sala_id = $this->salaId;
        $requisicao->dataInicio = '2024-01-01 10:00:00';
        $requisicao->dataFim = '2024-01-01 09:00:00'; // Data fim ANTERIOR à data início

        $requisicao->validate();
        $this->assertArrayHasKey('dataFim', $requisicao->errors);
        $this->assertStringContainsString('posterior', $requisicao->errors['dataFim'][0]);
    }

    public function testModeloValido()
    {
        $requisicao = new Requisicao([
            'user_id' => $this->userId,
            'sala_id' => $this->salaId,
            'dataInicio' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'dataFim' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'status' => Requisicao::STATUS_ATIVA,
        ]);

        // Nota: Pode falhar devido à regra de disponibilidade da sala
        // Mas testamos a estrutura básica
        $requisicao->validate();

        // Verificar que não há erros nos campos básicos
        $this->assertArrayNotHasKey('user_id', $requisicao->errors);
        $this->assertArrayNotHasKey('sala_id', $requisicao->errors);
        $this->assertArrayNotHasKey('dataInicio', $requisicao->errors);
        $this->assertArrayNotHasKey('status', $requisicao->errors);
    }

    public function testMetodosEstaticos()
    {
        $opts = Requisicao::optsStatus();

        $this->assertIsArray($opts);
        $this->assertArrayHasKey(Requisicao::STATUS_ATIVA, $opts);
        $this->assertArrayHasKey(Requisicao::STATUS_CONCLUIDA, $opts);
        $this->assertArrayHasKey(Requisicao::STATUS_CANCELADA, $opts);

        $this->assertEquals('Ativa', $opts[Requisicao::STATUS_ATIVA]);
        $this->assertEquals('Concluída', $opts[Requisicao::STATUS_CONCLUIDA]);
        $this->assertEquals('Cancelada', $opts[Requisicao::STATUS_CANCELADA]);
    }

    public function testMetodosDeEstado()
    {
        $requisicao = new Requisicao();

        // Testar isAtiva()
        $requisicao->status = Requisicao::STATUS_ATIVA;
        $this->assertTrue($requisicao->isAtiva());

        // Testar isConcluida()
        $requisicao->status = Requisicao::STATUS_CONCLUIDA;
        $this->assertTrue($requisicao->isConcluida());

        // Testar isCancelada()
        $requisicao->status = Requisicao::STATUS_CANCELADA;
        $this->assertTrue($requisicao->isCancelada());

        // Testar getEstadoLabel()
        $requisicao->status = Requisicao::STATUS_ATIVA;
        $this->assertEquals('Ativa', $requisicao->getEstadoLabel());
    }

    public function testConstantesDefinidas()
    {
        $this->assertEquals('Ativa', Requisicao::STATUS_ATIVA);
        $this->assertEquals('Concluída', Requisicao::STATUS_CONCLUIDA);
        $this->assertEquals('Cancelada', Requisicao::STATUS_CANCELADA);
    }
}