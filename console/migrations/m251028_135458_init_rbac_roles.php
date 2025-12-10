<?php

use yii\db\Migration;
use yii\base\Security;

/**
 * Class mXXXXXX_XXXXXX_init_rbac_roles
 */
class m251028_135458_init_rbac_roles extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // ========== CRIAR ROLES ==========
        $admin = $auth->createRole('Admin');
        $auth->add($admin);

        $tecnicoSaude = $auth->createRole('TecnicoSaude');
        $auth->add($tecnicoSaude);

        $assistenteManutencao = $auth->createRole('AssistenteManutencao');
        $auth->add($assistenteManutencao);

        // ========== PERMISSÕES POR TIPO DE ACESSO ==========

        // Back-Office Permissions
        $backOfficeAccess = $auth->createPermission('backOfficeAccess');
        $backOfficeAccess->description = 'Acesso ao Back-Office';
        $auth->add($backOfficeAccess);

        $manageUsers = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Gerir Utilizadores';
        $auth->add($manageUsers);

        $manageSystemParams = $auth->createPermission('manageSystemParams');
        $manageSystemParams->description = 'Gerir Parâmetros do Sistema';
        $auth->add($manageSystemParams);

        $viewReports = $auth->createPermission('viewReports');
        $viewReports->description = 'Visualizar Relatórios';
        $auth->add($viewReports);

        $manageMaintenance = $auth->createPermission('manageMaintenance');
        $manageMaintenance->description = 'Gerir Manutenções';
        $auth->add($manageMaintenance);

        $updateEquipmentStatus = $auth->createPermission('updateEquipmentStatus');
        $updateEquipmentStatus->description = 'Atualizar Estado de Equipamentos';
        $auth->add($updateEquipmentStatus);

        // Front-Office Permissions
        $frontOfficeAccess = $auth->createPermission('frontOfficeAccess');
        $frontOfficeAccess->description = 'Acesso ao Front-Office';
        $auth->add($frontOfficeAccess);

        $manageBookings = $auth->createPermission('manageBookings');
        $manageBookings->description = 'Gerir Marcações/Reservas';
        $auth->add($manageBookings);

        $viewResources = $auth->createPermission('viewResources');
        $viewResources->description = 'Consultar Recursos Disponíveis';
        $auth->add($viewResources);

        $manageRooms = $auth->createPermission('manageRooms');
        $manageRooms->description = 'Gerir Salas e Blocos';
        $auth->add($manageRooms);

        $reportIssues = $auth->createPermission('reportIssues');
        $reportIssues->description = 'Sinalizar Falhas/Problemas';
        $auth->add($reportIssues);

        // ========== ATRIBUIR PERMISSÕES CONFORME TABELA ==========

        // ADMIN - Acesso total a Back-Office e Front-Office
        $auth->addChild($admin, $backOfficeAccess);
        $auth->addChild($admin, $frontOfficeAccess);
        $auth->addChild($admin, $manageUsers);
        $auth->addChild($admin, $manageSystemParams);
        $auth->addChild($admin, $viewReports);
        $auth->addChild($admin, $manageMaintenance);
        $auth->addChild($admin, $updateEquipmentStatus);
        $auth->addChild($admin, $manageBookings);
        $auth->addChild($admin, $viewResources);
        $auth->addChild($admin, $manageRooms);
        $auth->addChild($admin, $reportIssues);

        // TÉCNICO DE SAÚDE - Apenas Front-Office
        $auth->addChild($tecnicoSaude, $frontOfficeAccess);
        $auth->addChild($tecnicoSaude, $manageBookings);
        $auth->addChild($tecnicoSaude, $viewResources);
        $auth->addChild($tecnicoSaude, $manageRooms);
        $auth->addChild($tecnicoSaude, $reportIssues);

        // ASSISTENTE DE MANUTENÇÃO - Apenas Back-Office
        $auth->addChild($assistenteManutencao, $backOfficeAccess);
        $auth->addChild($assistenteManutencao, $manageMaintenance);
        $auth->addChild($assistenteManutencao, $updateEquipmentStatus);
        $auth->addChild($assistenteManutencao, $reportIssues);
        $auth->addChild($assistenteManutencao, $viewResources);

        echo "RBAC roles criadas com sucesso!\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        echo "RBAC roles removidas.\n";
    }
}