// backend/web/js/mqtt-notifications.js
// MQTT Notifications via WebSocket (Mosquitto + mqtt.js)

class MQTTNotifications {
    constructor() {
        this.notifications = [];
        this.maxNotifications = 50;

        this.badgeElement = document.getElementById('mqtt-badge');
        this.notificationsContainer = document.getElementById('mqtt-notifications-container');
        this.headerElement = document.getElementById('mqtt-header');
        this.clearAllButton = document.getElementById('mqtt-clear-all');

        this.client = null;
        this.init();
    }

    /* =========================
       INIT
       ========================= */
    init() {
        this.loadFromStorage();
        this.renderNotifications();
        this.updateBadge();
        this.connectToMQTT();

        if (this.clearAllButton) {
            this.clearAllButton.addEventListener('click', () => this.clearAllNotifications());
        }
    }

    /* =========================
       MQTT CONNECTION
       ========================= */
    connectToMQTT() {
        const protocol = window.location.protocol === 'https:' ? 'wss' : 'ws';
        const wsUrl = `${protocol}://localhost:9001/mqtt`;

        this.client = mqtt.connect(wsUrl, {
            clientId: 'yii2_browser_' + Math.random().toString(16).substr(2, 8),
            reconnectPeriod: 3000,
            clean: true
        });

        this.client.on('connect', () => this.onConnect());
        this.client.on('message', (topic, message) => this.onMessageArrived(topic, message));
        this.client.on('error', err => console.error('❌ MQTT erro:', err));
        this.client.on('close', () => console.warn('⚠️ MQTT desconectado'));
    }

    onConnect() {
        console.log('✅ Conectado ao broker MQTT (WebSocket)');

        this.client.subscribe([
            // EQUIPAMENTO
            'INSERT_EQUIPAMENTO',
            'UPDATE_EQUIPAMENTO',
            'DELETE_EQUIPAMENTO',

            // TIPO EQUIPAMENTO
            'INSERT_TIPO_EQUIPAMENTO',
            'UPDATE_TIPO_EQUIPAMENTO',
            'DELETE_TIPO_EQUIPAMENTO',

            // BLOCO
            'INSERT_BLOCO',
            'UPDATE_BLOCO',
            'DELETE_BLOCO',

            // MANUTENÇÃO
            'INSERT_MANUTENCAO',
            'UPDATE_MANUTENCAO',
            'DELETE_MANUTENCAO',

            // REQUISIÇÃO
            'INSERT_REQUISICAO',
            'UPDATE_REQUISICAO',
            'DELETE_REQUISICAO',
            'STATUS_CHANGED_REQUISICAO',

            // SALA
            'INSERT_SALA',
            'UPDATE_SALA',
            'DELETE_SALA',
            'ESTADO_CHANGED_SALA',

            // USER (UTILIZADOR)
            'INSERT_USER',
            'UPDATE_USER',
            'DELETE_USER',
            'STATUS_CHANGED_USER',
            'EMAIL_CHANGED_USER'
        ]);
    }

    onMessageArrived(topic, message) {
        const payload = message.toString();
        console.log('📨 MQTT:', topic, payload);

        this.addNotification(topic, payload);
        this.showToastNotification(topic, payload);
    }

    /* =========================
       NOTIFICATIONS
       ========================= */
    addNotification(topic, message) {
        this.notifications.unshift({
            id: Date.now(),
            topic,
            message,
            timestamp: new Date().toLocaleTimeString(),
            read: false
        });

        if (this.notifications.length > this.maxNotifications) {
            this.notifications.pop();
        }

        this.saveToStorage();
        this.renderNotifications();
        this.updateBadge();
    }

