package com.treuominiemezzo.lido.dao;

import com.treuominiemezzo.lido.model.Prenotazione;
import com.treuominiemezzo.lido.util.Database;

import java.sql.Connection;
import java.sql.Date;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Exception;
import java.sql.Statement;
import java.time.LocalDate;
import java.util.Optional;

public class PrenotazioneDAO {

  private static final String FIND_QUERY = """
      SELECT
          c.numProgr AS id,
          cl.nome,
          cl.cognome,
          cl.dataNascita AS data_nascita,
          cl.indirizzo,
          cl.email,
          cl.telefono AS cellulare,
          c.importo AS prezzo_totale,
          (SELECT MIN(data) FROM OmbrelloneVenduto WHERE contratto = c.numProgr) AS data_inizio,
          (SELECT MAX(data) FROM OmbrelloneVenduto WHERE contratto = c.numProgr) AS data_fine
      FROM OmbrelloneVenduto ov
      JOIN Contratto c ON ov.contratto = c.numProgr
      JOIN Cliente cl ON c.stipulatoDa = cl.codice
      WHERE ov.idOmbrellone = ? AND ov.data = ?
      LIMIT 1
      """;

  public Optional<Prenotazione> findByUmbrellaAndDate(int idOmbrellone, LocalDate dataInizio) {
    try (Connection conn = Database.getConnection();
        PreparedStatement stmt = conn.prepareStatement(FIND_QUERY)) {

      stmt.setInt(1, idOmbrellone);
      stmt.setDate(2, Date.valueOf(dataInizio));

      try (ResultSet rs = stmt.executeQuery()) {
        if (rs.next()) {
          return Optional.of(mapRow(rs));
        }
      }
    } catch (Exception e) {
      System.err.println("Error fetching reservation: " + e.getMessage());
      e.printStackTrace();
    }
    return Optional.empty();
  }

  public String createReservation(int idOmbrellone, String nome, String cognome, LocalDate dataNascita,
      String email, String telefono, String indirizzo, LocalDate dataInizio, LocalDate dataFine,
      double prezzoTotale) {
    Connection conn = null;
    try {
      conn = Database.getConnection();
      conn.setAutoCommit(false);

      Integer idCliente = findClienteByEmail(conn, email);
      if (idCliente == null) {
        idCliente = insertCliente(conn, nome, cognome, dataNascita, email, telefono, indirizzo);
      }

      int numContratto = insertContratto(conn, prezzoTotale, idCliente);
      insertDailyBookings(conn, idOmbrellone, numContratto, dataInizio, dataFine);

      conn.commit();
      return "SUCCESS";
    } catch (SQLException e) {
      if (conn != null) {
        try {
          conn.rollback();
        } catch (SQLException rollbackEx) {
          rollbackEx.printStackTrace();
        }
      }
      if (e.getErrorCode() == 1062) {
        return "DUPLICATE";
      }
      e.printStackTrace();
      return "ERROR";
    } finally {
      if (conn != null) {
        try {
          conn.setAutoCommit(true);
        } catch (SQLException e) {
          e.printStackTrace();
        }
      }
    }
  }

  public boolean deleteReservation(int idContratto) {
    Connection conn = null;
    try {
      conn = Database.getConnection();
      conn.setAutoCommit(false);

      try (PreparedStatement stmtOv = conn.prepareStatement(
          "DELETE FROM OmbrelloneVenduto WHERE contratto = ?")) {
        stmtOv.setInt(1, idContratto);
        stmtOv.executeUpdate();
      }

      try (PreparedStatement stmtC = conn.prepareStatement(
          "DELETE FROM Contratto WHERE numProgr = ?")) {
        stmtC.setInt(1, idContratto);
        int rows = stmtC.executeUpdate();
        if (rows == 0) {
          conn.rollback();
          return false;
        }
      }

      conn.commit();
      return true;
    } catch (SQLException e) {
      if (conn != null) {
        try {
          conn.rollback();
        } catch (SQLException rollbackEx) {
          rollbackEx.printStackTrace();
        }
      }
      e.printStackTrace();
      return false;
    } finally {
      if (conn != null) {
        try {
          conn.setAutoCommit(true);
        } catch (SQLException e) {
          e.printStackTrace();
        }
      }
    }
  }

