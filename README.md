# MapGuesser

This is the MapGuesser Application project. This is a game about guessing where you are based on a street view panorama - inspired by existing applications.

## Installation

### Clone the Git repository

The first step is obviously cloning the repository to your machine:

```
git clone https://bitbucket.org/esoko/mapguesser.git
```

All the commands listed here should be executed from the repository root.

### (Optional) Setup Docker stack

The easiest way to build up a fully working application with web server and database is to use Docker Compose with the included `docker-compose.yml`.

All you have to do is executing the following command:

```
docker-compose up -d
```

Attach shell to the container of `mapguesser_app`. All of the following commands should be executed there.

**If you don't use the Docker stack you need to install your environment manually. Check `docker-compose.yml` and `docker/Dockerfile` to see the system requirements.**

### Initialize project

This command installes all of the Composer requirements and creates a copy of the example `.env` file.

```
composer create-project
```

### Set environment variables

The `.env` file contains several environment variables that are needed by the application to work properly. These should be configured for your environment.

**You should set here the API keys that enable playing the game. Without these API keys the application cannot work well. To get Google API keys visit this page: https://console.developers.google.com/**

One very important variable is `DEV`. This indicates that the application operates in development (staging) and not in produciton mode.

If you install the application in the Docker stack for development (staging) environment, only the variables for external dependencies (API keys, map attribution, etc.) should be adapted. All other variables (for DB connection, static root, mailing, etc.) are fine with the default value.

### Finalize installation

After you set the environment variables in the `.env` file, execute the following command:

```
scripts/install.sh
```

**And you are done!** The application is ready to use and develop.

If you installed it in the Docker stack, you can reach it on http://localhost. The mails that are sent by the application can be found on http://localhost:8080/. If needed, the database server can be directly reached on localhost:3306.

---

*License: **GNU AGPL 3.0**. Full license text can be found in file `LICENSE`.*
