# Cibles
.PHONY: up run

# Démarre les services
up:
	docker compose up -d

# Exécute la commande dans le conteneur
run: up
	docker compose exec api php bin/console d:d:r -df
