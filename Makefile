.PHONY: docker-env docker-build docker-up docker-down docker-migrate docker-seed docker-test docker-logs qa-pack

docker-env:
	cp -n .env.docker.example .env || true

docker-build:
	docker compose build

docker-up:
	docker compose up -d

docker-down:
	docker compose down

docker-migrate:
	docker compose exec app php artisan migrate --force

docker-seed:
	docker compose exec app php artisan db:seed --force

docker-test:
	docker compose exec app php artisan test

docker-logs:
	docker compose logs -f --tail=200

qa-pack:
	bash scripts/build_handover_pack.sh
