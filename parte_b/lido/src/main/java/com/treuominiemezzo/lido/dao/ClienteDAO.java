package com.treuominiemezzo.lido.dao;

import com.treuominiemezzo.lido.model.Cliente;
import com.treuominiemezzo.lido.util.Database;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class ClienteDAO {

    // Metodo per estrarre tutti i clienti
    public List<Cliente> getAllClienti() {
        List<Cliente> clienti = new ArrayList<>();
        // Query base: ordiniamo magari per cognome e nome
        String query = "SELECT codice, nome, cognome, dataNascita, email, telefono, indirizzo FROM Cliente ORDER BY cognome, nome";

        // Usiamo il try-with-resources per chiudere in automatico Statement e ResultSet
        try (Connection conn = Database.getConnection();
             PreparedStatement stmt = conn.prepareStatement(query);
             ResultSet rs = stmt.executeQuery()) {

            while (rs.next()) {
                // Leggiamo i dati dal Result Set
                int codice = rs.getInt("codice");
                String nome = rs.getString("nome");
                String cognome = rs.getString("cognome");
                
                // Conversione della data SQL a LocalDate (gestione dei NULL)
                java.sql.Date sqlDate = rs.getDate("dataNascita");
                LocalDate dataNascita = (sqlDate != null) ? sqlDate.toLocalDate() : null;
                
                String email = rs.getString("email");
                String telefono = rs.getString("telefono");
                String indirizzo = rs.getString("indirizzo");

                // Creiamo l'oggetto Cliente e lo aggiungiamo alla lista
                Cliente c = new Cliente(codice, nome, cognome, dataNascita, email, telefono, indirizzo);
                clienti.add(c);
            }

        } catch (SQLException e) {
            System.err.println("Errore nel recupero dei clienti: " + e.getMessage());
            e.printStackTrace();
        }

        return clienti;
    }
}