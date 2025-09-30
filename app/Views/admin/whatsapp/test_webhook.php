<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h2>🧪 Testar Webhooks WhatsApp</h2>

    <div class="row mt-4">
        <!-- Teste de Recebimento -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Testar Recebimento de Mensagem</h5>
                </div>
                <div class="card-body">
                    <p>Simula o recebimento de uma mensagem via webhook</p>
                    <button class="btn btn-primary" onclick="testReceive()">
                        Testar Recebimento
                    </button>
                    <div id="receiveResult" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Teste de Envio -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Testar Envio de Mensagem</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Número:</label>
                        <input type="text" class="form-control" id="testNumber" value="31999999999"
                            placeholder="5531999999999">
                    </div>
                    <div class="mb-3">
                        <label>Mensagem:</label>
                        <textarea class="form-control" id="testMessage" rows="3">Esta é uma mensagem de teste</textarea>
                    </div>
                    <button class="btn btn-success" onclick="testSend()">
                        Testar Envio
                    </button>
                    <div id="sendResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Mensagens de Teste -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>Últimas Mensagens de Teste</h5>
        </div>
        <div class="card-body">
            <button class="btn btn-outline-secondary mb-3" onclick="loadTestMessages()">
                Carregar Mensagens
            </button>
            <div id="testMessages"></div>
        </div>
    </div>
</div>

<script>
// Testar recebimento
function testReceive() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = 'Testando...';

    fetch('<?= site_url('whatsapp/test/webhook') ?>')
        .then(response => response.json())
        .then(data => {
            document.getElementById('receiveResult').innerHTML = `
                <div class="alert alert-${data.result.success ? 'success' : 'danger'}">
                    <strong>${data.message}</strong>
                    <pre class="mt-2">${JSON.stringify(data.result, null, 2)}</pre>
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('receiveResult').innerHTML = `
                <div class="alert alert-danger">
                    <strong>Erro:</strong> ${error.message}
                </div>
            `;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Testar Recebimento';
        });
}

// Testar envio
function testSend() {
    const numero = document.getElementById('testNumber').value;
    const mensagem = document.getElementById('testMessage').value;
    const btn = event.target;

    if (!numero || !mensagem) {
        alert('Preencha número e mensagem!');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = 'Enviando...';

    const formData = new FormData();
    formData.append('numero', numero);
    formData.append('mensagem', mensagem);

    fetch('<?= site_url('whatsapp/test/send') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('sendResult').innerHTML = `
            <div class="alert alert-${data.result.success ? 'success' : 'danger'}">
                <strong>${data.message}</strong>
                <pre class="mt-2">${JSON.stringify(data.result, null, 2)}</pre>
            </div>
        `;
        })
        .catch(error => {
            document.getElementById('sendResult').innerHTML = `
            <div class="alert alert-danger">
                <strong>Erro:</strong> ${error.message}
            </div>
        `;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Testar Envio';
        });
}

// Carregar mensagens de teste
function loadTestMessages() {
    fetch('<?= site_url('whatsapp/conversa/5531999999999') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages.length > 0) {
                let html = '<div class="list-group">';
                data.messages.forEach(msg => {
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong>${msg.numero}</strong>
                                <small>${new Date(msg.created_at).toLocaleString()}</small>
                            </div>
                            <div>${msg.mensagem}</div>
                            <small class="text-muted">Status: ${msg.status} | Direção: ${msg.direction}</small>
                        </div>
                    `;
                });
                html += '</div>';
                document.getElementById('testMessages').innerHTML = html;
            } else {
                document.getElementById('testMessages').innerHTML = `
                    <div class="alert alert-info">Nenhuma mensagem de teste encontrada</div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('testMessages').innerHTML = `
                <div class="alert alert-danger">Erro ao carregar mensagens: ${error.message}</div>
            `;
        });
}
</script>

<?= $this->endSection() ?>