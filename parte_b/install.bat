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

echo [2/3] Selezione della versione di Tomcat...
echo.
echo Quale versione di Tomcat e' installata sul sistema?
echo 1 - Tomcat 9 (Ambiente Java 8)
echo 2 - Tomcat 11 (Ambiente Java 21)
echo.
set /p tomcat_ver="Digita 1 oppure 2 e premi INVIO: "

if "%tomcat_ver%"=="1" (
    copy build\lido_java8.war lido.war >nul
    echo -^> Preparato pacchetto lido.war per Tomcat 9!
) else (
    copy build\lido_java21.war lido.war >nul
    echo -^> Preparato pacchetto lido.war per Tomcat 11!
)
echo.

echo ===================================================
echo [3/3] INSTALLAZIONE QUASI TERMINATA!
echo.
echo Per avviare il gestionale, trovare la cartella 'webapps' di Tomcat.
echo Di default si trova in percorsi simili a:
echo - C:\Program Files\Apache Software Foundation\Tomcat 11.0\webapps
echo - C:\xampp\tomcat\webapps
echo.
echo 1. Copiare il file "lido.war" appena generato in questa cartella dentro 'webapps'.
echo 2. Avviare Tomcat (es. eseguendo startup.bat nella cartella 'bin').
echo 3. Aprire il browser all'indirizzo: http://localhost:8080/lido/
echo    (Sostituire 8080 se Tomcat utilizza una porta diversa).
echo ===================================================
pause