    renderNotifications() {
        if (!this.notificationsContainer) return;

        if (this.notifications.length === 0) {
            this.notificationsContainer.innerHTML = `
                <div class="text-center p-3 text-muted">
                    Aguardando eventos MQTT…
                </div>
            `;
            if (this.headerElement) this.headerElement.textContent = '(0)';
            return;
        }

        let html = '';

        this.notifications.slice(0, 10).forEach(n => {
            let icon = 'fa-info-circle';
            let title = 'Notificação';
            let typeClass = 'insert';
            let text = n.message;

            try {
                const json = JSON.parse(n.message);

                /* ===== TIPO EQUIPAMENTO ===== */
                if (n.topic.includes('TIPO_EQUIPAMENTO')) {
                    icon = 'fa-tag';

                    if (n.topic.includes('INSERT')) {
                        title = 'Novo Tipo de Equipamento';
                        typeClass = 'insert';
                    }
                    else if (n.topic.includes('UPDATE')) {
                        title = 'Tipo de Equipamento Atualizado';
                        typeClass = 'update';
                    }
                    else if (n.topic.includes('DELETE')) {
                        title = 'Tipo de Equipamento Removido';
                        typeClass = 'delete';
                    }

                    text = `${json.nome}`;
                    if (json.total_equipamentos !== undefined) {
                        text += ` | ${json.total_equipamentos} equipamento(s)`;
                    }
                }

                /* ===== USER (UTILIZADOR) ===== */
                else if (n.topic.includes('USER')) {
                    icon = 'fa-user';

                    if (n.topic === 'STATUS_CHANGED_USER') {
                        title = 'Estado de Utilizador Alterado';
                        typeClass = 'update';
                        text = `${json.username} - ${json.old_status_text} → ${json.new_status_text}`;
                        if (json.email) {
                            text += ` | Email: ${json.email}`;
                        }
                    }
                    else if (n.topic === 'EMAIL_CHANGED_USER') {
                        title = 'Email de Utilizador Alterado';
                        typeClass = 'update';
                        text = `${json.username} - Email alterado`;
                        text += ` | Novo: ${json.new_email}`;
                    }
                    else if (n.topic.includes('INSERT')) {
                        title = 'Novo Utilizador';
                        typeClass = 'insert';
                    }
                    else if (n.topic.includes('UPDATE')) {
                        title = 'Utilizador Atualizado';
                        typeClass = 'update';
                    }
                    else if (n.topic.includes('DELETE')) {
                        title = 'Utilizador Removido';
                        typeClass = 'delete';
                    }

                    if (!n.topic.includes('STATUS_CHANGED') && !n.topic.includes('EMAIL_CHANGED')) {
                        text = `${json.username} - ${json.status_text ?? ''}`;
                        if (json.email) {
                            text += ` | Email: ${json.email}`;
                        }
                        if (json.roles && json.roles.length > 0) {
                            text += ` | Função: ${json.roles.join(', ')}`;
                        }
                    }
                }

                /* ===== SALA ===== */
                else if (n.topic.includes('SALA')) {
                    icon = 'fa-door-closed';

                    if (n.topic === 'ESTADO_CHANGED_SALA') {
                        title = 'Estado de Sala Alterado';
                        typeClass = 'update';
                        text = `Sala "${json.nome}" - ${json.old_estado} → ${json.new_estado}`;
                        if (json.bloco_nome) {
                            text += ` | Bloco: ${json.bloco_nome}`;
                        }
                    }
                    else if (n.topic.includes('INSERT')) {
                        title = 'Nova Sala';
                        typeClass = 'insert';
                    }
                    else if (n.topic.includes('UPDATE')) {
                        title = 'Sala Atualizada';
                        typeClass = 'update';
                    }
                    else if (n.topic.includes('DELETE')) {
                        title = 'Sala Removida';
                        typeClass = 'delete';
                    }

                    if (!n.topic.includes('ESTADO_CHANGED')) {
                        text = `Sala "${json.nome}" - Estado: ${json.estado ?? ''}`;
                        if (json.bloco_nome) {
                            text += ` | Bloco: ${json.bloco_nome}`;
                        }
                    }
                }

                /* ===== REQUISIÇÃO ===== */
                else if (n.topic.includes('REQUISICAO')) {
                    icon = 'fa-calendar-alt';

                    if (n.topic === 'STATUS_CHANGED_REQUISICAO') {
                        title = 'Status de Requisição Alterado';
                        typeClass = 'update';
                        text = `Requisição #${json.id} - ${json.old_status} → ${json.new_status}`;
                        if (json.sala_nome) {
                            text += ` | Sala: ${json.sala_nome}`;
                        }
                    }
                    else if (n.topic.includes('INSERT')) {
                        title = 'Nova Requisição';
                        typeClass = 'insert';
                    }
                    else if (n.topic.includes('UPDATE')) {
                        title = 'Requisição Atualizada';
                        typeClass = 'update';
                    }
                    else if (n.topic.includes('DELETE')) {
                        title = 'Requisição Removida';
                        typeClass = 'delete';
                    }

                    if (!n.topic.includes('STATUS_CHANGED')) {
                        text = `Requisição #${json.id} - ${json.status ?? ''}`;
                        if (json.sala_nome) {
                            text += ` | Sala: ${json.sala_nome}`;
                        }
                        if (json.user_nome) {
                            text += ` | Utilizador: ${json.user_nome}`;
                        }
                    }
                }

                /* ===== MANUTENÇÃO ===== */
                else if (n.topic.includes('MANUTENCAO')) {
                    icon = 'fa-tools';

                    if (n.topic.includes('INSERT')) title = 'Nova Manutenção';
                    if (n.topic.includes('UPDATE')) title = 'Manutenção Atualizada';
                    if (n.topic.includes('DELETE')) {
                        title = 'Manutenção Removida';
                        typeClass = 'delete';
                    }

                    text = `ID: ${json.id} — ${json.status ?? ''}`;
                    if (json.equipamento_nome) {
                        text += ` | Equipamento: ${json.equipamento_nome}`;
                    } else if (json.sala_nome) {
                        text += ` | Sala: ${json.sala_nome}`;
                    }
                }

                /* ===== BLOCO ===== */
                else if (n.topic.includes('BLOCO')) {
                    icon = 'fa-building';

                    if (n.topic.includes('INSERT')) title = 'Novo Bloco';
                    if (n.topic.includes('UPDATE')) title = 'Bloco Atualizada';
                    if (n.topic.includes('DELETE')) {
                        title = 'Bloco Removido';
                        typeClass = 'delete';
                    }

                    text = json.nome ?? `ID: ${json.id}`;
                }

                /* ===== EQUIPAMENTO ===== */
                else if (n.topic.includes('EQUIPAMENTO')) {
                    icon = 'fa-desktop';

                    if (n.topic.includes('INSERT')) title = 'Novo Equipamento';
                    if (n.topic.includes('UPDATE')) title = 'Equipamento Atualizado';
                    if (n.topic.includes('DELETE')) {
                        title = 'Equipamento Removido';
                        typeClass = 'delete';
                    }

                    text = json.equipamento ?? `ID: ${json.id}`;
                }

            } catch (_) {
                // Se não for JSON, usar o texto original
            }

            html += `
                <div class="mqtt-notification ${typeClass}">
                    <div class="d-flex justify-content-between">
                        <div>
                            <i class="fas ${icon} mr-2"></i>
                            <strong>${title}</strong>
                        </div>
                        <small>${n.timestamp}</small>
                    </div>
                    <div class="mqtt-message">${text}</div>
                </div>
            `;
        });

        this.notificationsContainer.innerHTML = html;
        if (this.headerElement) {
            this.headerElement.textContent = `(${this.notifications.length})`;
        }
    }