  private Prenotazione mapRow(ResultSet rs) throws SQLException {
    Prenotazione p = new Prenotazione();
    p.setId(rs.getInt("id"));
    p.setNome(rs.getString("nome"));
    p.setCognome(rs.getString("cognome"));

    Date sqlNascita = rs.getDate("data_nascita");
    if (sqlNascita != null) {
      p.setDataNascita(sqlNascita.toLocalDate());
    }

    p.setIndirizzo(rs.getString("indirizzo"));
    p.setEmail(rs.getString("email"));
    p.setCellulare(rs.getString("cellulare"));
    p.setPrezzoTotale(rs.getDouble("prezzo_totale"));

    Date sqlInizio = rs.getDate("data_inizio");
    if (sqlInizio != null) {
      p.setDataInizio(sqlInizio.toLocalDate());
    }

    Date sqlFine = rs.getDate("data_fine");
    if (sqlFine != null) {
      p.setDataFine(sqlFine.toLocalDate());
    }

    return p;
  }

  private Integer findClienteByEmail(Connection conn, String email) throws SQLException {
    if (email == null || email.isBlank()) {
      return null;
    }

    try (PreparedStatement stmt = conn.prepareStatement(
        "SELECT codice FROM Cliente WHERE email = ? LIMIT 1")) {
      stmt.setString(1, email);
      try (ResultSet rs = stmt.executeQuery()) {
        if (rs.next()) {
          return rs.getInt("codice");
        }
      }
    }
    return null;
  }

  private int insertCliente(Connection conn, String nome, String cognome, LocalDate dataNascita,
      String email, String telefono, String indirizzo) throws SQLException {
    try (PreparedStatement stmt = conn.prepareStatement(
        "INSERT INTO Cliente (nome, cognome, dataNascita, email, telefono, indirizzo) VALUES (?, ?, ?, ?, ?, ?)",
        Statement.RETURN_GENERATED_KEYS)) {

      stmt.setString(1, nome);
      stmt.setString(2, cognome);
      if (dataNascita != null) {
        stmt.setDate(3, Date.valueOf(dataNascita));
      } else {
        stmt.setNull(3, java.sql.Types.DATE);
      }
      stmt.setString(4, email);
      stmt.setString(5, telefono);
      stmt.setString(6, indirizzo);
      stmt.executeUpdate();

      try (ResultSet keys = stmt.getGeneratedKeys()) {
        if (keys.next()) {
          return keys.getInt(1);
        }
      }
    }
    throw new SQLException("Failed to retrieve generated client id.");
  }

  private int insertContratto(Connection conn, double importo, int idCliente) throws SQLException {
    try (PreparedStatement stmt = conn.prepareStatement(
        "INSERT INTO Contratto (data, importo, stipulatoDa) VALUES (?, ?, ?)",
        Statement.RETURN_GENERATED_KEYS)) {

      stmt.setDate(1, Date.valueOf(LocalDate.now()));
      stmt.setDouble(2, importo);
      stmt.setInt(3, idCliente);
      stmt.executeUpdate();

      try (ResultSet keys = stmt.getGeneratedKeys()) {
        if (keys.next()) {
          return keys.getInt(1);
        }
      }
    }
    throw new SQLException("Failed to retrieve generated contract id.");
  }

  private void insertDailyBookings(Connection conn, int idOmbrellone, int numContratto,
      LocalDate dataInizio, LocalDate dataFine) throws SQLException {

    try (PreparedStatement stmtDay = conn.prepareStatement(
        "INSERT IGNORE INTO GiornoDisponibilita (idOmbrellone, data) VALUES (?, ?)");
        PreparedStatement stmtSold = conn.prepareStatement(
            "INSERT INTO OmbrelloneVenduto (idOmbrellone, data, contratto) VALUES (?, ?, ?)")) {

      LocalDate current = dataInizio;
      while (!current.isAfter(dataFine)) {
        stmtDay.setInt(1, idOmbrellone);
        stmtDay.setDate(2, Date.valueOf(current));
        stmtDay.executeUpdate();

        stmtSold.setInt(1, idOmbrellone);
        stmtSold.setDate(2, Date.valueOf(current));
        stmtSold.setInt(3, numContratto);
        stmtSold.executeUpdate();

        current = current.plusDays(1);
      }
    }
  }
}
