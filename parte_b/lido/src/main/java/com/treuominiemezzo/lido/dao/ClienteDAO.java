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
import java.util.Arrays;

public class ClienteDAO {

  // Metodo per estrarre tutti i clienti
  public List<Cliente> getAllClienti() {
    List<Cliente> clienti = new ArrayList<>();
    String query = "SELECT codice, nome, cognome, dataNascita, email, telefono, indirizzo FROM Cliente ORDER BY cognome, nome";

    try (Connection conn = Database.getConnection();
        PreparedStatement stmt = conn.prepareStatement(query);
        ResultSet rs = stmt.executeQuery()) {

      while (rs.next()) {
        int codice = rs.getInt("codice");
        String nome = rs.getString("nome");
        String cognome = rs.getString("cognome");

        java.sql.Date sqlDate = rs.getDate("dataNascita");
        LocalDate dataNascita = (sqlDate != null) ? sqlDate.toLocalDate() : null;

        String email = rs.getString("email");
        String telefono = rs.getString("telefono");
        String indirizzo = rs.getString("indirizzo");

        Cliente c = new Cliente(codice, nome, cognome, dataNascita, email, telefono, indirizzo, 0);
        clienti.add(c);
      }

    } catch (SQLException e) {
      System.err.println("Errore nel recupero dei clienti: " + e.getMessage());
      e.printStackTrace();
    }

    return clienti;
  }

  // Method to get paginated and filtered clients
  public List<Cliente> getClientiFiltered(String searchNome, String searchCognome, String annoNascita,
      String searchEmail, String searchTelefono,
      String sortColumn, String sortDirection, int limit, int offset) {
    List<Cliente> clienti = new ArrayList<>();
    List<Object> params = new ArrayList<>();

    // Base query with LEFT JOIN for contracts count
    StringBuilder sql = new StringBuilder(
        "SELECT c.codice, c.nome, c.cognome, c.dataNascita, c.email, c.telefono, c.indirizzo, " +
            "COUNT(con.numProgr) AS num_contratti " +
            "FROM Cliente c " +
            "LEFT JOIN Contratto con ON c.codice = con.stipulatoDa " +
            "WHERE 1=1 ");

    // Dynamic filtering
    if (searchCognome != null && !searchCognome.trim().isEmpty()) {
      sql.append("AND c.cognome LIKE ? ");
      params.add("%" + searchCognome + "%");
    }
    if (searchNome != null && !searchNome.trim().isEmpty()) {
      sql.append("AND c.nome LIKE ? ");
      params.add("%" + searchNome + "%");
    }
    if (annoNascita != null && !annoNascita.trim().isEmpty()) {
      sql.append("AND YEAR(c.dataNascita) = ? ");
      params.add(annoNascita);
    }
    if (searchEmail != null && !searchEmail.trim().isEmpty()) {
      sql.append("AND c.email LIKE ? ");
      params.add("%" + searchEmail + "%");
    }
    if (searchTelefono != null && !searchTelefono.trim().isEmpty()) {
      sql.append("AND c.telefono LIKE ? ");
      params.add("%" + searchTelefono + "%");
    }

    // Grouping
    sql.append("GROUP BY c.codice ");

    // Sorting (safeguard against SQL injection by allowing only specific columns)
    String safeSort = "c.codice";
    if (Arrays.asList("codice", "nome", "cognome", "dataNascita").contains(sortColumn)) {
      safeSort = "c." + sortColumn;
    }
    String safeDir = "DESC".equalsIgnoreCase(sortDirection) ? "DESC" : "ASC";

    sql.append("ORDER BY ").append(safeSort).append(" ").append(safeDir).append(" ");

    // Pagination
    sql.append("LIMIT ? OFFSET ?");
    params.add(limit);
    params.add(offset);

    try (Connection conn = Database.getConnection();
        PreparedStatement stmt = conn.prepareStatement(sql.toString())) {

      // Set all dynamic parameters in the prepared statement
      for (int i = 0; i < params.size(); i++) {
        stmt.setObject(i + 1, params.get(i));
      }

      ResultSet rs = stmt.executeQuery();
      while (rs.next()) {
        int codice = rs.getInt("codice");
        String nome = rs.getString("nome");
        String cognome = rs.getString("cognome");
        java.sql.Date sqlDate = rs.getDate("dataNascita");
        LocalDate dataNascita = (sqlDate != null) ? sqlDate.toLocalDate() : null;
        String email = rs.getString("email");
        String telefono = rs.getString("telefono");
        String indirizzo = rs.getString("indirizzo");
        int numContratti = rs.getInt("num_contratti");

        clienti.add(new Cliente(codice, nome, cognome, dataNascita, email, telefono, indirizzo, numContratti));
      }
    } catch (SQLException e) {
      e.printStackTrace();
    }
    return clienti;
  }

