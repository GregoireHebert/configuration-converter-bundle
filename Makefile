.PHONY: test format
.DEFAULT_GOAL= help

help:
	@grep -E '(^[0-9a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-25s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

format: ## format the code
	vendor/bin/php-cs-fixer fix

test: ## test the code
	vendor/bin/phpstan -l7 analyze src tests
	vendor/bin/phpunit
