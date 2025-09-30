<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<h2>Vendas</h2>
<a href="/vendas/create" class="btn btn-primary mb-3">Nova Venda</a>

<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <th>Produto</th>
        <th>Valor</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>
    <?php foreach ($vendas as $v): ?>
    <tr>
        <td><?= $v->id ?></td>
        <td><?= $v->produto ?></td>
        <td>R$ <?= number_format($v->valor, 2, ',', '.') ?></td>
        <td><?= $v->status ?></td>
        <td>
            <a href="/vendas/delete/<?= $v->id ?>" class="btn btn-danger btn-sm">Excluir</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?= $this->endSection() ?>