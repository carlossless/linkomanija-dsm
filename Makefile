PHP_FILES = $(shell find src -type f -name '*.php')
PACKAGE_FILES = $(PHP_FILES) src/INFO

linkomanija.dlm: $(PHP_FILES) src/INFO
	tar zcf $@  -C src/ $(patsubst src/%, %, $^)

.PHONY: test
test:
	bin/phpspec run

.PHONY: system-test
system-test:
	php utils/system-test.php

.PHONY: clean
clean:
	rm linkomanija.dlm
