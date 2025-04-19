FROM phpswoole/swoole:6.0-php8.2

ARG USERNAME=user
ARG USER_UID=1000
ARG USER_GID=$USER_UID

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    && rm -rf /var/lib/apt/lists/*

# Create a non-root user.
RUN addgroup --gid $USER_GID $USERNAME \
  && adduser $USERNAME \
    --uid $USER_UID \
    --ingroup $USERNAME \
    --home /home/$USERNAME

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY "docker/php.ini" ""

# Install extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync \
    && install-php-extensions \
        bcmath \
        pdo_pgsql \
        xdebug \
        zip \
    && rm /usr/local/bin/install-php-extensions
