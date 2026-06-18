package com.treuominiemezzo.lido.model;

import java.time.LocalDate;

public class Cliente {
    private int codice;
    private String nome;
    private String cognome;
    private LocalDate dataNascita;
    private String email;
    private String telefono;
    private String indirizzo;
    
    private int numContratti;

    public Cliente() {}

    public Cliente(int codice, String nome, String cognome, LocalDate dataNascita, String email, String telefono, String indirizzo, int numContratti) {
        this.codice = codice;
        this.nome = nome;
        this.cognome = cognome;
        this.dataNascita = dataNascita;
        this.email = email;
        this.telefono = telefono;
        this.indirizzo = indirizzo;
        this.numContratti = numContratti;
    }

    public int getNumContratti() { return numContratti; }
    public void setNumContratti(int numContratti) { this.numContratti = numContratti; }
    public int getCodice() { return codice; }
    public void setCodice(int codice) { this.codice = codice; }
    public String getNome() { return nome; }
    public void setNome(String nome) { this.nome = nome; }
    public String getCognome() { return cognome; }
    public void setCognome(String cognome) { this.cognome = cognome; }
    public LocalDate getDataNascita() { return dataNascita; }
    public void setDataNascita(LocalDate dataNascita) { this.dataNascita = dataNascita; }
    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }
    public String getTelefono() { return telefono; }
    public void setTelefono(String telefono) { this.telefono = telefono; }
    public String getIndirizzo() { return indirizzo; }
    public void setIndirizzo(String indirizzo) { this.indirizzo = indirizzo; }
}