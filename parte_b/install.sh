#!/bin/bash
echo "==================================================="
echo "   LIDO SOLE E SABBIA - SCRIPT DI INSTALLAZIONE"
echo "==================================================="
echo "ATTENZIONE: Prima di continuare, assicurarsi di aver"
echo "inserito la porta e le credenziali corrette nel file:"
echo "lido/src/main/java/com/treuominiemezzo/lido/util/Database.java"
echo "==================================================="
read -p "Premere INVIO per continuare o Ctrl+C per annullare..."
echo ""

read -p "Inserire l'utente MySQL (default: root): " db_user
db_user=${db_user:-root}

read -p "Inserire la porta MySQL (default: 3306): " db_port
db_port=${db_port:-3306}

echo ""
echo "[1/3] Creazione e popolamento del database MySQL..."
echo "(Verra' richiesta la password per l'utente $db_user)"
mysql -u $db_user -p -P $db_port -e "CREATE DATABASE IF NOT EXISTS lido_db;"
mysql -u $db_user -p -P $db_port lido_db < database/crea_db.sql
mysql -u $db_user -p -P $db_port lido_db < database/seeding_spiaggia.sql
echo "-> Database pronto!"
echo ""

echo "[2/3] Compilazione del progetto con Maven..."
cd lido
mvn clean package
cd ..
echo "-> Compilazione completata!"
echo ""

echo "==================================================="
echo "[3/3] INSTALLAZIONE QUASI TERMINATA!"
echo ""
echo "Per avviare il gestionale, trovare la cartella 'webapps' di Tomcat."
echo "Di default si trova in percorsi simili a:"
echo "- /usr/local/tomcat/webapps"
echo "- /opt/tomcat/webapps"
echo "- /Library/Tomcat/webapps (Mac)"
echo ""
echo "1. Copiare il file 'lido/target/lido.war' dentro 'webapps'."
echo "2. Avviare Tomcat (es. eseguendo ./startup.sh nella cartella 'bin')."
echo "3. Aprire il browser all'indirizzo: http://localhost:8080/lido/"
echo "   (Sostituire 8080 se Tomcat utilizza una porta diversa)."
echo "==================================================="