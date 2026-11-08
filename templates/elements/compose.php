<section class="win compose">
    <div class="titlebar"><span>Nouveau touite.exe</span></div>
    <form class="panel" method="post" action="index.php?action=cree-un-touite" enctype="multipart/form-data">
        <textarea id="newTouite" name="contenu" maxlength="235" required placeholder="Quoi de neuf ? Utilise #tags"></textarea>

        <div class="image-zone">
            <input type="file" id="imageInput" name="image"
                   accept="image/jpeg,image/png,image/gif,image/webp" hidden>

            <label for="imageInput" class="image-drop" id="imageDrop">
                <span class="image-drop-icon">+</span>
                <span class="image-drop-text">
                    Ajouter une image<br>
                    <small>Glisse-la ici ou clique · JPG, PNG, GIF, WebP · 2 Mo max</small>
                </span>
            </label>

            <div class="image-preview" id="imagePreview" hidden>
                <img id="imagePreviewImg" alt="Aperçu de l'image">
                <button type="button" id="imageRemove" class="image-remove">✕ Retirer l'image</button>
            </div>
        </div>

        <div class="row">
            <span class="count" id="count">0 / 235</span>
            <button type="submit">Touiter &gt;&gt;</button>
        </div>
    </form>
</section>
