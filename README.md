# Quiz Certification Symfony

Application de quiz pour la préparation à la certification Symfony.

## Installation

```bash
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

## Commandes Fixtures

### Remise à zéro complète (efface toutes les données)

```bash
php bin/console doctrine:fixtures:load
```

> ⚠️ Cette commande **efface toutes les données** de la base, y compris les statistiques et sessions de quiz.

### Recharger les questions sans effacer les statistiques

```bash
php bin/console app:reload-questions
```

Cette commande :
- Préserve les sessions de quiz et les réponses utilisateur
- Met à jour les questions existantes (upsert)
- Préserve les IDs des questions pour maintenir la cohérence des statistiques

### Options de la commande `app:reload-questions`

| Option | Description |
|--------|-------------|
| `--group=GROUP` | Groupe de fixtures à charger (défaut: `questions`) |
| `--migrate-identifiers` | Génère les identifiants pour les questions existantes |

### Groupes de fixtures disponibles

| Groupe | Description |
|--------|-------------|
| `questions` | Toutes les questions d'entraînement |
| `exam` | Questions de l'examen de certification |

### Exemples

```bash
# Recharger uniquement les questions d'entraînement
php bin/console app:reload-questions --group=questions

# Recharger les questions de certification
php bin/console app:reload-questions --group=exam

# Migrer les identifiants (première utilisation après mise à jour)
php bin/console app:reload-questions --migrate-identifiers

# Utiliser doctrine:fixtures:load avec append (ne purge pas la base)
php bin/console doctrine:fixtures:load --append --group=questions
```

## Lancer le serveur

```bash
symfony server:start
```

## Tests

```bash
php bin/phpunit
```
