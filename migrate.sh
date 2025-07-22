#!/bin/bash

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW} Parando e removendo containers e volumes antigos...${NC}"
docker-compose down -v
if [ $? -ne 0 ]; then
    echo "Erro ao parar os containers. Abortando."
    exit 1
fi

echo -e "\n${YELLOW} Iniciando novos containers em background...${NC}"
docker-compose up -d
if [ $? -ne 0 ]; then
    echo "Erro ao iniciar os containers. Abortando."
    exit 1
fi

echo -e "\n${YELLOW}󱎫 Aguardando o serviço de banco de dados (db) ficar pronto (20 segundos)...${NC}"
sleep 20

echo -e "\n${YELLOW}󰥝 Iniciando a importação dos scripts SQL...${NC}"

for sql_file in $(ls database/*.sql | sort -V); do
    if [ -f "$sql_file" ]; then
        echo "  󱦰 Importando: $sql_file"
        docker-compose exec -T db mysql --default-character-set=utf8mb4 -uadmin -psecret gmleads_db < "$sql_file"
    fi
done

echo -e "\n${GREEN} Processo de recriação do banco de dados concluído com sucesso!${NC}"
