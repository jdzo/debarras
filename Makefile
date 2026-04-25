# Nom du projet / conteneur
PROJECT_NAME = symfony-frankenphp

# Couleurs pour le terminal (juste pour le fun)
GREEN = \033[0;32m
YELLOW = \033[1;33m
RESET = \033[0m


build:
	docker-compose build --no-cache
	@echo "$(GREEN)✅ Build terminé.$(RESET)"

up:
	@echo "$(YELLOW)🚀 Démarrage des conteneurs...$(RESET)"
	docker-compose up -d
	@echo "$(GREEN)✅ Conteneurs démarrés !$(RESET)"
	@echo "🌍 Application disponible sur : https://debarras.localhost:8443"

down:
	@echo "$(YELLOW)🛑 Arrêt et suppression des conteneurs...$(RESET)"
	docker-compose down
	@echo "$(GREEN)✅ Conteneurs arrêtés.$(RESET)"

restart: down up

logs:
	@echo "$(YELLOW)📜 Affichage des logs... (Ctrl+C pour quitter)$(RESET)"
	docker-compose logs -f

bash:
	@echo "$(YELLOW)🐚 Connexion au conteneur PHP...$(RESET)"
	docker-compose exec php bash

composer-install:
	@echo "$(YELLOW)📦 Installation des dépendances Composer...$(RESET)"
	docker-compose exec php composer install
	@echo "$(GREEN)✅ Dépendances installées.$(RESET)"

cache-clear:
	@echo "$(YELLOW)🧹 Nettoyage du cache Symfony...$(RESET)"
	docker-compose exec php php bin/console cache:clear
	@echo "$(GREEN)✅ Cache vidé.$(RESET)"

test:
	@echo "$(YELLOW)🧪 Lancement des tests...$(RESET)"
	docker-compose exec php ./vendor/bin/phpunit --testdox
	@echo "$(GREEN)✅ Tests terminés.$(RESET)"

test-functional:
	@echo "$(YELLOW)🧪 Lancement des tests fonctionnels...$(RESET)"
	docker-compose exec php ./vendor/bin/phpunit tests/Functional/ --testdox
	@echo "$(GREEN)✅ Tests fonctionnels terminés.$(RESET)"

test-unit:
	@echo "$(YELLOW)🧪 Lancement des tests unitaires...$(RESET)"
	docker-compose exec php ./vendor/bin/phpunit tests/Unit/ --testdox
	@echo "$(GREEN)✅ Tests unitaires terminés.$(RESET)"

# Nettoyage complet (containers, images, volumes)
clean:
	@echo "$(YELLOW)🧨 Nettoyage complet du projet Docker...$(RESET)"
	docker-compose down --volumes --rmi all
	@echo "$(GREEN)✅ Nettoyage terminé.$(RESET)"

