FROM alpine:3.8

ENV MYSQL_ROOT_PWD="webdevroot"
ENV MYSQL_USER="myuser"
ENV MYSQL_USER_PWD="webdevroot"
ENV MYSQL_USER_DB="db"


ADD https://repos.php.earth/alpine/phpearth.rsa.pub /etc/apk/keys/phpearth.rsa.pub
RUN apk update

# Base
RUN apk add --no-cache  \
    supervisor          \
    git                 \
    openssh             \
    ca-certificates     \
    curl                \
    wget                \
    zlib-dev            \
    procps

# CERTS
RUN update-ca-certificates

# PHP
RUN echo "https://repos.php.earth/alpine/v3.8" >> /etc/apk/repositories
RUN apk add --no-cache  \
    composer            \
    php7.2              \
    php7.2-fpm          \
    php7.2-curl         \
    php7.2-ctype        \
    php7.2-zip          \
    php7.2-mbstring     \
    php7.2-pcntl        \
    php7.2-posix        \
    php7.2-iconv        \
    php7.2-intl         \
    php7.2-pdo_mysql    \
    php7.2-tokenizer    \
    php7.2-dom          \
    php7.2-redis        \
    php7.2-xml          \
    php7.2-simplexml    \
    php7.2-json         \
    php7.2-sodium       \
    php7.2-opcache      \
    php7.2-shmop        \
    php7.2-xmlwriter    \
    php7.2-common


# Install PHPRedis
RUN wget https://github.com/phpredis/phpredis/archive/4.0.2.tar.gz
RUN tar xfz 4.0.2.tar.gz && rm -r 4.0.2.tar.gz
RUN mkdir -p /usr/src/php/ext/
RUN mv ./phpredis-4.0.2 /usr/src/php/ext/redis && rm -Rf phpredis-4.0.2

# Speed up composer
RUN composer global require hirak/prestissimo

# PHP Configuration
RUN echo 'memory_limit=1024M' > /etc/php/7.2/conf.d/memory_limit.ini
RUN echo '[global]' > /etc/php/7.2/php-fpm.d/zz-docker.conf
RUN echo 'daemonize = yes' >> /etc/php/7.2/php-fpm.d/zz-docker.conf
RUN echo '[www]' >> /etc/php/7.2/php-fpm.d/zz-docker.conf
RUN echo 'listen=9000' >> /etc/php/7.2/php-fpm.d/zz-docker.conf
RUN echo 'realpath_cache_size=2048M' > /etc/php/7.2/conf.d/pathcache.ini
RUN echo 'realpath_cache_ttl=7200' >> /etc/php/7.2/conf.d/pathcache.ini
RUN echo '[opcache]' > /etc/php/7.2/conf.d/opcache.ini
RUN echo 'opcache.memory_consumption = 512M' >> /etc/php/7.2/conf.d/opcache.ini
RUN echo 'opcache.max_accelerated_files = 1000000' >> /etc/php/7.2/conf.d/opcache.ini

# Install Mysql
RUN apk add --no-cache mysql mysql-client
RUN addgroup mysql mysql
RUN echo "lower_case_table_names=1 >> /etc/mysql/conf.d/docker.cnf"
RUN mkdir /scripts
COPY ./mysql.sh /scripts/mysql.sh
RUN chmod +x /scripts/mysql.sh
RUN /scripts/mysql.sh

# nginx


ENV NGINX_VERSION 1.13.12
ENV NCHAN_VERSION 1.1.15

