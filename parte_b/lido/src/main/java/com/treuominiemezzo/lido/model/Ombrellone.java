package com.treuominiemezzo.lido.model;

public class Ombrellone {

  private int idOmbrellone;
  private int settore;
  private int numeroFila;
  private int numeroOrdine;
  private String tipologiaNome;
  private int occupato;

  public Ombrellone() {}

  public Ombrellone(int idOmbrellone, int settore, int numeroFila, int numeroOrdine,
      String tipologiaNome, int occupato) {
    this.idOmbrellone = idOmbrellone;
    this.settore = settore;
    this.numeroFila = numeroFila;
    this.numeroOrdine = numeroOrdine;
    this.tipologiaNome = tipologiaNome;
    this.occupato = occupato;
  }

  public int getIdOmbrellone() {
    return idOmbrellone;
  }

  public void setIdOmbrellone(int idOmbrellone) {
    this.idOmbrellone = idOmbrellone;
  }

  public int getSettore() {
    return settore;
  }

  public void setSettore(int settore) {
    this.settore = settore;
  }

  public int getNumeroFila() {
    return numeroFila;
  }

  public void setNumeroFila(int numeroFila) {
    this.numeroFila = numeroFila;
  }

  public int getNumeroOrdine() {
    return numeroOrdine;
  }

  public void setNumeroOrdine(int numeroOrdine) {
    this.numeroOrdine = numeroOrdine;
  }

  public String getTipologiaNome() {
    return tipologiaNome;
  }

  public void setTipologiaNome(String tipologiaNome) {
    this.tipologiaNome = tipologiaNome;
  }

  public int getOccupato() {
    return occupato;
  }

  public void setOccupato(int occupato) {
    this.occupato = occupato;
  }
}
