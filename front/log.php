<?php

use GlpiPlugin\Delainventory\Log;
use Html;
use Session;
use User;

$itemtype = $item->getType();
$item_id  = $item->getID();

$logs = Log::getLogs($itemtype, $item_id);
?>

<div class="card">
    <div class="card-header d-flex justify-content-end">

        <form id="inventory-form" action="../plugins/delainventory/front/action.php" method="post">
            <?= Html::hidden('item_id', ['value' => $item_id]); ?>
            <?= Html::hidden('itemtype', ['value' => $itemtype]); ?>
            <?= Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]); ?>
            <input type="hidden" name="comment" id="comment">

            <button type="button"
                    class="btn btn-primary btn-sm py-2 px-2"
                    onclick="registrarInventario()">
                + Registrar inventário
            </button>
        </form>

        <button class="btn btn-secondary btn-sm px-2 py-2 ms-2"
                onclick="imprimirEtiqueta(<?= (int) $item_id ?>, '<?= htmlspecialchars($itemtype) ?>')">
            Imprimir etiqueta
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ação</th>
                    <th>Data</th>
                    <th>Usuário</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($logs as $row): ?>
                    <?php 
                        $user = new User();
                        $user->getFromDB($row['users_id']);
                        $username = $user->getFriendlyName();
                    ?>

                    <tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['comment']) ?></td>
                        <td><?= htmlspecialchars($row['date_creation']) ?></td>
                        <td><?= htmlspecialchars($username) ?></td>
                    </tr>

                <?php endforeach; ?>
            </tbody>

        </table>
    </div>
</div>

<script>
    function registrarInventario() {
        const comment = prompt("Insira a mensagem:");

        if (comment === null) return;

        document.getElementById('comment').value = comment;
        document.getElementById('inventory-form').submit();
    }

    function imprimirEtiqueta(id, itemtype) {
        fetch('/plugins/delainventory/front/print.php?itemtype=' + encodeURIComponent(itemtype) + '&id=' + id)
        .then(response => response.text())
        .then(msg => alert(msg)).catch(err => {
            alert('Erro ao imprimir');
            console.error(err);
        });
    }
</script>