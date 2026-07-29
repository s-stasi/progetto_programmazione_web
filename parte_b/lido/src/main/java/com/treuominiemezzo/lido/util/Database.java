package com.treuominiemezzo.lido.util;

import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;

public class Database {private static String URL;
    private static String USER;
    private static String PASS;

    static {
        // Cerca il file db.properties all'interno del pacchetto compilato
        try (InputStream input = Thread.currentThread().getContextClassLoader().getResourceAsStream("db.properties")) {
            Properties prop = new Properties();
            if (input != null) {
                prop.load(input);
                URL = "jdbc:mysql://localhost:" + prop.getProperty("db.port", "3306") + "/lido_db";
                USER = prop.getProperty("db.user", "root");
                PASS = prop.getProperty("db.pass", "");
            } else {
                // Valori di default nel caso in cui il file non venga trovato
                URL = "jdbc:mysql://localhost:3306/lido_db";
                USER = "root";
                PASS = "";
            }
            Class.forName("com.mysql.cj.jdbc.Driver");
        } catch (Exception e) {
            System.err.println("Errore nel caricamento del file db.properties");
            e.printStackTrace();
        }
    }

    public static Connection getConnection() throws SQLException {
      return DriverManager.getConnection(URL, USER, PASS);
    }
}