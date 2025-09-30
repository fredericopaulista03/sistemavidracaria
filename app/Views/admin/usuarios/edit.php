<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2>Editar Usuário</h2>
<form method="post" action="/usuarios/update/<?= $usuario->id ?>">
    <div class="mb-3">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" value="<?= $usuario->nome ?>">
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= $usuario->email ?>">
    </div>
    <div class="mb-3">
        <label>Telefone</label>
        <input type="text" name="telefone" class="form-control" value="<?= $usuario->telefone ?>">
    </div>
    <button class="btn btn-primary">Atualizar</button>
</form>

<?= $this->endSection() ?>