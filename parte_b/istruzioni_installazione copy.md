### **1. Cosa si sta installando**
L'applicazione in oggetto è un sistema gestionale web per stabilimento balneare (Caso A: Ristrutturazione) basata su un'architettura Java MVC.

### **2. Prerequisiti**
Prima di procedere con l'installazione, assicurarsi di disporre dei seguenti componenti installati e configurati nel OS:
- Java Development Kit (versione 8 o 21) installato.
- Apache Tomcat (versione 9 o 11) installato, coerentemente con la versione JDK scelta
- Database MySQL attivo e in esecuzione.

### **3. Esecuzione dell'installazione**
1. Aprire il terminale posizionandosi nella cartella principale del progetto estratto.
2. Avviare lo script di installazione specifico per la piattaforma d'uso:
    <ol type="a">
    <li>Windows eseguire: <code>.\install.bat</code> utilizzando il prompt dei comandi o windows terminal</li>
    <li>Unix/Mac eseguire: <code>sh install.sh</code> utilizzando il terminale</li>
    </ol>
3. Seguire le istruzioni testuali a schermo per inserire le credenziali (utente e porta di MySQL) 
4. Inserire la password del database quando richiesto dal flusso interattivo

### **4. Selezionare versione**
Durante la procedura il sistema chiederà di selezionare la coppia di tecnologie di esecuzione di cui si dispone. Scegliere rigorosamente tra una delle due configurazioni supportate: 
  - JDK 8 e Apache Tomcat 9 
  - JDK 21 e Apache Tomcat 11

### **5. Avvio dell'applicazione**
1. Individuare la cartella `webapps` all'interno della propria installazione di Apache Tomcat (es. `/usr/local/tomcat/webapps`su sistemi Unix/Linux o `C:\Program Files\Apache Software Foundation\Tomcat 11.0\webapps` su sistemi Windows).
2. Copiare la cartella `lido` (appena generata dallo script nella cartella `parte_b/lido_deploy/`) all'interno della cartella `webapps`.
3. Avviare il server Tomcat (tramite lo script `startup.sh` o `startup.bat`)
4. Aprire il browser web e digitare l'indirizzo: `http://localhost:8080/lido/` (sostituire 8080 in caso di porta Tomcat differente).

#### **Gestione e controllo tramite Tomcat Manager App**
Per verificare lo stato di esecuzione dell'applicazione, eseguire il reload rapido senza riavviare l'intero server, è possibile sfruttare l'interfaccia di amministrazione di Tomcat: 
1. **Configurazione preliminare delle credenziali:** l'accesso al manager richiede la configurazione di un utente abilitato. Per farlo aprire il file `conf/tomcat-users.xml` e aggiungere all'interno del tag principale la riga `<user username="admin" password = "TUA PASSWORD" roles="manager-gui>`
2. **Accesso alla dashboard:** aprire il browser e digitare `http://localhost:8080/manager/html`, inserendo le credenziali appena configurate 
3. **Funzionalità di collaudo:** 
    - nella sezione *Applications*, verificare che il percorso `/lido` abbia la colonna running impostata su true 
    - cliccare poi sul pulsante *Reload* associato a `/lido` per forzare il ri-parsing delle risorse o aggiornamenti minori senza spegnere il server


### **6. Risoluzione dei problemi comuni**
Di seguito vengono analizzate le situazioni critiche e le problematiche più frequenti che potrebbero verificarsi 

| **Problema** | **Causa principale** | **Soluzione** |  
|----- | ----- | -------- | 
|**Conflitto di Porta MySQL** |Il db non risponde sulla porta standard o l'applicazione fallisce la connessione JDBC | Verificare la stringa *DB_URL* nel file `Database.java` aggiornando esplicitamente il numero di porta |
|**Conflitti Tomcat** | Il server Tomcat non riesce ad avviarsi perchè la porta di default è impegnata | Modificare il file `conf/server.xml` all'interno della directory di Tomcat individuando il tag `<Connector port="8080"...>` e cambiando il valore della porta|
|**Incompatibilità di versione JDK/Tomcat**| Tentativo di eseguire classi non compatibili tra di loro| Verificare che la versione del JDK impostata nelle variabili di ambiente corrisponda effettivamente alla versione di Tomcat utilizzata per il deploy (JDK 8 - Tomcat 9 oppure JDK 21 - Tomcat 11). In caso non lo fosse modificare la voce JAVA_HOME (es. `C:\Program Files\Java\jdk-21.0.11\`)