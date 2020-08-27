# Matecat Docker

Dockerization for [https://github.com/matecat/MateCat](https://github.com/matecat/MateCat)

# Prerequisites

* Docker
* Docker Compose

# Configuration

1. Clone this Docker repo

```sh
git clone https://github.com/CrossLangNV/matecat-docker.git
```

2. Clone **Matecat** source 

```sh
git clone https://github.com/matecat/MateCat.git ~/matecat
```

3. Copy/move Matecat source folder to `MateCatApache/matecat`

4. Provide a valid `oauth.config.ini` file in `MateCatApache/app_configs` for Google OAuth

# Start Docker

```sh
docker-compose up
```