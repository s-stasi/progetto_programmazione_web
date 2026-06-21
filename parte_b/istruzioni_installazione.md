**1. Cosa si sta installando**

Applicazione web gestionale per stabilimento balneare (Caso A: Ristrutturazione) basata su architettura Java MVC.

**2. Prerequisiti**

- Java Development Kit (JDK) 21 installato.
- Apache Tomcat (9 o 11) installato.
- Database MySQL in esecuzione.
- Apache Maven installato e configurato nelle variabili d'ambiente.

**3. Procedura di configurazione preliminare**

1. Estrarre l'archivio ZIP in una directory locale.
2. Aprire il file `lido/src/main/java/com/treuominiemezzo/lido/util/Database.java` utilizzando un editor di testo.
3. Modificare le variabili `DB_URL` (inclusa la porta), `USER` e `PASS` inserendo i parametri corretti per il proprio server MySQL locale.
4. Salvare il file.

**4. Esecuzione dell'installazione**

1. Aprire il terminale nella cartella principale del progetto estratto.
2. Su sistemi Windows, eseguire: `.\install.bat` utilizzando il prompt dei comandi o windows terminal
3. Su sistemi Unix/Mac, eseguire: `sh install.sh` utilizzando il terminale
4. Seguire le istruzioni a schermo per inserire utente e porta di MySQL.
5. Inserire la password del database quando richiesta. (verrà richiesta più volte)
6. Attendere il completamento della compilazione Maven.

**5. Avvio dell'applicazione**

1. Individuare la cartella `webapps` all'interno della propria installazione di Apache Tomcat (es. `/usr/local/tomcat/webapps` o `C:\Program Files\Apache Software Foundation\Tomcat 11.0\webapps`).
2. Copiare il file `lido.war` (appena generato dallo script nella cartella `lido/target/`) all'interno della cartella `webapps`.
3. Avviare il server Tomcat.
4. Aprire il browser web e digitare l'indirizzo: `http://localhost:8080/lido/` (sostituire 8080 in caso di porta Tomcat differente).