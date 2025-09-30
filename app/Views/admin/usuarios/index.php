<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2>Usuários</h2>
<a href="/usuarios/create" class="btn btn-primary mb-3">Novo Usuário</a>

<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Telefone</th>
        <th>Ações</th>
    </tr>
    <?php foreach ($usuarios as $u): ?>
    <tr>
        <td><?= $u->id ?></td>
        <td><?= $u->nome ?></td>
        <td><?= $u->email ?></td>
        <td><?= $u->telefone ?></td>
        <td>
            <a href="/usuarios/edit/<?= $u->id ?>" class="btn btn-warning btn-sm">Editar</a>
            <a href="/usuarios/delete/<?= $u->id ?>" class="btn btn-danger btn-sm">Excluir</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?= $this->endSection() ?>