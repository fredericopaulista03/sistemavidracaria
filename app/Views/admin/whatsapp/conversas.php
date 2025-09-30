<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">💬 Conversas WhatsApp</h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary me-2" onclick="syncConversas()" id="syncBtn">
                        <i class="fas fa-sync-alt"></i> Sincronizar
                    </button>
                    <span class="badge bg-primary"><?= $totalConversas ?> Conversas</span>
                </div>
            </div>
            <div class="card-body">
                <!-- Status da Conexão -->
                <div class="alert alert-info d-flex align-items-center mb-3">
                    <div class="me-2">
                        <?php if ($connectionStatus['success']): ?>
                        <i class="fas fa-check-circle text-success"></i>
                        <?php else: ?>
                        <i class="fas fa-times-circle text-danger"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <small>
                            <?php if ($connectionStatus['success']): ?>
                            Conectado à Evolution API
                            <?php else: ?>
                            Desconectado: <?= $connectionStatus['error'] ?? 'Erro de conexão' ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-6">
                        <div class="text-center p-3 border rounded bg-light">
                            <div class="h4 mb-1 text-primary"><?= $totalConversas ?></div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 border rounded bg-light">
                            <div class="h4 mb-1 text-warning"><?= $conversasAtivas ?></div>
                            <small class="text-muted">Aguardando</small>
                        </div>
                    </div>
                </div>

                <!-- Barra de Busca -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Buscar por nome ou telefone..."
                            id="searchInput">
                    </div>
                </div>

                <!-- Lista de Conversas -->
                <div class="conversas-list" id="conversasList">
                    <?php if (!empty($conversas)): ?>
                    <?php foreach ($conversas as $conversa): ?>
                    <div class="conversa-item p-3 border-bottom" style="cursor: pointer;"
                        onclick="selecionarConversa('<?= $conversa->numero ?>')">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold"><?= esc($conversa->nome) ?></h6>
                                <p class="mb-1 text-muted small"><?= esc($conversa->ultima_mensagem) ?></p>
                                <small
                                    class="text-muted"><?= date('d/m/Y H:i', strtotime($conversa->ultima_atualizacao)) ?></small>
                            </div>
                            <div class="ms-2">
                                <?php if ($conversa->nao_lidas > 0): ?>
                                <span class="badge bg-danger"><?= $conversa->nao_lidas ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-comments fa-2x mb-2"></i>
                        <p>Nenhuma conversa encontrada</p>
                        <small>Clique em "Sincronizar" para buscar conversas</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Coluna da Direita - Área da Conversa -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">💬 Conversa</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" id="area-conversa">
                <div class="text-center text-muted">
                    <i class="fas fa-comments fa-3x mb-3"></i>
                    <h5>Selecione uma conversa</h5>
                    <p>Escolha uma conversa da lista para começar a conversar</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.conversa-item {
    transition: background-color 0.2s;
}

.conversa-item:hover {
    background-color: #f8f9fa;
}

.conversa-item.active {
    background-color: #e3f2fd;
    border-left: 3px solid #0d6efd;
}

.conversas-list {
    max-height: 500px;
    overflow-y: auto;
}
</style>

