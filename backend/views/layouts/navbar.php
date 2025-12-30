<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

<!-- NAVBAR -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- LEFT -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= Url::home() ?>" class="nav-link">Home</a>
        </li>
    </ul>

    <!-- RIGHT -->
    <ul class="navbar-nav ml-auto">
        <!-- MQTT NOTIFICATIONS -->
        <li class="nav-item" style="position: relative;">
            <a class="nav-link" href="#" id="mqtt-btn">
                <i class="fas fa-satellite"></i>
                <span id="mqtt-badge"
                      class="badge badge-danger"
                      style="display:none; position:absolute; top:0; right:0; transform:translate(25%,-25%);">
                    0
                </span>
            </a>

            <!-- DROPDOWN -->
            <div id="mqtt-dropdown"
                 style="display:none; position:fixed; top:56px; right:20px; width:400px;
                        background:#fff; border:1px solid #dee2e6; border-radius:4px;
                        box-shadow:0 10px 30px rgba(0,0,0,.2); z-index:999999;">

                <div style="padding:.75rem 1rem; background:#f8f9fa; border-bottom:1px solid #dee2e6;">
                    <i class="fas fa-broadcast-tower mr-1"></i>
                    Eventos do Sistema
                    <span id="mqtt-header" class="float-right">(0)</span>
                </div>

                <div id="mqtt-notifications-container"
                     style="max-height:400px; overflow-y:auto;">
                    <div class="text-center p-3 text-muted">
                        Aguardando eventos MQTT…
                    </div>
                </div>

                <div style="border-top:1px solid #dee2e6; padding:.5rem;">
                    <a href="#" id="mqtt-clear"
                       class="d-block p-2 text-dark" style="text-decoration:none;">
                        <i class="fas fa-trash mr-2"></i> Limpar tudo
                    </a>
                </div>
            </div>
        </li>

        <!-- BOTÃO DE LOGOUT -->
        <li class="nav-item">
            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex align-items-center']) ?>
            <?= Html::submitButton(
                    '<i class="fas fa-sign-out-alt"></i> Sair',
                    [
                            'class' => 'btn btn-link nav-link',
                            'style' => 'color: #6c757d; text-decoration: none;',
                            'onclick' => 'return confirm("Tem certeza que deseja sair?")'
                    ]
            ) ?>
            <?= Html::endForm() ?>
        </li>
    </ul>
</nav>

<!-- MODAL DETALHE -->
<div class="modal fade" id="mqttDetailModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhe do Evento</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="mqtt-detail-content"
                     style="background:#f8f9fa; padding:10px; max-height:400px; overflow:auto;"></pre>
            </div>
        </div>
    </div>
</div>

<!-- JS DA NAVBAR (SÓ UI, SEM MQTT, SEM AJAX) -->
<script>
    (function () {

        let open = false;

        const btn = document.getElementById('mqtt-btn');
        const dropdown = document.getElementById('mqtt-dropdown');
        const clearBtn = document.getElementById('mqtt-clear');

        if (!btn || !dropdown) return;

        // Toggle dropdown
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            open = !open;
            dropdown.style.display = open ? 'block' : 'none';

            if (open && window.mqttNotifications) {
                // marcar todas como lidas
                window.mqttNotifications.notifications.forEach(n => n.read = true);
                window.mqttNotifications.saveToStorage();
                window.mqttNotifications.updateBadge();
                window.mqttNotifications.renderNotifications();
            }
        });

        // Fechar ao clicar fora
        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                dropdown.style.display = 'none';
                open = false;
            }
        });

        // Limpar tudo
        clearBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (window.mqttNotifications) {
                window.mqttNotifications.clearAllNotifications();
                dropdown.style.display = 'none';
                open = false;
            }
        });

    })();
</script>

<style>
    /* ITENS */
    .mqtt-notification {
        padding: .75rem 1rem;
        border-left: 4px solid #007bff;
        border-bottom: 1px solid #f1f1f1;
        cursor: pointer;
    }

    .mqtt-notification.insert { border-left-color: #28a745; }
    .mqtt-notification.update { border-left-color: #ffc107; }
    .mqtt-notification.delete { border-left-color: #dc3545; }

    .mqtt-notification:hover {
        background: #f8f9fa;
    }
</style>