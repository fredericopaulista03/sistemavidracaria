<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2>Clientes</h2>
<a href="/clientes/create" class="btn btn-primary mb-3">Novo Cliente</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($clientes): ?>
        <?php foreach ($clientes as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= $c['nome'] ?></td>
            <td><?= $c['email'] ?></td>
            <td><?= $c['telefone'] ?></td>
            <td>
                <a href="/clientes/edit/<?= $c['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                <a href="/clientes/delete/<?= $c['id'] ?>" class="btn btn-sm btn-danger"
                    onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="5">Nenhum cliente encontrado.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>