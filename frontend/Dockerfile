FROM node:latest

ARG USER_ID=1000
ARG GROUP_ID=1000

# Crear grupo solo si no existe
RUN if ! getent group ${GROUP_ID}; then \
        groupadd -g ${GROUP_ID} devgroup; \
    fi

# Crear usuario solo si no existe
RUN if ! id -u ${USER_ID} >/dev/null 2>&1; then \
        useradd -m -u ${USER_ID} -g ${GROUP_ID} devuser; \
    fi

WORKDIR /app

# Dar permisos al directorio de trabajo
RUN chown -R ${USER_ID}:${GROUP_ID} /app

USER ${USER_ID}

# Instalar CLI de TanStack Start
RUN npm create @tanstack/start@latest

# Instalar dependencias para dev al arrancar
CMD ["sh", "-c", "npm install && npm run dev -- --host"]
