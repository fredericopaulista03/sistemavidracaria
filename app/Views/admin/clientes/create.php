<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2>Novo Cliente</h2>

<form method="post" action="/clientes/store">
    <div class="mb-3">
        <label>Nome</label>
        <input type="text" name="nome" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control">
    </div>
    <div class="mb-3">
        <label>Telefone</label>
        <input type="text" name="telefone" class="form-control">
    </div>
    <div class="mb-3">
        <label>Endereço</label>
        <textarea name="endereco" class="form-control"></textarea>
    </div>
    <button class="btn btn-success">Salvar</button>
</form>

<?= $this->endSection() ?>