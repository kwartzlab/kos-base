FROM ubuntu:latest

# Install core packages.
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -yq \
      php \
      php-mbstring \
      php-xml \
      php-curl \
      php-mysql \
      php-sqlite3 \
      php-zip \
      php-gd \
      git-core \
      zip \
      mysql-client

# Install Composer.
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

# # Create an unprivileged user.
RUN useradd app # No shell or home directory should be required.
USER app

WORKDIR /app
COPY --chown=app:app . /app

RUN composer install --prefer-dist

# Composer doesn't always set owner properly so let's globally repair that.
RUN chown -R app:app .

CMD ./docker-entrypoint.sh