  // Method to count total filtered records for pagination
  public int getTotalClientiFiltered(String searchNome, String searchCognome, String annoNascita,
      String searchEmail, String searchTelefono) {
    int total = 0;
    List<Object> params = new ArrayList<>();
    StringBuilder sql = new StringBuilder("SELECT COUNT(DISTINCT c.codice) AS total FROM Cliente c WHERE 1=1 ");

    if (searchCognome != null && !searchCognome.trim().isEmpty()) {
      sql.append("AND c.cognome LIKE ? ");
      params.add("%" + searchCognome + "%");
    }
    if (searchNome != null && !searchNome.trim().isEmpty()) {
      sql.append("AND c.nome LIKE ? ");
      params.add("%" + searchNome + "%");
    }
    if (annoNascita != null && !annoNascita.trim().isEmpty()) {
      sql.append("AND YEAR(c.dataNascita) = ? ");
      params.add(annoNascita);
    }
    if (searchEmail != null && !searchEmail.trim().isEmpty()) {
      sql.append("AND c.email LIKE ? ");
      params.add("%" + searchEmail + "%");
    }
    if (searchTelefono != null && !searchTelefono.trim().isEmpty()) {
      sql.append("AND c.telefono LIKE ? ");
      params.add("%" + searchTelefono + "%");
    }

    try (Connection conn = Database.getConnection();
        PreparedStatement stmt = conn.prepareStatement(sql.toString())) {

      for (int i = 0; i < params.size(); i++) {
        stmt.setObject(i + 1, params.get(i));
      }

      ResultSet rs = stmt.executeQuery();
      if (rs.next()) {
        total = rs.getInt("total");
      }
    } catch (SQLException e) {
      e.printStackTrace();
    }
    return total;
  }

  // Method to delete a client and handle foreign key constraints
  public String deleteCliente(int codice) {
    String sql = "DELETE FROM Cliente WHERE codice = ?";
    try (Connection conn = Database.getConnection();
         PreparedStatement stmt = conn.prepareStatement(sql)) {
      
      stmt.setInt(1, codice);
      int rows = stmt.executeUpdate();
      
      if (rows > 0) {
        return "SUCCESS";
      } else {
        return "NOT_FOUND";
      }
    } catch (SQLException e) {
      // 1451 is the MySQL error code for Foreign Key constraint violation
      if (e.getErrorCode() == 1451) {
        return "HAS_CONTRACTS";
      }
      e.printStackTrace();
      return "ERROR";
    }
  }

  // Method to update an existing client
  public boolean updateCliente(Cliente c) {
    String sql = "UPDATE Cliente SET nome=?, cognome=?, dataNascita=?, email=?, telefono=?, indirizzo=? WHERE codice=?";
    try (Connection conn = Database.getConnection();
         PreparedStatement stmt = conn.prepareStatement(sql)) {
      
      stmt.setString(1, c.getNome());
      stmt.setString(2, c.getCognome());
      if (c.getDataNascita() != null) {
        stmt.setDate(3, java.sql.Date.valueOf(c.getDataNascita()));
      } else {
        stmt.setNull(3, java.sql.Types.DATE);
      }
      stmt.setString(4, c.getEmail());
      stmt.setString(5, c.getTelefono());
      stmt.setString(6, c.getIndirizzo());
      stmt.setInt(7, c.getCodice());

      return stmt.executeUpdate() > 0;
    } catch (SQLException e) {
      e.printStackTrace();
      return false;
    }
  }

  // Method to insert a new client
  public boolean insertCliente(Cliente c) {
    String sql = "INSERT INTO Cliente (nome, cognome, dataNascita, email, telefono, indirizzo) VALUES (?, ?, ?, ?, ?, ?)";
    try (Connection conn = Database.getConnection();
         PreparedStatement stmt = conn.prepareStatement(sql)) {
      
      stmt.setString(1, c.getNome());
      stmt.setString(2, c.getCognome());
      if (c.getDataNascita() != null) {
        stmt.setDate(3, java.sql.Date.valueOf(c.getDataNascita()));
      } else {
        stmt.setNull(3, java.sql.Types.DATE);
      }
      stmt.setString(4, c.getEmail());
      stmt.setString(5, c.getTelefono());
      stmt.setString(6, c.getIndirizzo());

      return stmt.executeUpdate() > 0;
    } catch (SQLException e) {
      e.printStackTrace();
      return false;
    }
  }
}