<?php

namespace backend\tests\unit\models;

use common\models\Manutencao;

class ManutencaoTest extends \Codeception\Test\Unit
{
    public function testValidacaoDataInicioObrigatoria()
    {
        $manutencao = new Manutencao();

        $this->assertFalse($manutencao->validate());
        $this->assertArrayHasKey('dataInicio', $manutencao->errors);
    }

    public function testValidacaoEquipamentoOuSalaObrigatorio()
    {
        $manutencao = new Manutencao();
        $manutencao->dataInicio = '2024-01-01 10:00:00';

        // Deve falhar porque não tem equipamento nem sala
        $this->assertFalse($manutencao->validate());

        // Deve ter erros em ambos os campos
        $this->assertArrayHasKey('equipamento_id', $manutencao->errors);
        $this->assertArrayHasKey('sala_id', $manutencao->errors);
    }

    public function testMetodosEstaticos()
    {
        $opts = Manutencao::getStatusOptions();

        $this->assertIsArray($opts);
        $this->assertArrayHasKey(Manutencao::STATUS_PENDENTE, $opts);
        $this->assertArrayHasKey(Manutencao::STATUS_EM_CURSO, $opts);
        $this->assertArrayHasKey(Manutencao::STATUS_CONCLUIDA, $opts);

        $this->assertEquals('Pendente', $opts[Manutencao::STATUS_PENDENTE]);
        $this->assertEquals('Em Curso', $opts[Manutencao::STATUS_EM_CURSO]);
        $this->assertEquals('Concluída', $opts[Manutencao::STATUS_CONCLUIDA]);
    }
}