RUN GPG_KEYS=B0F4253373F8F6F510D42178520A9993A1C052F8 \
	&& CONFIG="\
		--prefix=/etc/nginx \
		--sbin-path=/usr/sbin/nginx \
		--modules-path=/usr/lib/nginx/modules \
		--conf-path=/etc/nginx/nginx.conf \
		--error-log-path=/var/log/nginx/error.log \
		--http-log-path=/var/log/nginx/access.log \
		--pid-path=/var/run/nginx.pid \
		--lock-path=/var/run/nginx.lock \
		--http-client-body-temp-path=/var/cache/nginx/client_temp \
		--http-proxy-temp-path=/var/cache/nginx/proxy_temp \
		--http-fastcgi-temp-path=/var/cache/nginx/fastcgi_temp \
		--http-uwsgi-temp-path=/var/cache/nginx/uwsgi_temp \
		--http-scgi-temp-path=/var/cache/nginx/scgi_temp \
		--user=nginx \
		--group=nginx \
		--with-http_ssl_module \
		--with-http_realip_module \
		--with-http_addition_module \
		--with-http_sub_module \
		--with-http_dav_module \
		--with-http_flv_module \
		--with-http_mp4_module \
		--with-http_gunzip_module \
		--with-http_gzip_static_module \
		--with-http_random_index_module \
		--with-http_secure_link_module \
		--with-http_stub_status_module \
		--with-http_auth_request_module \
		--with-http_xslt_module=dynamic \
		--with-http_image_filter_module=dynamic \
		--with-http_geoip_module=dynamic \
		--with-threads \
		--with-stream \
		--with-stream_ssl_module \
		--with-stream_ssl_preread_module \
		--with-stream_realip_module \
		--with-stream_geoip_module=dynamic \
		--with-http_slice_module \
		--with-mail \
		--with-mail_ssl_module \
		--with-compat \
		--with-file-aio \
		--with-http_v2_module \
		--add-dynamic-module=/usr/src/nchan-${NCHAN_VERSION} \
	" \
	&& addgroup -S nginx \
	&& adduser -D -S -h /var/cache/nginx -s /sbin/nologin -G nginx nginx \
	&& apk add --no-cache --virtual .build-deps \
		gcc \
		libc-dev \
		make \
		openssl-dev \
		pcre-dev \
		zlib-dev \
		linux-headers \
		curl \
		gnupg \
		libxslt-dev \
		gd-dev \
		geoip-dev \
	&& curl -fSL https://nginx.org/download/nginx-$NGINX_VERSION.tar.gz -o nginx.tar.gz \
	&& curl -fSL https://nginx.org/download/nginx-$NGINX_VERSION.tar.gz.asc  -o nginx.tar.gz.asc \
	&& curl -fSL https://github.com/slact/nchan/archive/v${NCHAN_VERSION}.tar.gz -o nchan.tar.gz \
	&& export GNUPGHOME="$(mktemp -d)" \
	&& found=''; \
	for server in \
		ha.pool.sks-keyservers.net \
		hkp://keyserver.ubuntu.com:80 \
		hkp://p80.pool.sks-keyservers.net:80 \
		pgp.mit.edu \
	; do \
		echo "Fetching GPG key $GPG_KEYS from $server"; \
		gpg --keyserver "$server" --keyserver-options timeout=10 --recv-keys "$GPG_KEYS" && found=yes && break; \
	done; \
	test -z "$found" && echo >&2 "error: failed to fetch GPG key $GPG_KEYS" && exit 1; \
	gpg --batch --verify nginx.tar.gz.asc nginx.tar.gz \
	&& rm -rf "$GNUPGHOME" nginx.tar.gz.asc \
	&& mkdir -p /usr/src \
	&& tar -zxC /usr/src -f nginx.tar.gz \
	&& tar -zxC /usr/src -f nchan.tar.gz \
	&& rm nginx.tar.gz \
	&& rm nchan.tar.gz \
	&& cd /usr/src/nginx-$NGINX_VERSION \
	&& ./configure $CONFIG --with-debug \
	&& make -j$(getconf _NPROCESSORS_ONLN) \
	&& mv objs/nginx objs/nginx-debug \
	&& mv objs/ngx_http_xslt_filter_module.so objs/ngx_http_xslt_filter_module-debug.so \
	&& mv objs/ngx_http_image_filter_module.so objs/ngx_http_image_filter_module-debug.so \
	&& mv objs/ngx_http_geoip_module.so objs/ngx_http_geoip_module-debug.so \
	&& mv objs/ngx_stream_geoip_module.so objs/ngx_stream_geoip_module-debug.so \
	&& mv objs/ngx_nchan_module.so objs/ngx_nchan_module-debug.so \
	&& ./configure $CONFIG \
	&& make -j$(getconf _NPROCESSORS_ONLN) \
	&& make install \
	&& rm -rf /etc/nginx/html/ \
	&& mkdir /etc/nginx/conf.d/ \
	&& mkdir -p /usr/share/nginx/html/ \
	&& install -m644 html/index.html /usr/share/nginx/html/ \
	&& install -m644 html/50x.html /usr/share/nginx/html/ \
	&& install -m755 objs/nginx-debug /usr/sbin/nginx-debug \
	&& install -m755 objs/ngx_http_xslt_filter_module-debug.so /usr/lib/nginx/modules/ngx_http_xslt_filter_module-debug.so \
	&& install -m755 objs/ngx_http_image_filter_module-debug.so /usr/lib/nginx/modules/ngx_http_image_filter_module-debug.so \
	&& install -m755 objs/ngx_http_geoip_module-debug.so /usr/lib/nginx/modules/ngx_http_geoip_module-debug.so \
	&& install -m755 objs/ngx_stream_geoip_module-debug.so /usr/lib/nginx/modules/ngx_stream_geoip_module-debug.so \
	&& install -m755 objs/ngx_nchan_module-debug.so /usr/lib/nginx/modules/ngx_nchan_module-debug.so \
	&& ln -s ../../usr/lib/nginx/modules /etc/nginx/modules \
	&& strip /usr/sbin/nginx* \
	&& strip /usr/lib/nginx/modules/*.so \
	&& rm -rf /usr/src/nginx-$NGINX_VERSION /usr/src/nchan-${NCHAN_VERSION} \
	\
	# Bring in gettext so we can get `envsubst`, then throw
	# the rest away. To do this, we need to install `gettext`
	# then move `envsubst` out of the way so `gettext` can
	# be deleted completely, then move `envsubst` back.
	&& apk add --no-cache --virtual .gettext gettext \
	&& mv /usr/bin/envsubst /tmp/ \
	\
	&& runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' /usr/sbin/nginx /usr/lib/nginx/modules/*.so /tmp/envsubst \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)" \
	&& apk add --no-cache --virtual .nginx-rundeps $runDeps \
	&& apk del .build-deps \
	&& apk del .gettext \
	&& mv /tmp/envsubst /usr/local/bin/ \
	\
	# Bring in tzdata so users could set the timezones through the environment
	# variables
	&& apk add --no-cache tzdata






RUN mkdir -p /run/nginx/
RUN mkdir -p /var/log/nginx/
COPY site.conf /etc/nginx/conf.d/site.conf
COPY nginx.conf /etc/nginx/nginx.conf
# RUN rm /etc/nginx/conf.d/default.conf

# redis
RUN apk add redis
COPY redis.conf /etc/redis.conf


#Supervisor
COPY supervisord.conf /etc/supervisord.conf
RUN mkdir -p /var/log/supervisord/

# lastly
# needed for mysql
RUN chmod 777 /var/tmp

# Docker
RUN ln -sf /dev/stdout /var/log/nginx/access.log
RUN ln -sf /dev/stdout /var/log/nginx/error.log

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
