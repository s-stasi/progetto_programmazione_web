package com.treuominiemezzo.lido.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class Database {
    // Aggiorna questi dati con quelli del tuo DB locale o remoto
    private static final String URL = "jdbc:mysql://localhost:3306/lido_db?serverTimezone=UTC";
    private static final String USER = "root";
    private static final String PASS = "samuele2002"; 
    
    private static Connection connection = null;

    private Database() {}

    public static Connection getConnection() throws SQLException {
        if (connection == null || connection.isClosed()) {
            try {
                // Carica il driver JDBC
                Class.forName("com.mysql.cj.jdbc.Driver");
                connection = DriverManager.getConnection(URL, USER, PASS);
            } catch (ClassNotFoundException e) {
                throw new SQLException("Driver MySQL non trovato", e);
            }
        }
        return connection;
    }
}