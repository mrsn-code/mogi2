init:
	docker-compose up -d --build
	docker-compose exec php composer install