#!/bin/bash
# Install script for Mac/Linux environments

echo "==================================================="
echo "   LIDO SOLE E SABBIA - SCRIPT DI INSTALLAZIONE"
echo "==================================================="
echo ""

# Database configuration prompts
read -p "Inserire l'utente MySQL (default: root): " db_user
db_user=${db_user:-root}

read -p "Inserire la porta MySQL (default: 3306): " db_port
db_port=${db_port:-3306}

# Prompt silenzioso per la password
read -s -p "Inserire la password MySQL (premere INVIO se vuota): " db_pass
echo ""

echo ""
echo "[1/3] Creazione e popolamento del database MySQL..."

# Execute SQL scripts based on password presence
if [ -z "$db_pass" ]; then
  mysql -u "$db_user" -P "$db_port" -e "CREATE DATABASE IF NOT EXISTS lido_db;"
  mysql -u "$db_user" -P "$db_port" lido_db < database/crea_db.sql
  mysql -u "$db_user" -P "$db_port" lido_db < database/seeding_spiaggia.sql
else
  mysql -u "$db_user" -p"$db_pass" -P "$db_port" -e "CREATE DATABASE IF NOT EXISTS lido_db;"
  mysql -u "$db_user" -p"$db_pass" -P "$db_port" lido_db < database/crea_db.sql
  mysql -u "$db_user" -p"$db_pass" -P "$db_port" lido_db < database/seeding_spiaggia.sql
fi
echo "-> Database pronto!"
echo ""

echo "[2/3] Selezione della versione di Tomcat..."
echo ""
echo "Quale versione di Tomcat e' installata sul sistema?"
echo "1 - Tomcat 9 (Ambiente Java 8)"
echo "2 - Tomcat 11 (Ambiente Java 21)"
echo ""

read -p "Digita 1 oppure 2 e premi INVIO: " tomcat_ver

# Prepare target directory
rm -rf lido_deploy
mkdir -p lido_deploy/lido

if [ "$tomcat_ver" = "1" ]; then
  cp -r build/java8/lido/* lido_deploy/lido/
  echo "-> Preparata l'applicazione per Tomcat 9!"
else
  cp -r build/java21/lido/* lido_deploy/lido/
  echo "-> Preparata l'applicazione per Tomcat 11!"
fi

echo "Creazione del file di configurazione del database..."
mkdir -p lido_deploy/lido/WEB-INF/classes
cat <<EOF > lido_deploy/lido/WEB-INF/classes/db.properties
db.user=$db_user
db.pass=$db_pass
db.port=$db_port
EOF
echo "-> Configurazione completata!"
echo ""

echo "==================================================="
echo "[3/3] INSTALLAZIONE QUASI TERMINATA!"
echo ""
echo "1. Trova la cartella 'webapps' di Tomcat."
echo "2. Apri la cartella 'lido_deploy' appena creata in questa directory."
echo "3. Copia la cartella 'lido' contenuta al suo interno dentro 'webapps'."
echo "4. Avvia Tomcat ed entra all'indirizzo: http://localhost:8080/lido/"
echo "   (Sostituire 8080 se Tomcat utilizza una porta diversa)."
echo "==================================================="