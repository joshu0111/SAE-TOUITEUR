<h2>Détail du touite</h2>

<?php 
$touite = $data['touite'] ?? null;
?>

<?php if (!empty($touite)): ?>
    <div style="border: 1px solid #333; padding: 15px; border-radius: 8px; margin-top: 15px; background: #111; max-width: 500px;">
        <div style="margin-bottom: 10px;">
            <strong style="color: #fff;"><?= htmlspecialchars($touite['auteur']) ?></strong>
            <span style="color: #888; font-size: 0.9em;">@<?= htmlspecialchars($touite['pseudo']) ?></span>
            <span style="color: #666; font-size: 0.8em; margin-left: 10px; display: block; margin-top: 4px;"><?= htmlspecialchars($touite['date']) ?></span>
        </div>
        
        <div style="margin-top: 12px; font-size: 1.1em; color: #ddd; line-height: 1.4;">
            <p><?= htmlspecialchars($touite['contenu']) ?></p>
        </div>
    </div>
<?php else: ?>
    <p>Aucun touite trouvé avec cet identifiant.</p>
<?php endif; ?>