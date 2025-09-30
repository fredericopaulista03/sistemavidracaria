<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<h2>Novo Lançamento</h2>
<form method="post" action="/financeiro/store">
    <div class="mb-3">
        <label>Tipo</label>
        <select name="tipo" class="form-control">
            <option value="entrada">Entrada</option>
            <option value="saida">Saída</option>
        </select>
    </div>
    <div class="mb-3">
        <label>Descrição</label>
        <input type="text" name="descricao" class="form-control">
    </div>
    <div class="mb-3">
        <label>Valor</label>
        <input type="number" name="valor" step="0.01" class="form-control">
    </div>
    <div class="mb-3">
        <label>Data</label>
        <input type="date" name="data" class="form-control">
    </div>
    <button class="btn btn-success">Salvar</button>
</form>

<?= $this->endSection() ?>