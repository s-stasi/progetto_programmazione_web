package com.treuominiemezzo.lido.model;

import java.time.LocalDate;

public class Contratto {
  private int numProgr;
  private LocalDate dataStipula;
  private double importo;
  
  // Dati correlati
  private String clienteNomeCognome;
  private int idOmbrellone;
  private String tipologiaOmbrellone;
  private LocalDate inizioPrenotazione;
  private LocalDate finePrenotazione;

  // Costruttore vuoto
  public Contratto() {}

  // Costruttore completo per la dashboard
  public Contratto(int numProgr, LocalDate dataStipula, double importo, String clienteNomeCognome, 
                   int idOmbrellone, String tipologiaOmbrellone, LocalDate inizioPrenotazione, LocalDate finePrenotazione) {
    this.numProgr = numProgr;
    this.dataStipula = dataStipula;
    this.importo = importo;
    this.clienteNomeCognome = clienteNomeCognome;
    this.idOmbrellone = idOmbrellone;
    this.tipologiaOmbrellone = tipologiaOmbrellone;
    this.inizioPrenotazione = inizioPrenotazione;
    this.finePrenotazione = finePrenotazione;
  }

  // Getters e Setters
  public int getNumProgr() { return numProgr; }
  public void setNumProgr(int numProgr) { this.numProgr = numProgr; }
  public LocalDate getDataStipula() { return dataStipula; }
  public void setDataStipula(LocalDate dataStipula) { this.dataStipula = dataStipula; }
  public double getImporto() { return importo; }
  public void setImporto(double importo) { this.importo = importo; }
  public String getClienteNomeCognome() { return clienteNomeCognome; }
  public void setClienteNomeCognome(String clienteNomeCognome) { this.clienteNomeCognome = clienteNomeCognome; }
  public int getIdOmbrellone() { return idOmbrellone; }
  public void setIdOmbrellone(int idOmbrellone) { this.idOmbrellone = idOmbrellone; }
  public String getTipologiaOmbrellone() { return tipologiaOmbrellone; }
  public void setTipologiaOmbrellone(String tipologiaOmbrellone) { this.tipologiaOmbrellone = tipologiaOmbrellone; }
  public LocalDate getInizioPrenotazione() { return inizioPrenotazione; }
  public void setInizioPrenotazione(LocalDate inizioPrenotazione) { this.inizioPrenotazione = inizioPrenotazione; }
  public LocalDate getFinePrenotazione() { return finePrenotazione; }
  public void setFinePrenotazione(LocalDate finePrenotazione) { this.finePrenotazione = finePrenotazione; }
  
  // Metodi "scorciatoia" usati dal Javascript nel frontend
  public String getNomeSolo() { 
    return clienteNomeCognome != null ? clienteNomeCognome.split(" ")[0] : ""; 
  }
  public String getCognomeSolo() { 
    if (clienteNomeCognome == null || !clienteNomeCognome.contains(" ")) return "";
    return clienteNomeCognome.substring(clienteNomeCognome.indexOf(" ") + 1);
  }
}