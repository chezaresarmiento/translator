# Use Debian-based PHP-FPM image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Update package list and upgrade installed packages
RUN apt-get update && apt-get upgrade -y

# Install system dependencies
RUN apt-get install -y \
    bash \
    curl \
    wget \
    unzip \
    build-essential \
    python3 \
    python3-pip \
    python3-venv \
    ffmpeg \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libbz2-dev \
    libzip-dev \
    pkg-config \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install GD extension with zlib support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql bz2 zip


# Create a virtual environment for Python packages
RUN python3 -m venv /opt/whisper-venv

# Upgrade pip with increased timeout
RUN /opt/whisper-venv/bin/pip install --default-timeout=100 --upgrade pip setuptools wheel

# Install PyTorch and Whisper with increased timeout
RUN /opt/whisper-venv/bin/pip install --default-timeout=100 torch torchaudio -f https://download.pytorch.org/whl/cpu.html
RUN /opt/whisper-venv/bin/pip install --default-timeout=100 openai-whisper

# Verify Whisper installation
RUN /opt/whisper-venv/bin/whisper --help || echo "Whisper installed"

# Add virtual environment to PATH
ENV PATH="/opt/whisper-venv/bin:$PATH"

# Set Whisper cache directory
ENV WHISPER_CACHE_DIR="/root/.cache/whisper"

# Ensure cache directory exists
RUN mkdir -p $WHISPER_CACHE_DIR

# Pre-download the Whisper Medium model to prevent runtime downloads
RUN /opt/whisper-venv/bin/python3 -c "import whisper; whisper.load_model('medium')"

# Set up PHP-FPM to use Laravel user
ENV PHPGROUP=laravel
ENV PHPUSER=laravel
RUN groupadd -r ${PHPGROUP} && useradd -m -g ${PHPGROUP} -s /bin/bash ${PHPUSER}
RUN sed -i 's/user = www-data/user = ${PHPUSER}/g' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/group = www-data/group = ${PHPGROUP}/g' /usr/local/etc/php-fpm.d/www.conf

# Install yt-dlp inside a virtual environment
RUN python3 -m venv /opt/venv
RUN /opt/venv/bin/pip install yt-dlp
ENV PATH="/opt/venv/bin:$PATH"

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure PHP upload settings
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
RUN sed -i 's/^upload_max_filesize.*/upload_max_filesize = 2000M/' /usr/local/etc/php/php.ini || echo "upload_max_filesize = 2000M" >> /usr/local/etc/php/php.ini
RUN sed -i 's/^post_max_size.*/post_max_size = 2000M/' /usr/local/etc/php/php.ini || echo "post_max_size = 2000M" >> /usr/local/etc/php/php.ini
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Install Node.js and npm
RUN apt-get update && apt-get install -y nodejs npm

# Set up Laravel storage permissions
RUN mkdir -p /var/www/html/storage/logs && \
    chown -R ${PHPUSER}:${PHPGROUP} /var/www/html/storage && \
    chmod -R 775 /var/www/html/storage

# Copy Supervisor configuration
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
