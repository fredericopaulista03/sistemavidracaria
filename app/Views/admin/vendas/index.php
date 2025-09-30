<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2>Vendas</h2>

<a href="/vendas/create" class="btn btn-primary mb-3">Nova Venda</a>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Produto</th>
            <th>Valor</th>
            <th>Status</th>
            <th>Criada em</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($vendas)): ?>
        <?php foreach ($vendas as $venda): ?>
        <tr>
            <td><?= $venda['id'] ?></td>
            <td><?= esc($venda['cliente_nome']) ?></td>
            <td><?= esc($venda['produto']) ?></td>
            <td>R$ <?= number_format($venda['valor'], 2, ',', '.') ?></td>
            <td><?= ucfirst($venda['status']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($venda['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="6">Nenhuma venda encontrada.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>