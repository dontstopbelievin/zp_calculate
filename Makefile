build:
	$(info Make: Running user server.)
	docker-compose up --build -d
	docker-compose exec zp_app composer install
	docker-compose exec --user root zp_app chown -R user:www-data storage
	docker-compose exec --user root zp_app chmod -R 775 storage
	docker-compose exec zp_app php artisan migrate:fresh --seed
