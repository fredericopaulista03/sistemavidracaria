<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2>Nova Venda</h2>

<?php if(session()->getFlashdata('errors')): ?>
<div class="alert alert-danger">
    <ul>
        <?php foreach(session()->getFlashdata('errors') as $error): ?>
        <li><?= esc($error) ?></li>
        <?php endforeach ?>
    </ul>
</div>
<?php endif; ?>

<form method="post" action="/vendas/store">
    <div class="mb-3">
        <label>Cliente</label>
        <select name="cliente_id" class="form-control" required>
            <option value="">Selecione...</option>
            <?php foreach($clientes as $cliente): ?>
            <option value="<?= $cliente['id'] ?>">
                <?= esc($cliente['nome']) ?> (<?= esc($cliente['email']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Produto</label>
        <input type="text" name="produto" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Valor</label>
        <input type="number" step="0.01" name="valor" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="aberta">Aberta</option>
            <option value="fechada">Fechada</option>
            <option value="cancelada">Cancelada</option>
        </select>
    </div>

    <button class="btn btn-success">Salvar</button>
</form>

<?= $this->endSection() ?>