    updateBadge() {
        const unread = this.notifications.filter(n => !n.read).length;
        if (this.badgeElement) {
            this.badgeElement.textContent = unread;
            this.badgeElement.style.display = unread > 0 ? 'inline-block' : 'none';
        }
    }

    /* =========================
       STORAGE & UI
       ========================= */
    saveToStorage() {
        localStorage.setItem('mqtt_notifications', JSON.stringify(this.notifications));
    }

    loadFromStorage() {
        const saved = localStorage.getItem('mqtt_notifications');
        if (saved) this.notifications = JSON.parse(saved);
    }

    clearAllNotifications() {
        if (!confirm('Limpar todas as notificações?')) return;

        this.notifications = [];
        this.saveToStorage();
        this.renderNotifications();
        this.updateBadge();

        if (typeof toastr !== 'undefined') {
            toastr.success('Notificações limpas');
        }
    }

    showToastNotification(topic, message) {
        if (typeof toastr === 'undefined') return;

        let title = topic.replace('_', ' ');
        let text = message;

        try {
            const json = JSON.parse(message);

            // Personalizar mensagens baseadas no tipo
            if (topic.includes('TIPO_EQUIPAMENTO')) {
                const prefix = topic.includes('INSERT') ? 'Novo' :
                    topic.includes('UPDATE') ? 'Atualizado' :
                        topic.includes('DELETE') ? 'Removido' : '';
                title = `${prefix} Tipo de Equipamento`;

                if (json.nome) {
                    text = `Tipo: ${json.nome}`;
                    if (json.total_equipamentos !== undefined) {
                        text += ` (${json.total_equipamentos} equipamentos)`;
                    }
                } else {
                    text = `ID: ${json.id}`;
                }
            }
            else if (topic === 'STATUS_CHANGED_USER') {
                title = 'Estado Alterado';
                text = `${json.username}: ${json.old_status_text} → ${json.new_status_text}`;
            }
            else if (topic === 'EMAIL_CHANGED_USER') {
                title = 'Email Alterado';
                text = `${json.username}: ${json.old_email} → ${json.new_email}`;
            }
            else if (topic === 'ESTADO_CHANGED_SALA') {
                title = 'Estado da Sala Alterado';
                text = `${json.nome}: ${json.old_estado} → ${json.new_estado}`;
                if (json.bloco_nome) {
                    text += ` (${json.bloco_nome})`;
                }
            }
            else if (topic === 'STATUS_CHANGED_REQUISICAO') {
                title = 'Status Alterado';
                text = `Requisição #${json.id}: ${json.old_status} → ${json.new_status}`;
                if (json.sala_nome) {
                    text += ` (${json.sala_nome})`;
                }
            }
            else if (topic.includes('USER')) {
                const prefix = topic.includes('INSERT') ? 'Novo' :
                    topic.includes('UPDATE') ? 'Atualizado' :
                        topic.includes('DELETE') ? 'Removido' : '';
                title = `${prefix} Utilizador`;

                if (json.username) {
                    text = `Utilizador: ${json.username}`;
                    if (json.status_text) {
                        text += ` (${json.status_text})`;
                    }
                } else {
                    text = `ID: ${json.id}`;
                }
            }
            else if (topic.includes('SALA')) {
                const prefix = topic.includes('INSERT') ? 'Nova' :
                    topic.includes('UPDATE') ? 'Atualizada' :
                        topic.includes('DELETE') ? 'Removida' : '';
                title = `${prefix} Sala`;

                if (json.nome) {
                    text = `Sala: ${json.nome} (Estado: ${json.estado})`;
                } else {
                    text = `ID: ${json.id}`;
                }
            }
            else if (topic.includes('REQUISICAO')) {
                const prefix = topic.includes('INSERT') ? 'Nova' :
                    topic.includes('UPDATE') ? 'Atualizada' :
                        topic.includes('DELETE') ? 'Removida' : '';
                title = `${prefix} Requisição`;

                if (json.sala_nome) {
                    text = `Sala: ${json.sala_nome}`;
                } else {
                    text = `ID: ${json.id}`;
                }
            }
            else if (topic.includes('EQUIPAMENTO')) {
                const prefix = topic.includes('INSERT') ? 'Novo' :
                    topic.includes('UPDATE') ? 'Atualizado' :
                        topic.includes('DELETE') ? 'Removido' : '';
                title = `${prefix} Equipamento`;

                if (json.equipamento) {
                    text = json.equipamento;
                } else {
                    text = `ID: ${json.id}`;
                }
            }
            else if (json.equipamento_nome) {
                text = json.equipamento_nome;
            }
            else if (json.sala_nome) {
                text = json.sala_nome;
            }
            else if (json.nome) {
                text = json.nome;
            }
            else if (json.username) {
                text = json.username;
            }
            else if (json.id) {
                text = `ID: ${json.id}`;
            }
        } catch (_) {}

        toastr.info(text, title, {
            timeOut: 4000,
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right'
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.mqttNotifications = new MQTTNotifications();
});