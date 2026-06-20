package com.treuominiemezzo.lido.model;

import java.time.LocalDate;

public class Prenotazione {

  private int id;
  private String nome;
  private String cognome;
  private LocalDate dataNascita;
  private String indirizzo;
  private String email;
  private String cellulare;
  private double prezzoTotale;
  private LocalDate dataInizio;
  private LocalDate dataFine;

  public Prenotazione() {}

  public int getId() {
    return id;
  }

  public void setId(int id) {
    this.id = id;
  }

  public String getNome() {
    return nome;
  }

  public void setNome(String nome) {
    this.nome = nome;
  }

  public String getCognome() {
    return cognome;
  }

  public void setCognome(String cognome) {
    this.cognome = cognome;
  }

  public LocalDate getDataNascita() {
    return dataNascita;
  }

  public void setDataNascita(LocalDate dataNascita) {
    this.dataNascita = dataNascita;
  }

  public String getIndirizzo() {
    return indirizzo;
  }

  public void setIndirizzo(String indirizzo) {
    this.indirizzo = indirizzo;
  }

  public String getEmail() {
    return email;
  }

  public void setEmail(String email) {
    this.email = email;
  }

  public String getCellulare() {
    return cellulare;
  }

  public void setCellulare(String cellulare) {
    this.cellulare = cellulare;
  }

  public double getPrezzoTotale() {
    return prezzoTotale;
  }

  public void setPrezzoTotale(double prezzoTotale) {
    this.prezzoTotale = prezzoTotale;
  }

  public LocalDate getDataInizio() {
    return dataInizio;
  }

  public void setDataInizio(LocalDate dataInizio) {
    this.dataInizio = dataInizio;
  }

  public LocalDate getDataFine() {
    return dataFine;
  }

  public void setDataFine(LocalDate dataFine) {
    this.dataFine = dataFine;
  }
}
