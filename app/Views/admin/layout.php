<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Sistema Gestão' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">Sistema</a>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="/clientes">Clientes</a></li>
                <li class="nav-item"><a class="nav-link" href="/usuarios">Usuários</a></li>
                <li class="nav-item"><a class="nav-link" href="/financeiro">Financeiro</a></li>
                <li class="nav-item"><a class="nav-link" href="/vendas">Vendas</a></li>
                <li class="nav-item"><a class="nav-link" href="/whatsapp">WhatsApp</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <?= $this->renderSection('content') ?>
    </div>
</body>

</html>