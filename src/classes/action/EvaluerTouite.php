<?php

namespace touiteur\action;

use touiteur\touites\Evaluation;
use touiteur\repository\TouiteurRepository;

class EvaluerTouite extends Action {
    public function execute(): string {
        $user = $this->getUtilisateurConnecte();

        $page = filter_var($_POST['page'] ?? 1, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'default' => 1],
        ]);
        $idTouite = filter_var($_POST['id_touite'] ?? null, FILTER_VALIDATE_INT);
        $vote = $_POST['vote'] ?? '';

        if ($this->http_method === 'POST' && $user !== null && $idTouite !== false && $idTouite !== null
            && ($vote === 'like' || $vote === 'dislike')) {
            $evaluation = new Evaluation($user['id'], $idTouite, $vote === 'like' ? 1 : -1);
            TouiteurRepository::getInstance()->evaluerTouite($evaluation);
        }

        header('Location: index.php?action=home&page=' . $page . '#touite-' . (int) $idTouite);
        exit;
    }
}