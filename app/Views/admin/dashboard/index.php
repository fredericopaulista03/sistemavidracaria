<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">📊 Dashboard</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total de Vendas</h5>
                <p class="card-text fs-3"><?= $totalVendas ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Lançamentos Financeiros</h5>
                <p class="card-text fs-3"><?= $totalFinanceiro ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Mensagens Pendentes</h5>
                <p class="card-text fs-3"><?= $pendentes ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Últimas Vendas -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Últimas Vendas</div>
            <div class="card-body">
                <?php if ($ultimasVendas): ?>
                <ul class="list-group">
                    <?php foreach ($ultimasVendas as $v): ?>
                    <li class="list-group-item">
                        <?= $v->produto ?> - R$ <?= number_format($v->valor, 2, ',', '.') ?> (<?= $v->status ?>)
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>Nenhuma venda registrada.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Últimos Lançamentos -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Últimos Lançamentos Financeiros</div>
            <div class="card-body">
                <?php if ($ultimosLancamentos): ?>
                <ul class="list-group">
                    <?php foreach ($ultimosLancamentos as $f): ?>
                    <li class="list-group-item">
                        <?= $f->tipo ?> - <?= $f->descricao ?> - R$ <?= number_format($f->valor, 2, ',', '.') ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>Nenhum lançamento registrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Últimas Mensagens -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Últimas Mensagens WhatsApp</div>
            <div class="card-body">
                <?php if ($ultimasMensagens): ?>
                <ul class="list-group">
                    <?php foreach ($ultimasMensagens as $m): ?>
                    <li class="list-group-item">
                        <?= $m->numero ?> - <?= $m->mensagem ?> (<?= $m->status ?>)
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p>Nenhuma mensagem registrada.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>