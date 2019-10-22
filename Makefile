start:
	php -S localhost:8080 -t public public/index.php

test:
	composer run-script phpunit tests
