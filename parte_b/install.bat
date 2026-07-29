@echo off
echo ===================================================
echo    LIDO SOLE E SABBIA - SCRIPT DI INSTALLAZIONE
echo ===================================================
echo.

set /p db_user="Inserisci l'utente MySQL (premi INVIO per 'root'): "
if "%db_user%"=="" set db_user=root

set /p db_port="Inserisci la porta MySQL (premi INVIO per '3306'): "
if "%db_port%"=="" set db_port=3306

set /p db_pass="Inserisci la password MySQL (premi INVIO se vuota): "

echo.
echo [1/3] Creazione e popolamento del database MySQL...
if "%db_pass%"=="" (
  mysql -u %db_user% -P %db_port% -e "CREATE DATABASE IF NOT EXISTS lido_db;"
  mysql -u %db_user% -P %db_port% lido_db < database\crea_db.sql
  mysql -u %db_user% -P %db_port% lido_db < database\seeding_spiaggia.sql
) else (
  mysql -u %db_user% -p"%db_pass%" -P %db_port% -e "CREATE DATABASE IF NOT EXISTS lido_db;"
  mysql -u %db_user% -p"%db_pass%" -P %db_port% lido_db < database\crea_db.sql
  mysql -u %db_user% -p"%db_pass%" -P %db_port% lido_db < database\seeding_spiaggia.sql
)
echo -^> Database pronto!
echo.

echo [2/3] Selezione della versione di Tomcat...
echo.
echo Quale versione di Tomcat e' installata sul sistema?
echo 1 - Tomcat 9 (Ambiente Java 8)
echo 2 - Tomcat 11 (Ambiente Java 21)
echo.
set /p tomcat_ver="Digita 1 oppure 2 e premi INVIO: "

:: Pulisce vecchie installazioni se presenti
if exist "lido_deploy" rmdir /S /Q "lido_deploy"
mkdir "lido_deploy\lido"

if "%tomcat_ver%"=="1" (
  xcopy /E /I /Q "build\java8\lido" "lido_deploy\lido" >nul
  echo -^> Preparata l'applicazione per Tomcat 9!
) else (
  xcopy /E /I /Q "build\java21\lido" "lido_deploy\lido" >nul
  echo -^> Preparata l'applicazione per Tomcat 11!
)

echo Creazione del file di configurazione del database...
:: Crea la cartella classes se non esiste e scrive il file di configurazione
if not exist "lido_deploy\lido\WEB-INF\classes" mkdir "lido_deploy\lido\WEB-INF\classes"
(
  echo db.user=%db_user%
  echo db.pass=%db_pass%
  echo db.port=%db_port%
) > "lido_deploy\lido\WEB-INF\classes\db.properties"
echo -^> Configurazione completata!
echo.

echo ===================================================
echo [3/3] INSTALLAZIONE QUASI TERMINATA!
echo.
echo 1. Trova la cartella 'webapps' di Tomcat.
echo 2. Apri la cartella 'lido_deploy' appena creata in questa directory.
echo 3. Copia la cartella 'lido' contenuta al suo interno dentro 'webapps'.
echo 4. Avvia Tomcat ed entra all'indirizzo: http://localhost:8080/lido/
echo    (Sostituire 8080 se Tomcat utilizza una porta diversa).
echo ===================================================
pause