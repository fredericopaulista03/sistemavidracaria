<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="row">
    <!-- Coluna da Esquerda - Lista de Conversas -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">💬 Conversas WhatsApp</h5>
                <span class="badge bg-primary"><?= $totalConversas ?> Conversas</span>
            </div>
            <div class="card-body">
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
                        <input type="text" class="form-control" placeholder="Buscar por nome ou telefone...">
                    </div>
                </div>

                <!-- Lista de Conversas -->
                <div class="conversas-list">
                    <?php if ($conversas && count($conversas) > 0): ?>
                    <?php foreach ($conversas as $conversa): ?>
                    <div class="conversa-item p-3 border-bottom" style="cursor: pointer;"
                        onclick="selecionarConversa(<?= $conversa->id ?>)">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold"><?= esc($conversa->nome) ?></h6>
                                <p class="mb-1 text-muted small"><?= esc($conversa->ultima_mensagem) ?></p>
                                <small
                                    class="text-muted"><?= date('d/m/Y', strtotime($conversa->ultima_atualizacao)) ?></small>
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
function selecionarConversa(conversaId) {
    // Remove a classe active de todos os itens
    document.querySelectorAll('.conversa-item').forEach(item => {
        item.classList.remove('active');
    });

    // Adiciona a classe active ao item clicado
    event.currentTarget.classList.add('active');

    // Aqui você faria uma requisição para carregar a conversa
    carregarConversa(conversaId);
}

function carregarConversa(conversaId) {
    // Simulação de carregamento da conversa
    const areaConversa = document.getElementById('area-conversa');
    areaConversa.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p>Carregando conversa...</p>
        </div>
    `;

    // Simula um delay de carregamento
    setTimeout(() => {
        areaConversa.innerHTML = `
            <div class="conversa-detalhes">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>João Silva</h5>
                    <span class="badge bg-success">Online</span>
                </div>
                
                <div class="mensagens-container" style="height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; background-color: #f8f9fa;">
                    <!-- Mensagens aparecerão aqui -->
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-comment-dots fa-2x mb-2"></i>
                        <p>Inicie a conversa</p>
                    </div>
                </div>
                
                <div class="input-group mt-3">
                    <input type="text" class="form-control" placeholder="Digite sua mensagem...">
                    <button class="btn btn-primary" type="button">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        `;
    }, 1000);
}
</script>

<?= $this->endSection() ?>