<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2>Nova Venda</h2>
<form method="post" action="/vendas/store">
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
            <option value="pendente">Pendente</option>
            <option value="pago">Pago</option>
            <option value="cancelado">Cancelado</option>
        </select>
    </div>
    <button class="btn btn-success">Salvar</button>
</form>

<?= $this->endSection() ?>