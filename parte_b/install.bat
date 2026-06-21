@echo off
echo ===================================================
echo    LIDO SOLE E SABBIA - SCRIPT DI INSTALLAZIONE
echo ===================================================
echo ATTENZIONE: Prima di continuare, assicurati di aver
echo inserito la porta e le credenziali corrette nel file:
echo lido\src\main\java\com\treuominiemezzo\lido\util\Database.java
echo ===================================================
pause
echo.

set /p db_user="Inserisci l'utente MySQL (premi INVIO per 'root'): "
if "%db_user%"=="" set db_user=root

set /p db_port="Inserisci la porta MySQL (premi INVIO per '3306'): "
if "%db_port%"=="" set db_port=3306

echo.
echo [1/3] Creazione e popolamento del database MySQL...
echo (Verra' richiesta la password per l'utente %db_user%)
mysql -u %db_user% -p -P %db_port% -e "CREATE DATABASE IF NOT EXISTS lido_db;"
mysql -u %db_user% -p -P %db_port% lido_db < database\crea_db.sql
mysql -u %db_user% -p -P %db_port% lido_db < database\seeding_spiaggia.sql
echo -^> Database pronto!
echo.

echo [2/3] Compilazione del progetto con Maven...
cd lido
call mvn clean package
cd ..
echo -^> Compilazione completata!
echo.

echo ===================================================
echo [3/3] INSTALLAZIONE QUASI TERMINATA!
echo.
echo Per avviare il gestionale, trovare la cartella 'webapps' di Tomcat.
echo Di default si trova in percorsi simili a:
echo - C:\Program Files\Apache Software Foundation\Tomcat 11.0\webapps
echo - C:\xampp\tomcat\webapps
echo.
echo 1. Copiare il file appena generato "lido\target\lido.war" dentro 'webapps'.
echo 2. Avviare Tomcat (es. eseguendo startup.bat nella cartella 'bin').
echo 3. Aprire il browser all'indirizzo: http://localhost:8080/lido/
echo    (Sostituire 8080 se Tomcat utilizza una porta diversa).
echo ===================================================
pause