<script>
// Função para sincronizar conversas
function syncConversas() {
    const btn = document.getElementById('syncBtn');
    const originalText = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sincronizando...';
    btn.disabled = true;

    fetch('<?= site_url('whatsapp/sync') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na rede: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Resposta da sincronização:', data);

            if (data.success) {
                showAlert('success', data.message || 'Conversas sincronizadas com sucesso!');
                // Recarrega a página após 2 segundos para mostrar os novos dados
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showAlert('error', data.error || 'Erro ao sincronizar conversas');
            }
        })
        .catch(error => {
            console.error('Erro na sincronização:', error);
            showAlert('error', 'Erro na sincronização: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
}

// Função para selecionar conversa
function selecionarConversa(numero) {
    // Remove a classe active de todos os itens
    document.querySelectorAll('.conversa-item').forEach(item => {
        item.classList.remove('active');
    });

    // Adiciona a classe active ao item clicado
    event.currentTarget.classList.add('active');

    // Carrega a conversa
    carregarConversa(numero);
}

// Função para carregar conversa
function carregarConversa(numero) {
    const areaConversa = document.getElementById('area-conversa');
    areaConversa.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p>Carregando conversa...</p>
        </div>
    `;

    fetch(`<?= site_url('whatsapp/conversa/') ?>${numero}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                areaConversa.innerHTML = `
                <div class="conversa-detalhes">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>${numero}</h5>
                        <span class="badge bg-success">Online</span>
                    </div>
                    
                    <div class="mensagens-container" style="height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; background-color: #f8f9fa;">
                        ${data.messages.map(msg => `
                            <div class="message ${msg.from_me ? 'my-message' : 'other-message'} mb-2">
                                <div class="card ${msg.from_me ? 'bg-primary text-white' : 'bg-light'}">
                                    <div class="card-body p-2">
                                        <p class="card-text mb-1">${msg.mensagem}</p>
                                        <small class="${msg.from_me ? 'text-white-50' : 'text-muted'}">
                                            ${msg.formatted_time || ''}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="input-group mt-3">
                        <input type="text" class="form-control" placeholder="Digite sua mensagem..." id="messageInput">
                        <button class="btn btn-primary" type="button" onclick="enviarMensagem('${numero}')">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            `;
            } else {
                areaConversa.innerHTML = `
                <div class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>Erro ao carregar conversa</p>
                </div>
            `;
            }
        })
        .catch(error => {
            console.error('Erro ao carregar conversa:', error);
            areaConversa.innerHTML = `
            <div class="text-center text-danger">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <p>Erro ao carregar conversa</p>
                <small>${error.message}</small>
            </div>
        `;
        });
}

// Função para enviar mensagem
function enviarMensagem(numero) {
    const input = document.getElementById('messageInput');
    const mensagem = input.value.trim();

    if (!mensagem) return;

    fetch('<?= site_url('whatsapp/send') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                numero: numero,
                mensagem: mensagem
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                // Recarrega a conversa para mostrar a nova mensagem
                carregarConversa(numero);
            } else {
                showAlert('error', data.error || 'Erro ao enviar mensagem');
            }
        })
        .catch(error => {
            showAlert('error', 'Erro ao enviar mensagem: ' + error.message);
        });
}

// Função para mostrar alertas
function showAlert(type, message) {
    // Remove alertas existentes
    const existingAlerts = document.querySelectorAll('.alert-dismissible');
    existingAlerts.forEach(alert => alert.remove());

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);

    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Busca em tempo real
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.conversa-item');

    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>

<?= $this->endSection() ?><?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">💬 Conversas WhatsApp</h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary me-2" onclick="syncConversas()" id="syncBtn">
                        <i class="fas fa-sync-alt"></i> Sincronizar
                    </button>
                    <span class="badge bg-primary"><?= $totalConversas ?> Conversas</span>
                </div>
            </div>
            <div class="card-body">
                <!-- Status da Conexão -->
                <div class="alert alert-info d-flex align-items-center mb-3">
                    <div class="me-2">
                        <?php if ($connectionStatus['success']): ?>
                        <i class="fas fa-check-circle text-success"></i>
                        <?php else: ?>
                        <i class="fas fa-times-circle text-danger"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <small>
                            <?php if ($connectionStatus['success']): ?>
                            Conectado à Evolution API
                            <?php else: ?>
                            Desconectado: <?= $connectionStatus['error'] ?? 'Erro de conexão' ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-6">
                        <div class="text-center p-3 border rounded bg-light">
                            <div class="h4 mb-1 text-primary"><?= $totalConversas ?></div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 border rounded bg-light">
                            <div class="h4 mb-1 text-warning"><?= $conversasAtivas ?></div>
                            <small class="text-muted">Aguardando</small>
                        </div>
                    </div>
                </div>

                <!-- Barra de Busca -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Buscar por nome ou telefone..."
                            id="searchInput">
                    </div>
                </div>

                <!-- Lista de Conversas -->
                <div class="conversas-list" id="conversasList">
                    <?php if (!empty($conversas)): ?>
                    <?php foreach ($conversas as $conversa): ?>
                    <div class="conversa-item p-3 border-bottom" style="cursor: pointer;"
                        onclick="selecionarConversa('<?= $conversa->numero ?>')">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold"><?= esc($conversa->nome) ?></h6>
                                <p class="mb-1 text-muted small"><?= esc($conversa->ultima_mensagem) ?></p>
                                <small
                                    class="text-muted"><?= date('d/m/Y H:i', strtotime($conversa->ultima_atualizacao)) ?></small>
                            </div>
                            <div class="ms-2">
                                <?php if ($conversa->nao_lidas > 0): ?>
                                <span class="badge bg-danger"><?= $conversa->nao_lidas ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-comments fa-2x mb-2"></i>
                        <p>Nenhuma conversa encontrada</p>
                        <small>Clique em "Sincronizar" para buscar conversas</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Coluna da Direita - Área da Conversa -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">💬 Conversa</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" id="area-conversa">
                <div class="text-center text-muted">
                    <i class="fas fa-comments fa-3x mb-3"></i>
                    <h5>Selecione uma conversa</h5>
                    <p>Escolha uma conversa da lista para começar a conversar</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.conversa-item {
    transition: background-color 0.2s;
}

.conversa-item:hover {
    background-color: #f8f9fa;
}

.conversa-item.active {
    background-color: #e3f2fd;
    border-left: 3px solid #0d6efd;
}

.conversas-list {
    max-height: 500px;
    overflow-y: auto;
}
</style>

<script>
// Função para sincronizar conversas
function syncConversas() {
    const btn = document.getElementById('syncBtn');
    const originalText = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sincronizando...';
    btn.disabled = true;

    fetch('<?= site_url('whatsapp/sync') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na rede: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Resposta da sincronização:', data);

            if (data.success) {
                showAlert('success', data.message || 'Conversas sincronizadas com sucesso!');
                // Recarrega a página após 2 segundos para mostrar os novos dados
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showAlert('error', data.error || 'Erro ao sincronizar conversas');
            }
        })
        .catch(error => {
            console.error('Erro na sincronização:', error);
            showAlert('error', 'Erro na sincronização: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
}

// Função para selecionar conversa
function selecionarConversa(numero) {
    // Remove a classe active de todos os itens
    document.querySelectorAll('.conversa-item').forEach(item => {
        item.classList.remove('active');
    });

    // Adiciona a classe active ao item clicado
    event.currentTarget.classList.add('active');

    // Carrega a conversa
    carregarConversa(numero);
}

// Função para carregar conversa
function carregarConversa(numero) {
    const areaConversa = document.getElementById('area-conversa');
    areaConversa.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p>Carregando conversa...</p>
        </div>
    `;

    fetch(`<?= site_url('whatsapp/conversa/') ?>${numero}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                areaConversa.innerHTML = `
                <div class="conversa-detalhes">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>${numero}</h5>
                        <span class="badge bg-success">Online</span>
                    </div>
                    
                    <div class="mensagens-container" style="height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; background-color: #f8f9fa;">
                        ${data.messages.map(msg => `
                            <div class="message ${msg.from_me ? 'my-message' : 'other-message'} mb-2">
                                <div class="card ${msg.from_me ? 'bg-primary text-white' : 'bg-light'}">
                                    <div class="card-body p-2">
                                        <p class="card-text mb-1">${msg.mensagem}</p>
                                        <small class="${msg.from_me ? 'text-white-50' : 'text-muted'}">
                                            ${msg.formatted_time || ''}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="input-group mt-3">
                        <input type="text" class="form-control" placeholder="Digite sua mensagem..." id="messageInput">
                        <button class="btn btn-primary" type="button" onclick="enviarMensagem('${numero}')">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            `;
            } else {
                areaConversa.innerHTML = `
                <div class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>Erro ao carregar conversa</p>
                </div>
            `;
            }
        })
        .catch(error => {
            console.error('Erro ao carregar conversa:', error);
            areaConversa.innerHTML = `
            <div class="text-center text-danger">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <p>Erro ao carregar conversa</p>
                <small>${error.message}</small>
            </div>
        `;
        });
}

// Função para enviar mensagem
function enviarMensagem(numero) {
    const input = document.getElementById('messageInput');
    const mensagem = input.value.trim();

    if (!mensagem) return;

    fetch('<?= site_url('whatsapp/send') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                numero: numero,
                mensagem: mensagem
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                // Recarrega a conversa para mostrar a nova mensagem
                carregarConversa(numero);
            } else {
                showAlert('error', data.error || 'Erro ao enviar mensagem');
            }
        })
        .catch(error => {
            showAlert('error', 'Erro ao enviar mensagem: ' + error.message);
        });
}

// Função para mostrar alertas
function showAlert(type, message) {
    // Remove alertas existentes
    const existingAlerts = document.querySelectorAll('.alert-dismissible');
    existingAlerts.forEach(alert => alert.remove());

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);

    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Busca em tempo real
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.conversa-item');

    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>

<?= $this->endSection() ?>