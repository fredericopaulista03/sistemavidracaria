<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2>Financeiro</h2>
<a href="/financeiro/create" class="btn btn-primary mb-3">Novo Lançamento</a>

<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Descrição</th>
        <th>Valor</th>
        <th>Data</th>
        <th>Ações</th>
    </tr>
    <?php foreach ($lancamentos as $f): ?>
    <tr>
        <td><?= $f->id ?></td>
        <td><?= $f->tipo ?></td>
        <td><?= $f->descricao ?></td>
        <td>R$ <?= number_format($f->valor, 2, ',', '.') ?></td>
        <td><?= $f->data ?></td>
        <td>
            <a href="/financeiro/delete/<?= $f->id ?>" class="btn btn-danger btn-sm">Excluir</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?= $this->endSection() ?>