USE touiteur;

ALTER TABLE UTILISATEUR
    ADD COLUMN role VARCHAR(10) NOT NULL DEFAULT 'user' AFTER email;

ALTER TABLE UTILISATEUR
    ADD CONSTRAINT chk_utilisateur_role CHECK (role IN ('user', 'admin'));
