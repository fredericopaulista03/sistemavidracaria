<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2>Editar Cliente</h2>

<form method="post" action="/clientes/update/<?= $cliente['id'] ?>">
    <div class="mb-3">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" value="<?= $cliente['nome'] ?>" required>
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= $cliente['email'] ?>">
    </div>
    <div class="mb-3">
        <label>Telefone</label>
        <input type="text" name="telefone" class="form-control" value="<?= $cliente['telefone'] ?>">
    </div>
    <div class="mb-3">
        <label>Endereço</label>
        <textarea name="endereco" class="form-control"><?= $cliente['endereco'] ?></textarea>
    </div>
    <button class="btn btn-primary">Atualizar</button>
</form>

<?= $this->endSection() ?>